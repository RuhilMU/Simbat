<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\Clinic;
use App\Models\Master\Drug;
use App\Models\Master\Vendor;
use App\Models\Transaction\Bill;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionDetail;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Inventory",
 *     description="API Endpoints for managing inventory and stock"
 * )
 */
class InventoryController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/inventory/inflows",
     *     summary="Get all inflows",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inflows retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Inflows retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="No. LPB", type="string", example="LPB-001"),
     *                 @OA\Property(property="Vendor", type="string", example="Vendor Name"),
     *                 @OA\Property(property="Date", type="string", example="20 March 2024")
     *             ))
     *         )
     *     )
     * )
     */
    public function getInflows(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $inflows = Transaction::where('variant', 'LPB')
            ->paginate($perPage);

        $formattedInflows = $inflows->map(function ($inflow) {
            return [
                'id' => $inflow->id,
                'No. LPB' => $inflow->code,
                'Date' => Carbon::parse($inflow->created_at)->isoFormat('D MMMM Y')
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Inflows retrieved successfully',
            'data' => $formattedInflows,
            'pagination' => [
            'current_page' => $inflows->currentPage(),
            'per_page' => $inflows->perPage(),
            'total' => $inflows->total(),
            'last_page' => $inflows->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory/inflows/{id}",
     *     summary="Get inflow details",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Inflow ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inflow details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Inflow detail retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Profile", type="object"),
     *                 @OA\Property(property="No. LPB", type="string", example="LPB-001"),
     *                 @OA\Property(property="Date", type="string", example="20 March 2024"),
     *                 @OA\Property(property="Vendor", type="object"),
     *                 @OA\Property(property="Details", type="array", @OA\Items(
     *                     @OA\Property(property="drug_code", type="string", example="DRUG-001"),
     *                     @OA\Property(property="drug_name", type="string", example="Drug Name"),
     *                     @OA\Property(property="total", type="string", example="10 pcs"),
     *                     @OA\Property(property="piece_price", type="string", example="10,000"),
     *                     @OA\Property(property="subtotal", type="string", example="100,000")
     *                 )),
     *                 @OA\Property(property="Grand_total", type="string", example="1,000,000")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inflow not found"
     *     )
     * )
     */
    public function getInflowDetail($id)
    {
        $transaction = Transaction::findOrFail($id);
        $profile = Profile::first();

        $formattedDate = Carbon::parse($transaction->created_at)->isoFormat('D MMMM Y');

        $details = $transaction->details()->map(function ($detail) {
            $drug = $detail->drug();
            return [
                'drug_code' => $drug->code,
                'drug_name' => $drug->name,
                'total' => $detail->quantity,
                'piece_price' => number_format($detail->piece_price, 0, ',', '.'),
                'subtotal' => number_format($detail->total_price, 0, ',', '.')
            ];
        });

        $grandTotal = $details->sum(function ($detail) {
            return str_replace(['.', ','], ['', '.'], $detail['subtotal']);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Inflow detail retrieved successfully',
            'data' => [
                'Profile' => $profile,
                'No. LPB' => $transaction->code,
                'Date' => $formattedDate,
                'Vendor' => $transaction->vendor(),
                'Details' => $details,
                'Grand_total' => number_format($grandTotal, 0, ',', '.')
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory/vendors",
     *     summary="Get all vendors",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Vendors retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vendors retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to get vendors"
     *     )
     * )
     */
    public function getVendors()
    {
        try {
            $vendors = Vendor::all();
            return $this->successResponse($vendors, 'Vendors retrieved successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get vendors: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory/drugs",
     *     summary="Get all drugs",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Drugs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Drugs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to get drugs"
     *     )
     * )
     */
    public function getDrugs()
    {
        try {
            $drugs = Drug::all();
            return $this->successResponse($drugs, 'Drugs retrieved successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get drugs: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory/inflows",
     *     summary="Create a new inflow",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"vendor_id", "method", "destination", "items"},
     *             @OA\Property(property="vendor_id", type="integer", example=1),
     *             @OA\Property(property="method", type="string", enum={"cash", "credit"}),
     *             @OA\Property(property="due", type="string", format="date", example="2024-04-20"),
     *             @OA\Property(property="destination", type="string", enum={"warehouse", "clinic"}),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="name", type="string", example="Drug Name"),
     *                 @OA\Property(property="quantity", type="number", format="float", example=10),
     *                 @OA\Property(property="unit", type="string", enum={"pcs", "pack", "box"}),
     *                 @OA\Property(property="expired", type="string", format="date", example="2025-03-20")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inflow created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Inflow created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create inflow"
     *     )
     * )
     */
    public function createInflow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
            'method' => 'required|in:cash,credit',
            'due' => 'required_if:method,credit|date',
            'destination' => 'required|in:warehouse,clinic',
            'items' => 'required|array',
            'items.*.name' => 'required|exists:drugs,name',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'required|in:pcs,pack,box',
            'items.*.expired' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $drug = Drug::where('name', $item['name'])->first();
                $piecePrice = $drug->last_price;

                // Calculate total price based on unit
                $totalPrice = match ($item['unit']) {
                    'pcs' => $piecePrice * $item['quantity'],
                    'pack' => $piecePrice * $item['quantity'] * $drug->piece_quantity,
                    'box' => $piecePrice * $item['quantity'] * $drug->piece_quantity * $drug->pack_quantity,
                };

                $totalAmount += $totalPrice;
            }

            $transaction = Transaction::create([
                'vendor_id' => $request->vendor_id,
                'destination' => $request->destination,
                'method' => $request->method,
                'variant' => $request->destination === 'clinic' ? 'LPK' : 'LPB',
                'outcome' => $totalAmount
            ]);

            $transaction->generate_code();

            if ($transaction->method == 'credit') {
                Bill::create([
                    'transaction_id' => $transaction->id,
                    'total' => $totalAmount,
                    'status' => 'Belum Bayar',
                    'due' => $request->due
                ]);
            }

            foreach ($request->items as $item) {
                $drug = Drug::where('name', $item['name'])->first();
                $piecePrice = $drug->last_price;

                // Calculate total price based on unit
                $totalPrice = match ($item['unit']) {
                    'pcs' => $piecePrice * $item['quantity'],
                    'pack' => $piecePrice * $item['quantity'] * $drug->piece_quantity,
                    'box' => $piecePrice * $item['quantity'] * $drug->piece_quantity * $drug->pack_quantity,
                };

                $detail = TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'drug_id' => $drug->id,
                    'name' => $drug->name . ' 1 ' . $item['unit'],
                    'quantity' => $item['quantity'] . ' ' . $item['unit'],
                    'stock' => $item['quantity'],
                    'expired' => $item['expired'],
                    'piece_price' => $piecePrice,
                    'total_price' => $totalPrice,
                    'used' => false
                ]);

                // Calculate quantity in pieces
                $quantity = match ($item['unit']) {
                    'pcs' => $item['quantity'] * $drug->piece_netto,
                    'pack' => $item['quantity'] * ($drug->piece_netto * $drug->piece_quantity),
                    'box' => $item['quantity'] * ($drug->piece_netto * $drug->piece_quantity * $drug->pack_quantity),
                };

                if ($request->destination === 'clinic') {
                    // Update clinic stock only
                    $clinicStock = Clinic::where('drug_id', $drug->id)->first();
                    $clinicStock->quantity = $clinicStock->quantity + $quantity;

                    // Update clinic expiration dates
                    if ($clinicStock->oldest == null) {
                        $clinicStock->oldest = $item['expired'];
                        $clinicStock->latest = $item['expired'];
                        $drug->used = $detail->id;
                        $detail->used = true;
                        $detail->save();
                        $drug->save();
                    } else {
                        if ($clinicStock->oldest > $item['expired']) {
                            $old = TransactionDetail::find($drug->used);
                            $old->used = false;
                            $old->save();
                            $drug->used = $detail->id;
                            $detail->used = true;
                            $detail->save();
                            $drug->save();
                            $clinicStock->oldest = $item['expired'];
                        }
                        if ($clinicStock->latest < $item['expired']) {
                            $clinicStock->latest = $item['expired'];
                        }
                    }
                    $clinicStock->save();
                } else {
                    // Update warehouse stock only
                    $warehouseStock = Warehouse::where('drug_id', $drug->id)->first();
                    $warehouseStock->quantity = $warehouseStock->quantity + $quantity;

                    // Update warehouse expiration dates
                    if ($warehouseStock->oldest == null) {
                        $warehouseStock->oldest = $item['expired'];
                        $warehouseStock->latest = $item['expired'];
                        $drug->used = $detail->id;
                        $detail->used = true;
                        $detail->save();
                        $drug->save();
                    } else {
                        if ($warehouseStock->oldest > $item['expired']) {
                            $old = TransactionDetail::find($drug->used);
                            $old->used = false;
                            $old->save();
                            $drug->used = $detail->id;
                            $detail->used = true;
                            $detail->save();
                            $drug->save();
                            $warehouseStock->oldest = $item['expired'];
                        }
                        if ($warehouseStock->latest < $item['expired']) {
                            $warehouseStock->latest = $item['expired'];
                        }
                    }
                    $warehouseStock->save();
                }

                $detail->stock = $quantity;
                $detail->flow = $quantity;
                $detail->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Inflow created successfully',
                'data' => $transaction
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create inflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory/stocks",
     *     summary="Get all stocks",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stocks retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Stocks retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getStocks(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $stocks = Warehouse::paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Stocks retrieved successfully',
            'data' => $stocks
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory/stocks/{id}",
     *     summary="Get stock details",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Stock ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Stock details retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock not found"
     *     )
     * )
     */
    public function getStockDetail($id)
    {
        $drug = Drug::findOrFail($id);
        $judul = "Stock ".$drug->name;
        $stock = Warehouse::where('drug_id', $drug->id)->first();
        $inflow = Transaction::where('variant', 'LPB')->pluck('id');

        $details = TransactionDetail::where('drug_id', $drug->id)
            ->whereIn('transaction_id', $inflow)
            ->whereNot('stock', 0)
            ->orderBy('expired')
            ->paginate(10);

        $transactions = TransactionDetail::where('drug_id', $drug->id)
            ->orderBy('created_at')
            ->paginate(5);

        return response()->json([
            'status' => 'success',
            'message' => $judul.' retrieved successfully',
            'data' => [
                'drug' => $drug,
                'stock' => $stock,
                'details' => $details,
                'transactions' => $transactions
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory/clinic-stocks",
     *     summary="Get all clinic stocks",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic stocks retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Clinic stocks retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getClinicStocks(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $stocks = Clinic::paginate($perPage);

        $formattedStocks = $stocks->map(function ($stock) {
            $drug = $stock->drug();
            return [
                'drug_code' => $drug->code,
                'drug_name' => $drug->name,
                'quantity' => $stock->quantity / $drug->piece_netto,
                'category' => $drug->category()->name,
                'manufacture' => $drug->manufacture()->name,
                'variant' => $drug->variant()->name
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Clinic stocks retrieved successfully',
            'data' => $formattedStocks
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory/clinic-stocks/{id}",
     *     summary="Get clinic stock details",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Clinic stock ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic stock details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Clinic stock details retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Clinic stock not found"
     *     )
     * )
     */
    public function getClinicStockDetail($id)
    {
        $drug = Drug::findOrFail($id);
        $stock = Clinic::where('drug_id', $drug->id)->first();
        $inflow = Transaction::where('variant', 'LPB')
            ->where('destination', 'clinic')
            ->pluck('id');

        $details = TransactionDetail::where('drug_id', $drug->id)
            ->whereIn('transaction_id', $inflow)
            ->whereNot('stock', 0)
            ->orderBy('expired')
            ->paginate(10);

        $transactions = TransactionDetail::where('drug_id', $drug->id)
            ->whereIn('transaction_id', $inflow)
            ->orderBy('created_at')
            ->paginate(5);

        return response()->json([
            'status' => 'success',
            'message' => 'Clinic stock detail retrieved successfully',
            'data' => [
                'drug' => [
                    'code' => $drug->code,
                    'name' => $drug->name,
                    'category' => $drug->category()->name,
                    'manufacture' => $drug->manufacture()->name,
                    'variant' => $drug->variant()->name
                ],
                'stock' => $stock,
                'details' => $details,
                'transactions' => $transactions
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory/transfer-to-clinic",
     *     summary="Transfer stock to clinic",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="drug_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="number", format="float", example=10)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Stock transferred successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Stock transferred successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or insufficient stock"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to transfer stock"
     *     )
     * )
     */
    public function transferToClinic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transaction = Transaction::create([
                'variant' => 'LPK',
                'destination' => 'clinic',
                'method' => 'cash',
                'outcome' => 0
            ]);

            $transaction->generate_code();

            foreach ($request->items as $item) {
                $drug = Drug::findOrFail($item['drug_id']);
                $warehouseStock = Warehouse::where('drug_id', $drug->id)->first();

                // Check if warehouse has enough stock
                if ($warehouseStock->quantity < $item['quantity']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Insufficient stock for {$drug->name} in warehouse"
                    ], 422);
                }

                // Calculate quantity in pieces
                $quantity = $item['quantity'] * $drug->piece_netto;

                // Get the oldest stock from warehouse for expiration date
                $oldestStock = TransactionDetail::where('drug_id', $drug->id)
                    ->where('stock', '>', 0)
                    ->orderBy('expired')
                    ->first();

                if (!$oldestStock) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "No stock found with expiration date for {$drug->name}"
                    ], 422);
                }

                // Update warehouse stock
                $warehouseStock->quantity = $warehouseStock->quantity - $quantity;
                $warehouseStock->save();

                // Update clinic stock
                $clinicStock = Clinic::where('drug_id', $drug->id)->first();
                $clinicStock->quantity = $clinicStock->quantity + $quantity;

                // Update clinic expiration dates if needed
                if ($clinicStock->oldest == null) {
                    $clinicStock->oldest = $warehouseStock->oldest;
                    $clinicStock->latest = $warehouseStock->latest;
                } else {
                    if ($clinicStock->oldest > $warehouseStock->oldest) {
                        $clinicStock->oldest = $warehouseStock->oldest;
                    }
                    if ($clinicStock->latest < $warehouseStock->latest) {
                        $clinicStock->latest = $warehouseStock->latest;
                    }
                }
                $clinicStock->save();

                // Create transaction detail
                $detail = TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'drug_id' => $drug->id,
                    'name' => $drug->name . ' 1 pcs',
                    'quantity' => $item['quantity'] . ' pcs',
                    'stock' => $quantity,
                    'flow' => $quantity,
                    'piece_price' => $drug->last_price,
                    'total_price' => $drug->last_price * $item['quantity'],
                    'used' => false,
                    'expired' => $oldestStock->expired
                ]);

                // Update the warehouse stock's transaction detail
                $oldestStock->stock = $oldestStock->stock - $quantity;
                $oldestStock->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Transfer to clinic successful',
                'data' => $transaction
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to transfer to clinic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory/clinic-inflows",
     *     summary="Get all clinic inflows",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic inflows retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Clinic inflows retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="No. LPK", type="string", example="LPK-001"),
     *                 @OA\Property(property="Vendor", type="string", example="Vendor Name"),
     *                 @OA\Property(property="Date", type="string", example="20 March 2024")
     *             ))
     *         )
     *     )
     * )
     */
    public function getClinicInflows(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $inflows = Transaction::where('variant', 'LPK')
            ->where('destination', 'clinic')
            ->paginate($perPage);

        $formattedInflows = $inflows->map(function ($inflow) {
            return [
                'id' => $inflow->id,
                'No. LPK' => $inflow->code,
                'Vendor' => 'Klinik Simbat',
                'Date' => Carbon::parse($inflow->created_at)->isoFormat('D MMMM Y')
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Clinic inflows retrieved successfully',
            'data' => $formattedInflows,
            'pagination' => [
                'current_page' => $inflows->currentPage(),
                'per_page' => $inflows->perPage(),
                'total' => $inflows->total(),
                'last_page' => $inflows->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory/clinic-inflows/{id}",
     *     summary="Get clinic inflow details",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Clinic inflow ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic inflow details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Clinic inflow detail retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Profile", type="object"),
     *                 @OA\Property(property="No. LPK", type="string", example="LPK-001"),
     *                 @OA\Property(property="Date", type="string", example="20 March 2024"),
     *                 @OA\Property(property="Vendor", type="object"),
     *                 @OA\Property(property="Details", type="array", @OA\Items(
     *                     @OA\Property(property="drug_code", type="string", example="DRUG-001"),
     *                     @OA\Property(property="drug_name", type="string", example="Drug Name"),
     *                     @OA\Property(property="total", type="string", example="10 pcs"),
     *                     @OA\Property(property="piece_price", type="string", example="10,000"),
     *                     @OA\Property(property="subtotal", type="string", example="100,000")
     *                 )),
     *                 @OA\Property(property="Grand_total", type="string", example="1,000,000")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Clinic inflow not found"
     *     )
     * )
     */
    public function getClinicInflowDetail($id)
    {
        $transaction = Transaction::where('variant', 'LPK')
            ->where('destination', 'clinic')
            ->find($id);
            
        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic inflow not found'
            ], 404);
        }
        
        $profile = Profile::first();
        $formattedDate = Carbon::parse($transaction->created_at)->isoFormat('D MMMM Y');

        $details = $transaction->details()->map(function ($detail) {
            $drug = $detail->drug();
            return [
                'drug_code' => $drug->code,
                'drug_name' => $drug->name,
                'total' => $detail->quantity,
                'piece_price' => number_format($detail->piece_price, 0, ',', '.'),
                'subtotal' => number_format($detail->total_price, 0, ',', '.')
            ];
        });

        $grandTotal = $details->sum(function ($detail) {
            return str_replace(['.', ','], ['', '.'], $detail['subtotal']);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Clinic inflow detail retrieved successfully',
            'data' => [
                'Profile' => $profile,
                'No. LPK' => $transaction->code,
                'Date' => $formattedDate,
                'Vendor' => 'Klinik Simbat',
                'Details' => $details,
                'Grand_total' => number_format($grandTotal, 0, ',', '.')
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory/clinic-inflows",
     *     summary="Create a new clinic inflow",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"vendor_id", "method", "due", "items"},
     *             @OA\Property(property="vendor_id", type="integer", example=1),
     *             @OA\Property(property="method", type="string", enum={"cash", "credit"}),
     *             @OA\Property(property="due", type="string", format="date", example="2024-04-20"),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="name", type="string", example="Drug Name"),
     *                 @OA\Property(property="quantity", type="number", format="float", example=10),
     *                 @OA\Property(property="unit", type="string", enum={"pcs", "pack", "box"}),
     *                 @OA\Property(property="expired", type="string", format="date", example="2025-03-20")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Clinic inflow created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Clinic inflow created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create clinic inflow"
     *     )
     * )
     */
    public function createClinicInflow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
            'method' => 'required|in:cash,credit',
            'due' => 'required_if:method,credit|date',
            'items' => 'required|array',
            'items.*.name' => 'required|exists:drugs,name',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'required|in:pcs,pack,box',
            'items.*.expired' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $drug = Drug::where('name', $item['name'])->first();
                $piecePrice = $drug->last_price;

                // Calculate total price based on unit
                $totalPrice = match ($item['unit']) {
                    'pcs' => $piecePrice * $item['quantity'],
                    'pack' => $piecePrice * $item['quantity'] * $drug->piece_quantity,
                    'box' => $piecePrice * $item['quantity'] * $drug->piece_quantity * $drug->pack_quantity,
                };

                $totalAmount += $totalPrice;
            }

            $transaction = Transaction::create([
                'vendor_id' => $request->vendor_id,
                'destination' => 'clinic',
                'method' => $request->method,
                'variant' => 'LPK',
                'outcome' => $totalAmount
            ]);

            $transaction->generate_code();

            if ($transaction->method == 'credit') {
                Bill::create([
                    'transaction_id' => $transaction->id,
                    'total' => $totalAmount,
                    'status' => 'Belum Bayar',
                    'due' => $request->due
                ]);
            }

            foreach ($request->items as $item) {
                $drug = Drug::where('name', $item['name'])->first();
                $piecePrice = $drug->last_price;

                // Calculate total price based on unit
                $totalPrice = match ($item['unit']) {
                    'pcs' => $piecePrice * $item['quantity'],
                    'pack' => $piecePrice * $item['quantity'] * $drug->piece_quantity,
                    'box' => $piecePrice * $item['quantity'] * $drug->piece_quantity * $drug->pack_quantity,
                };

                $detail = TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'drug_id' => $drug->id,
                    'name' => $drug->name . ' 1 ' . $item['unit'],
                    'quantity' => $item['quantity'] . ' ' . $item['unit'],
                    'stock' => $item['quantity'],
                    'expired' => $item['expired'],
                    'piece_price' => $piecePrice,
                    'total_price' => $totalPrice,
                    'used' => false
                ]);

                // Calculate quantity in pieces
                $quantity = match ($item['unit']) {
                    'pcs' => $item['quantity'] * $drug->piece_netto,
                    'pack' => $item['quantity'] * ($drug->piece_netto * $drug->piece_quantity),
                    'box' => $item['quantity'] * ($drug->piece_netto * $drug->piece_quantity * $drug->pack_quantity),
                };

                // Update clinic stock only
                $clinicStock = Clinic::where('drug_id', $drug->id)->first();
                $clinicStock->quantity = $clinicStock->quantity + $quantity;

                // Update clinic expiration dates
                if ($clinicStock->oldest == null) {
                    $clinicStock->oldest = $item['expired'];
                    $clinicStock->latest = $item['expired'];
                    $drug->used = $detail->id;
                    $detail->used = true;
                    $detail->save();
                    $drug->save();
                } else {
                    if ($clinicStock->oldest > $item['expired']) {
                        $old = TransactionDetail::find($drug->used);
                        $old->used = false;
                        $old->save();
                        $drug->used = $detail->id;
                        $detail->used = true;
                        $detail->save();
                        $drug->save();
                        $clinicStock->oldest = $item['expired'];
                    }
                    if ($clinicStock->latest < $item['expired']) {
                        $clinicStock->latest = $item['expired'];
                    }
                }
                $clinicStock->save();

                $detail->stock = $quantity;
                $detail->flow = $quantity;
                $detail->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Clinic inflow created successfully',
                'data' => $transaction
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create clinic inflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
