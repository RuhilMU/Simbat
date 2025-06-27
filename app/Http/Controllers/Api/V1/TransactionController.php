<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionDetail;
use App\Models\Master\Drug;
use App\Models\Master\Repack;
use App\Models\Master\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="API Endpoints for managing transactions"
 * )
 */
class TransactionController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/transactions/top-selling",
     *     summary="Get top selling items for a period",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         description="Start date for analysis",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         description="End date for analysis",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of top items to return",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Top selling items retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Top selling items retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="drug_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Paracetamol 500mg"),
     *                 @OA\Property(property="total_quantity", type="number", format="float", example=80),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=400000),
     *                 @OA\Property(property="drug", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="PC0001"),
     *                     @OA\Property(property="name", type="string", example="Paracetamol 500mg"),
     *                     @OA\Property(property="category", type="object", nullable=true,
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Analgesik"),
     *                         @OA\Property(property="code", type="string", example="AG")
     *                     ),
     *                     @OA\Property(property="manufacture", type="object", nullable=true,
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Kimia Farma")
     *                     ),
     *                     @OA\Property(property="variant", type="object", nullable=true,
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Tablet")
     *                     ),
     *                     @OA\Property(property="piece_netto", type="integer", example=500),
     *                     @OA\Property(property="piece_price", type="integer", example=5000),
     *                     @OA\Property(property="last_price", type="integer", example=5000)
     *                 )
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function topSelling(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        $limit = $request->limit ?? 10;

        $topSelling = TransactionDetail::whereHas('transaction', function($query) use ($request) {
                            $query->whereBetween('created_at', [$request->start_date, $request->end_date])
                                  ->where('variant', 'Checkout'); // Only include checkout transactions
                        })
                        ->select(
                            'drug_id',
                            DB::raw('SUM(CAST(REPLACE(quantity, " pcs", "") AS DECIMAL(10,2))) as total_quantity'),
                            DB::raw('SUM(total_price) as total_amount'),
                            DB::raw('MAX(name) as name') // Get the drug name
                        )
                        ->groupBy('drug_id')
                        ->orderBy('total_quantity', 'desc')
                        ->limit($limit)
                        ->get();

        // Get drug information separately
        $drugIds = $topSelling->pluck('drug_id')->unique();
        $drugs = Drug::whereIn('id', $drugIds)->get();

        // Combine the data
        $result = $topSelling->map(function($item) use ($drugs) {
            $drug = $drugs->firstWhere('id', $item->drug_id);
            return [
                'drug_id' => $item->drug_id,
                'name' => $item->name,
                'total_quantity' => $item->total_quantity,
                'total_amount' => $item->total_amount,
                'drug' => $drug ? [
                    'id' => $drug->id,
                    'code' => $drug->code,
                    'name' => $drug->name,
                    'category' => $drug->category(),
                    'manufacture' => $drug->manufacture(),
                    'variant' => $drug->variant(),
                    'piece_netto' => $drug->piece_netto,
                    'piece_price' => $drug->piece_price,
                    'last_price' => $drug->last_price,
                ] : null
            ];
        });

        return $this->successResponse($result, 'Top selling items retrieved successfully');
    }
} 