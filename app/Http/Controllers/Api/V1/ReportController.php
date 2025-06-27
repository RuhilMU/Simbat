<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Drug;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Reports",
 *     description="API Endpoints for generating reports"
 * )
 */
class ReportController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/reports/drugs",
     *     summary="Get drug inventory report",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Drug report retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Drug report retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="drug_code", type="string", example="DRG001"),
     *                 @OA\Property(property="drug_name", type="string", example="Paracetamol"),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="oldest_expired", type="string", example="01-01-2024"),
     *                 @OA\Property(property="latest_expired", type="string", example="31-12-2024")
     *             ))
     *         )
     *     )
     * )
     */
    public function getDrugReport(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $stocks = Warehouse::with('data')->paginate($perPage);

        $formattedStocks = $stocks->map(function ($stock) {
            $drug = $stock->data;
            return [
                'drug_code' => $drug->code,
                'drug_name' => $drug->name,
                'quantity' => floor($stock->quantity / $drug->piece_netto),
                'oldest_expired' => Carbon::parse($stock->oldest)->format('d-m-Y'),
                'latest_expired' => Carbon::parse($stock->latest)->format('d-m-Y')
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Drug report retrieved successfully',
            'data' => $formattedStocks
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/drugs/{id}",
     *     summary="Get detailed drug report",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Drug ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Drug detail report retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Drug detail report retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="drug", type="object",
     *                     @OA\Property(property="code", type="string", example="DRG001"),
     *                     @OA\Property(property="name", type="string", example="Paracetamol"),
     *                     @OA\Property(property="category", type="string", example="Analgesic"),
     *                     @OA\Property(property="manufacture", type="string", example="PT Pharma"),
     *                     @OA\Property(property="variant", type="string", example="Tablet"),
     *                     @OA\Property(property="remaining_stock", type="integer", example=100)
     *                 ),
     *                 @OA\Property(property="repacks", type="array", @OA\Items(
     *                     @OA\Property(property="name", type="string", example="Paracetamol 500mg"),
     *                     @OA\Property(property="margin", type="number", format="float", example=20),
     *                     @OA\Property(property="stock_conversion", type="integer", example=50),
     *                     @OA\Property(property="selling_price", type="number", format="float", example=5000)
     *                 )),
     *                 @OA\Property(property="expired_details", type="array", @OA\Items(
     *                     @OA\Property(property="expired_date", type="string", format="date", example="2024-12-31"),
     *                     @OA\Property(property="quantity", type="integer", example=50)
     *                 )),
     *                 @OA\Property(property="transactions", type="array", @OA\Items(
     *                     @OA\Property(property="date", type="string", format="date-time", example="2024-01-01 10:00:00"),
     *                     @OA\Property(property="name", type="string", example="Paracetamol 500mg"),
     *                     @OA\Property(property="margin", type="number", format="float", example=20),
     *                     @OA\Property(property="price", type="number", format="float", example=5000),
     *                     @OA\Property(property="quantity", type="integer", example=10),
     *                     @OA\Property(property="status", type="string", example="Masuk"),
     *                     @OA\Property(property="subtotal", type="number", format="float", example=50000)
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Drug not found"
     *     )
     * )
     */
    public function getDrugDetailReport($id)
    {
        $drug = Drug::findOrFail($id);
        $stock = Warehouse::where('drug_id', $drug->id)->first();
        $inflow = Transaction::where('variant', 'LPB')->pluck('id');
        
        $details = TransactionDetail::where('drug_id', $drug->id)
            ->whereIn('transaction_id', $inflow)
            ->whereNot('stock', 0)
            ->orderBy('expired')
            ->get();

        $transactions = TransactionDetail::with('transaction')
            ->where('drug_id', $drug->id)
            ->get();

        $formattedDetails = $details->map(function ($detail) use ($drug) {
            return [
                'expired_date' => Carbon::parse($detail->expired)->format('Y-m-d'),
                'quantity' => floor($detail->stock / $drug->piece_netto)
            ];
        });

        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'date' => Carbon::parse($transaction->created_at)->format('Y-m-d H:i:s'),
                'name' => $transaction->name,
                'margin' => $transaction->margin ?? 0,
                'price' => $transaction->piece_price,
                'quantity' => $transaction->quantity,
                'status' => $this->getTransactionStatus($transaction->transaction->variant),
                'subtotal' => $transaction->total_price
            ];
        });

        $formattedRepacks = $drug->repacks()->map(function ($repack) use ($stock, $drug) {
            return [
                'name' => $repack->name,
                'margin' => $repack->margin,
                'stock_conversion' => floor($stock->quantity / $repack->quantity),
                'selling_price' => $repack->price
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Drug detail report retrieved successfully',
            'data' => [
                'drug' => [
                    'code' => $drug->code,
                    'name' => $drug->name,
                    'category' => $drug->category()->name,
                    'manufacture' => $drug->manufacture()->name,
                    'variant' => $drug->variant()->name,
                    'remaining_stock' => floor($stock->quantity / $drug->piece_netto)
                ],
                'repacks' => $formattedRepacks,
                'expired_details' => $formattedDetails,
                'transactions' => $formattedTransactions
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/transactions",
     *     summary="Get transaction report",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         description="Start date for filtering transactions",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end",
     *         in="query",
     *         description="End date for filtering transactions",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction report retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transaction report retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="code", type="string", example="TRX-1234567890"),
     *                 @OA\Property(property="date", type="string", format="date-time", example="2024-01-01 10:00:00"),
     *                 @OA\Property(property="variant", type="string", example="LPB"),
     *                 @OA\Property(property="outcome", type="number", format="float", example=100000)
     *             ))
     *         )
     *     )
     * )
     */
    public function getTransactionReport(Request $request)
    {
        $query = Transaction::query();

        if ($request->has('start') && $request->has('end')) {
            $end = Carbon::parse($request->end)->endOfDay();
            $query->whereBetween('created_at', [$request->start, $end]);
        }

        $perPage = $request->input('per_page', 10);
        $transactions = $query->paginate($perPage);

        $formattedTransactions = $transactions->map(function ($transaction) {
            $outcome = match ($transaction->variant) {
                'LPB' => $transaction->outcome,
                'LPK' => $transaction->detail->sum('total_price'),
                'Checkout' => $transaction->income,
                'Retur' => 0,
                'Trash' => -$transaction->loss,
                default => 0
            };

            return [
                'code' => $transaction->code,
                'date' => Carbon::parse($transaction->created_at)->format('Y-m-d H:i:s'),
                'variant' => $transaction->variant,
                'outcome' => $outcome
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction report retrieved successfully',
            'data' => $formattedTransactions
        ]);
    }

    private function getTransactionStatus($variant)
    {
        return match ($variant) {
            'LPB' => 'Masuk',
            'LPK' => 'Klinik',
            'Checkout' => 'Keluar',
            'Trash' => 'Buang',
            'Retur' => 'Retur',
            default => 'Tidak diketahui',
        };
    }
} 