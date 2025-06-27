<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionDetail;
use App\Models\Master\Drug;
use App\Models\Master\Repack;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Checkout",
 *     description="API Endpoints for managing checkout transactions"
 * )
 */
class CheckoutController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/checkout/drugs",
     *     summary="Get available drugs for checkout",
     *     tags={"Checkout"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         required=true,
     *         description="Source of drugs (warehouse or clinic)",
     *         @OA\Schema(type="string", enum={"warehouse", "clinic"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Available drugs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Paracetamol"),
     *                 @OA\Property(property="stock", type="number", format="float", example=100),
     *                 @OA\Property(property="price", type="number", format="float", example=5000),
     *                 @OA\Property(property="last_discount", type="number", format="float", example=10)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function getAvailableDrugs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source' => 'required|in:warehouse,clinic'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        try {
            $drugs = [];
            if ($request->source === 'warehouse') {
                $warehouseItems = Warehouse::where('quantity', '>', 0)->get();
                $drugs = $warehouseItems->map(function ($item) {
                    $drug = Drug::find($item->drug_id);
                    return [
                        'id' => $drug->id,
                        'name' => $drug->name,
                        'stock' => $item->quantity / $drug->piece_netto,
                        'price' => $drug->last_price,
                        'last_discount' => $drug->last_discount
                    ];
                });
            } else {
                $clinicItems = Clinic::where('quantity', '>', 0)->get();
                $drugs = $clinicItems->map(function ($item) {
                    $drug = Drug::find($item->drug_id);
                    return [
                        'id' => $drug->id,
                        'name' => $drug->name,
                        'stock' => $item->quantity / $drug->piece_netto,
                        'price' => $drug->last_price,
                        'last_discount' => $drug->last_discount
                    ];
                });
            }

            return $this->successResponse($drugs, 'Available drugs retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve available drugs: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/checkout",
     *     summary="Create a checkout transaction",
     *     tags={"Checkout"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"source", "items", "transaction_discount_type"},
     *             @OA\Property(property="source", type="string", enum={"warehouse", "clinic"}),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="drug_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="number", format="float", example=2),
     *                 @OA\Property(property="discount", type="number", format="float", example=5)
     *             )),
     *             @OA\Property(property="transaction_discount_type", type="string", enum={"percentage", "fixed"}),
     *             @OA\Property(property="transaction_discount", type="number", format="float", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Checkout completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction", type="object"),
     *                 @OA\Property(property="details", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="summary", type="object",
     *                     @OA\Property(property="subtotal", type="number", format="float", example=10000),
     *                     @OA\Property(property="item_discounts", type="number", format="float", example=500),
     *                     @OA\Property(property="transaction_discount", type="number", format="float", example=950),
     *                     @OA\Property(property="total_discount", type="number", format="float", example=1450),
     *                     @OA\Property(property="total", type="number", format="float", example=8550),
     *                     @OA\Property(property="profit", type="number", format="float", example=2000)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or insufficient stock"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source' => 'required|in:warehouse,clinic',
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'transaction_discount_type' => 'required|in:percentage,fixed',
            'transaction_discount' => 'nullable|numeric|min:0'
        ]);
    
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }
    
        try {
            DB::beginTransaction();
    
            $subtotal = 0;
            $itemDiscountTotal = 0;
            $transactionDiscountAmount = 0;
            $totalProfit = 0;
            $items = [];
    
            foreach ($request->items as $item) {
                $drug = Drug::findOrFail($item['drug_id']);
                $quantityInPieces = $item['quantity'];
                $quantityInNetto = $quantityInPieces * $drug->piece_netto;
    
                $repack = Repack::where('drug_id', $drug->id)
                    ->where('quantity', $drug->piece_netto)
                    ->first();
    
                if (!$repack) {
                    return $this->errorResponse("No repack configuration found for {$drug->name}", [], 422);
                }
    
                $stock = ($request->source === 'warehouse')
                    ? Warehouse::where('drug_id', $drug->id)->where('quantity', '>', 0)->first()
                    : Clinic::where('drug_id', $drug->id)->where('quantity', '>', 0)->first();
    
                if (!$stock || $stock->quantity < $quantityInNetto) {
                    return $this->errorResponse("Insufficient stock for {$drug->name}", [], 422);
                }
    
                $oldestStock = TransactionDetail::where('drug_id', $drug->id)
                    ->where('stock', '>', 0)
                    ->orderBy('expired', 'asc')
                    ->first();
    
                if (!$oldestStock) {
                    return $this->errorResponse("No stock with expiration date found for {$drug->name}", [], 422);
                }
    
                $itemTotal = $quantityInPieces * $repack->price;
    
                // Hitung diskon bawaan produk
                $drugDiscountAmount = $itemTotal * ($drug->last_discount / 100);
                $afterDrugDiscount = $itemTotal - $drugDiscountAmount;
    
                // Hitung diskon tambahan dari item (jika ada)
                $additionalDiscountAmount = $afterDrugDiscount * (($item['discount'] ?? 0) / 100);
    
                // Total diskon item
                $totalItemDiscount = $drugDiscountAmount + $additionalDiscountAmount;
                $itemTotalAfterDiscount = $afterDrugDiscount - $additionalDiscountAmount;
    
                $subtotal += $itemTotal;
                $itemDiscountTotal += $totalItemDiscount;
    
                $costPerUnit = 2; // Ganti sesuai kebutuhan
                $itemProfit = -$totalItemDiscount - ($quantityInPieces * $costPerUnit);
                $totalProfit += $itemProfit;
    
                $items[] = [
                    'drug' => $drug,
                    'repack' => $repack,
                    'quantity' => $quantityInPieces,
                    'quantity_netto' => $quantityInNetto,
                    'price' => $repack->price,
                    'discount' => $item['discount'] ?? 0,
                    'total' => $itemTotal,
                    'discount_amount' => $totalItemDiscount,
                    'profit' => $itemProfit,
                    'stock' => $stock,
                    'expired' => $oldestStock->expired
                ];
            }
    
            // Hitung sisa setelah diskon item
            $afterItemDiscountTotal = $subtotal - $itemDiscountTotal;
    
            // Hitung diskon transaksi berdasarkan nilai setelah diskon item
            if ($request->transaction_discount_type === 'percentage') {
                $transactionDiscountAmount = $afterItemDiscountTotal * ($request->transaction_discount / 100);
            } else {
                $transactionDiscountAmount = $request->transaction_discount;
            }
    
            // Final total discount dan income
            $totalDiscount = $itemDiscountTotal + $transactionDiscountAmount;
            $finalTotal = $subtotal - $totalDiscount;
    
            $transaction = Transaction::create([
                'variant' => 'Checkout',
                'destination' => 'customer',
                'income' => $finalTotal,
                'discount' => $totalDiscount,
                'profit' => $totalProfit
            ]);
    
            $transaction->generate_code();
    
            foreach ($items as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'drug_id' => $item['drug']->id,
                    'name' => $item['drug']->name . ' 1 pcs',
                    'quantity' => $item['quantity'] . ' pcs',
                    'piece_price' => $item['price'],
                    'total_price' => $item['total'],
                    'flow' => -$item['quantity_netto'],
                    'expired' => $item['expired']
                ]);
    
                $stock = $item['stock'];
                $stock->quantity -= $item['quantity_netto'];
                $stock->save();
            }
    
            DB::commit();
    
            $transactionDetails = TransactionDetail::where('transaction_id', $transaction->id)->get();
    
            return $this->successResponse([
                'transaction' => $transaction,
                'details' => $transactionDetails,
                'summary' => [
                    'subtotal' => $subtotal,
                    'item_discounts' => $itemDiscountTotal,
                    'transaction_discount' => $transactionDiscountAmount,
                    'total_discount' => $totalDiscount,
                    'total' => $finalTotal,
                    'profit' => $totalProfit
                ]
            ], 'Checkout completed successfully', 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to process checkout: ' . $e->getMessage(), [], 500);
        }
    }
    
    
    /**
     * @OA\Get(
     *     path="/api/v1/checkout/history",
     *     summary="Get checkout transaction history",
     *     tags={"Checkout"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Checkout history retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=5),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="total", type="integer", example=100)
     *         )
     *     )
     * )
     */
    public function history(Request $request)
    {
        $transactions = Transaction::where('variant','Checkout')->paginate(20);

        return $this->successResponse($transactions, 'Checkout history retrieved successfully');
    }
} 