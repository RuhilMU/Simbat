<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Transaction\Bill;
use App\Models\Transaction\Retur;
use App\Models\Transaction\Trash;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionDetail;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Drug;
use App\Models\Master\Vendor;
use App\Models\Master\Manufacture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Management",
 *     description="API Endpoints for managing bills, returns, and trash records"
 * )
 */
class ManagementController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/management/bills",
     *     summary="Get all bills",
     *     tags={"Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         description="Start date for filtering",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end",
     *         in="query",
     *         description="End date for filtering",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bills retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bills retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="bills", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="BILL-001"),
     *                     @OA\Property(property="inflow_date", type="string", format="date", example="2024-03-20"),
     *                     @OA\Property(property="due_date", type="string", format="date", example="2024-04-20"),
     *                     @OA\Property(property="paid_date", type="string", format="date", example="2024-03-25"),
     *                     @OA\Property(property="subtotal", type="number", format="float", example=1000000),
     *                     @OA\Property(property="status", type="string", example="Done")
     *                 )),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="total", type="integer", example=100),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getBills(Request $request)
    {
        if ($request->has('start') && $request->has('end')) {
            $end = Carbon::parse($request->end)->endOfDay();
            $bills = Bill::whereBetween('created_at', [$request->start, $end])
                ->orderBy('status')
                ->paginate(10);
        } else {
            $bills = Bill::orderBy('status')->paginate(10);
        }

        $formattedBills = $bills->map(function ($bill) {
            $transaction = Transaction::find($bill->transaction_id);
            $details = TransactionDetail::where('transaction_id', $transaction->id)->get();
            $subtotal = $details->sum('total_price');

            return [
                'id' => $bill->id,
                'code' => $transaction->code,
                'inflow_date' => Carbon::parse($transaction->created_at)->format('Y-m-d'),
                'due_date' => Carbon::parse($bill->due)->format('Y-m-d'),
                'paid_date' => $bill->pay ? Carbon::parse($bill->pay)->format('Y-m-d') : '-',
                'subtotal' => $subtotal,
                'status' => $bill->status
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Bills retrieved successfully',
            'data' => [
                'bills' => $formattedBills,
                'pagination' => [
                    'total' => $bills->total(),
                    'per_page' => $bills->perPage(),
                    'current_page' => $bills->currentPage(),
                    'last_page' => $bills->lastPage()
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/management/bills/{id}",
     *     summary="Get bill details",
     *     tags={"Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bill ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bill detail retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="transaction", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="BILL-001"),
     *                     @OA\Property(property="date", type="string", format="date", example="2024-03-20"),
     *                     @OA\Property(property="vendor", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Vendor Name")
     *                     )
     *                 ),
     *                 @OA\Property(property="bill", type="object",
     *                     @OA\Property(property="due_date", type="string", format="date", example="2024-04-20"),
     *                     @OA\Property(property="paid_date", type="string", format="date", example="2024-03-25"),
     *                     @OA\Property(property="status", type="string", example="Done"),
     *                     @OA\Property(property="total", type="number", format="float", example=1000000)
     *                 ),
     *                 @OA\Property(property="details", type="array", @OA\Items(
     *                     @OA\Property(property="drug", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Drug Name"),
     *                         @OA\Property(property="code", type="string", example="DRUG-001")
     *                     ),
     *                     @OA\Property(property="quantity", type="integer", example=10),
     *                     @OA\Property(property="piece_price", type="number", format="float", example=10000),
     *                     @OA\Property(property="total_price", type="number", format="float", example=100000),
     *                     @OA\Property(property="expired", type="string", format="date", example="2025-03-20")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bill not found"
     *     )
     * )
     */
    public function getBillDetail($id)
    {
        $bill = Bill::findOrFail($id);
        $transaction = Transaction::find($bill->transaction_id);
        $vendor = Vendor::find($transaction->vendor_id);
        $details = TransactionDetail::where('transaction_id', $transaction->id)->get();
        
        $formattedDetails = $details->map(function ($detail) {
            $drug = Drug::find($detail->drug_id);
            return [
                'drug' => [
                    'id' => $drug->id,
                    'name' => $drug->name,
                    'code' => $drug->code
                ],
                'quantity' => $detail->quantity,
                'piece_price' => $detail->piece_price,
                'total_price' => $detail->total_price,
                'expired' => Carbon::parse($detail->expired)->format('Y-m-d')
            ];
        });

        $formattedBill = [
            'id' => $bill->id,
            'transaction' => [
                'id' => $transaction->id,
                'code' => $transaction->code,
                'date' => Carbon::parse($transaction->created_at)->format('Y-m-d'),
                'vendor' => [
                    'id' => $vendor->id,
                    'name' => $vendor->name
                ]
            ],
            'bill' => [
                'due_date' => Carbon::parse($bill->due)->format('Y-m-d'),
                'paid_date' => $bill->pay ? Carbon::parse($bill->pay)->format('Y-m-d') : null,
                'status' => $bill->status,
                'total' => $bill->total
            ],
            'details' => $formattedDetails
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Bill detail retrieved successfully',
            'data' => $formattedBill
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/management/bills/{id}/pay",
     *     summary="Pay a bill",
     *     tags={"Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bill ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill paid successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bill paid successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Bill is already paid"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to pay bill"
     *     )
     * )
     */
    public function payBill(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $bill = Bill::findOrFail($id);
            
            if ($bill->status === 'Done') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bill is already paid'
                ], 422);
            }

            $bill->update([
                'status' => 'Done',
                'pay' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Bill paid successfully',
                'data' => $bill
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to pay bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/management/returns",
     *     summary="Get all returns",
     *     tags={"Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         description="Start date for filtering",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end",
     *         in="query",
     *         description="End date for filtering",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returns retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Returns retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="returns", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="RET-001"),
     *                     @OA\Property(property="drug_name", type="string", example="Drug Name"),
     *                     @OA\Property(property="quantity", type="string", example="10 pcs"),
     *                     @OA\Property(property="return_date", type="string", format="date", example="2024-03-20"),
     *                     @OA\Property(property="arrive_date", type="string", format="date", example="2024-03-25"),
     *                     @OA\Property(property="status", type="string", example="Done")
     *                 )),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="total", type="integer", example=100),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getReturns(Request $request)
    {
        if ($request->has('start') && $request->has('end')) {
            $end = Carbon::parse($request->end)->endOfDay();
            $returns = Retur::whereBetween('created_at', [$request->start, $end])
                ->paginate(10);
        } else {
            $returns = Retur::paginate(10);
        }

        $formattedReturns = $returns->map(function ($retur) {
            $transaction = Transaction::find($retur->transaction_id);
            $drug = Drug::find($retur->drug_id);
            return [
                'id' => $retur->id,
                'code' => $transaction->code,
                'drug_name' => $drug->name,
                'quantity' => ($retur->quantity / $drug->piece_netto) . ' pcs',
                'return_date' => Carbon::parse($retur->created_at)->format('Y-m-d'),
                'arrive_date' => $retur->arrive ? Carbon::parse($retur->arrive)->format('Y-m-d') : null,
                'status' => $retur->status
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Returns retrieved successfully',
            'data' => [
                'returns' => $formattedReturns,
                'pagination' => [
                    'total' => $returns->total(),
                    'per_page' => $returns->perPage(),
                    'current_page' => $returns->currentPage(),
                    'last_page' => $returns->lastPage()
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/management/returns/{id}",
     *     summary="Get return details",
     *     tags={"Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Return ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Return details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Return detail retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="drug", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Drug Name"),
     *                     @OA\Property(property="manufacture", type="string", example="Manufacture Name")
     *                 ),
     *                 @OA\Property(property="transaction", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="RET-001"),
     *                     @OA\Property(property="vendor", type="string", example="Vendor Name")
     *                 ),
     *                 @OA\Property(property="quantity", type="object",
     *                     @OA\Property(property="value", type="integer", example=10),
     *                     @OA\Property(property="unit", type="string", example="pcs")
     *                 ),
     *                 @OA\Property(property="dates", type="object",
     *                     @OA\Property(property="return_date", type="string", format="date", example="2024-03-20"),
     *                     @OA\Property(property="expired_date", type="string", format="date", example="2025-03-20"),
     *                     @OA\Property(property="arrive_date", type="string", format="date", example="2024-03-25")
     *                 ),
     *                 @OA\Property(property="status", type="string", example="Done"),
     *                 @OA\Property(property="reason", type="string", example="Return reason")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Return not found"
     *     )
     * )
     */
    public function getReturnDetail($id)
    {
        $retur = Retur::findOrFail($id);
        $transaction = Transaction::find($retur->transaction_id);
        $drug = Drug::find($retur->drug_id);
        $detail = TransactionDetail::find($retur->transaction_detail_id);
        $source = TransactionDetail::find($retur->source);
        $vendor = Vendor::find($transaction->vendor_id);
        $manufacture = Manufacture::find($drug->manufacture_id);

        $formattedRetur = [
            'id' => $retur->id,
            'drug' => [
                'id' => $drug->id,
                'name' => $drug->name,
                'manufacture' => $manufacture->name
            ],
            'transaction' => [
                'id' => $transaction->id,
                'code' => $transaction->code,
                'vendor' => $vendor->name
            ],
            'quantity' => [
                'value' => $retur->quantity / $drug->piece_netto,
                'unit' => 'pcs'
            ],
            'dates' => [
                'return_date' => Carbon::parse($retur->created_at)->format('Y-m-d'),
                'expired_date' => Carbon::parse($detail->expired)->format('Y-m-d'),
                'arrive_date' => $retur->arrive ? Carbon::parse($retur->arrive)->format('Y-m-d') : null
            ],
            'status' => $retur->status,
            'reason' => $retur->reason
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Return detail retrieved successfully',
            'data' => $formattedRetur
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/management/returns",
     *     summary="Create a return",
     *     tags={"Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_detail_id", "quantity"},
     *             @OA\Property(property="transaction_detail_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="number", format="float", example=10),
     *             @OA\Property(property="reason", type="string", example="Return reason")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Return created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Return created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction", type="object"),
     *                 @OA\Property(property="detail", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or insufficient stock"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create return"
     *     )
     * )
     */
    public function createReturn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_detail_id' => 'required|exists:transaction_details,id',
            'quantity' => 'required|numeric|min:1',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $detail = TransactionDetail::findOrFail($request->transaction_detail_id);
            $drug = Drug::find($detail->drug_id);
            
            // Calculate quantity in pieces
            $quantityInPieces = $request->quantity;
            $quantityInNetto = $quantityInPieces * $drug->piece_netto;
            
            // Check if there's enough stock to return
            if ($detail->stock < $quantityInNetto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient stock to return'
                ], 422);
            }

            // Create return transaction
            $transaction = Transaction::create([
                'vendor_id' => $detail->transaction->vendor_id,
                'destination' => 'warehouse',
                'variant' => 'Retur'
            ]);

            $transaction->generate_code();

            // Create return detail
            $returnDetail = TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'drug_id' => $drug->id,
                'expired' => $detail->expired,
                'name' => $drug->name . ' 1 pcs',
                'quantity' => $quantityInPieces . ' pcs',
                'piece_price' => $drug->last_price,
                'total_price' => $quantityInPieces * $drug->last_price,
                'flow' => 0
            ]);

            // Create return record
            Retur::create([
                'drug_id' => $drug->id,
                'transaction_id' => $transaction->id,
                'transaction_detail_id' => $returnDetail->id,
                'source' => $detail->id,
                'quantity' => $quantityInNetto,
                'status' => 'Belum Kembali',
                'reason' => $request->reason
            ]);

            // Update original stock
            $detail->stock = $detail->stock - $quantityInNetto;
            $detail->save();

            // Update warehouse stock
            $warehouse = Warehouse::where('drug_id', $drug->id)->first();
            $warehouse->quantity = $warehouse->quantity - $quantityInNetto;
            $warehouse->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Return created successfully',
                'data' => [
                    'transaction' => $transaction,
                    'detail' => $returnDetail
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create return',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/management/returns/{id}/complete",
     *     summary="Complete a return",
     *     tags={"Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Return ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"new_expired_date"},
     *             @OA\Property(property="new_expired_date", type="string", format="date", example="2025-03-20")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Return completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Return completed successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Return is already completed or validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to complete return"
     *     )
     * )
     */
    public function completeReturn(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'new_expired_date' => 'required|date|after:today'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $retur = Retur::findOrFail($id);
            
            if ($retur->status === 'Done') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Return is already completed'
                ], 422);
            }

            $batch = TransactionDetail::find($retur->source);
            $drug = Drug::find($retur->drug_id);

            // Update original stock with new expiration date
            $batch->update([
                'stock' => $batch->stock + $retur->quantity,
                'expired' => $request->new_expired_date
            ]);

            // Update warehouse stock
            $warehouse = Warehouse::where('drug_id', $drug->id)->first();
            $warehouse->update([
                'quantity' => $warehouse->quantity + $retur->quantity
            ]);

            // Update return status
            $retur->update([
                'status' => 'Done',
                'arrive' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Return completed successfully',
                'data' => $retur
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to complete return',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/management/trash",
     *     summary="Get all trash records",
     *     tags={"Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         description="Start date for filtering",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end",
     *         in="query",
     *         description="End date for filtering",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trash records retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Trash records retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="trash", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="TRASH-001"),
     *                     @OA\Property(property="drug_name", type="string", example="Drug Name"),
     *                     @OA\Property(property="quantity", type="string", example="10 pcs"),
     *                     @OA\Property(property="trash_date", type="string", format="date", example="2024-03-20")
     *                 )),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="total", type="integer", example=100),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getTrash(Request $request)
    {
        if ($request->has('start') && $request->has('end')) {
            $end = Carbon::parse($request->end)->endOfDay();
            $trash = Trash::whereBetween('created_at', [$request->start, $end])
                ->paginate(10);
        } else {
            $trash = Trash::paginate(10);
        }

        $formattedTrash = $trash->map(function ($trash) {
            $transaction = Transaction::find($trash->transaction_id);
            $drug = Drug::find($trash->drug_id);
            $quantityInPieces = $trash->quantity / $drug->piece_netto;

            return [
                'id' => $trash->id,
                'code' => $transaction->code,
                'drug_name' => $drug->name,
                'quantity' => $quantityInPieces . ' pcs',
                'trash_date' => Carbon::parse($trash->created_at)->format('Y-m-d')
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Trash records retrieved successfully',
            'data' => [
                'trash' => $formattedTrash,
                'pagination' => [
                    'total' => $trash->total(),
                    'per_page' => $trash->perPage(),
                    'current_page' => $trash->currentPage(),
                    'last_page' => $trash->lastPage()
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/management/trash/{id}",
     *     summary="Get trash details",
     *     tags={"Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Trash ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trash details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Trash detail retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="drug", type="object",
     *                     @OA\Property(property="name", type="string", example="Drug Name"),
     *                     @OA\Property(property="manufacture", type="string", example="Manufacture Name")
     *                 ),
     *                 @OA\Property(property="expired_date", type="string", format="date", example="2025-03-20"),
     *                 @OA\Property(property="trash_date", type="string", format="date", example="2024-03-20"),
     *                 @OA\Property(property="transaction", type="object",
     *                     @OA\Property(property="code", type="string", example="TRASH-001"),
     *                     @OA\Property(property="vendor", type="string", example="Vendor Name")
     *                 ),
     *                 @OA\Property(property="quantity", type="string", example="10 pcs"),
     *                 @OA\Property(property="reason", type="string", example="Trash reason")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trash record not found"
     *     )
     * )
     */
    public function getTrashDetail($id)
    {
        $trash = Trash::findOrFail($id);
        $transaction = Transaction::find($trash->transaction_id);
        $drug = Drug::find($trash->drug_id);
        $manufacture = Manufacture::find($drug->manufacture_id);
        $vendor = Vendor::find($transaction->vendor_id);
        $detail = TransactionDetail::find($trash->transaction_detail_id);

        $formattedTrash = [
            'id' => $trash->id,
            'drug' => [
                'name' => $drug->name,
                'manufacture' => $manufacture->name
            ],
            'expired_date' => Carbon::parse($detail->expired)->format('Y-m-d'),
            'trash_date' => Carbon::parse($trash->created_at)->format('Y-m-d'),
            'transaction' => [
                'code' => $transaction->code,
                'vendor' => $vendor->name
            ],
            'quantity' => ($trash->quantity / $drug->piece_netto) . ' pcs',
            'reason' => $trash->reason
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Trash detail retrieved successfully',
            'data' => $formattedTrash
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/management/trash",
     *     summary="Create a trash record",
     *     tags={"Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_detail_id", "quantity", "reason"},
     *             @OA\Property(property="transaction_detail_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="number", format="float", example=10),
     *             @OA\Property(property="reason", type="string", example="Trash reason")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Trash record created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Trash created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction", type="object"),
     *                 @OA\Property(property="detail", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or insufficient stock"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create trash record"
     *     )
     * )
     */
    public function createTrash(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_detail_id' => 'required|exists:transaction_details,id',
            'quantity' => 'required|numeric|min:1',
            'reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $detail = TransactionDetail::findOrFail($request->transaction_detail_id);
            $drug = $detail->drug();
            
            // Check if there's enough stock to trash
            if ($detail->stock < $request->quantity * $drug->piece_netto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient stock to trash'
                ], 422);
            }

            // Create trash transaction
            $transaction = Transaction::create([
                'vendor_id' => $detail->transaction->vendor_id,
                'destination' => 'warehouse',
                'variant' => 'Trash',
                'loss' => $request->quantity * $drug->last_price
            ]);

            $transaction->generate_code();

            // Create trash detail
            $trashDetail = TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'drug_id' => $drug->id,
                'expired' => $detail->expired,
                'name' => $drug->name . ' 1 pcs',
                'quantity' => $request->quantity . ' pcs',
                'piece_price' => $drug->last_price,
                'total_price' => $request->quantity * $drug->last_price,
                'flow' => -($request->quantity * $drug->piece_netto)
            ]);

            // Create trash record
            Trash::create([
                'drug_id' => $drug->id,
                'transaction_id' => $transaction->id,
                'transaction_detail_id' => $trashDetail->id,
                'quantity' => $request->quantity * $drug->piece_netto,
                'reason' => $request->reason
            ]);

            // Update original stock
            $detail->stock = $detail->stock - ($request->quantity * $drug->piece_netto);
            $detail->save();

            // Update warehouse stock
            $warehouse = Warehouse::where('drug_id', $drug->id)->first();
            $warehouse->quantity = $warehouse->quantity - ($request->quantity * $drug->piece_netto);
            $warehouse->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Trash created successfully',
                'data' => [
                    'transaction' => $transaction,
                    'detail' => $trashDetail
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create trash',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 