<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Transaction\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="API Endpoints for dashboard data and analytics"
 * )
 */
class DashboardController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/obat",
     *     summary="Get top selling drugs",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Top selling drugs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Top selling drugs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="drug_name", type="string", example="Paracetamol"),
     *                 @OA\Property(property="total_sold", type="integer", example=100)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function obat()
    {
        try {
            $transactions = DB::table('transaction_details')
            ->select(
                'drugs.name as drug_name', 
                DB::raw('SUM(CASE 
                            WHEN transactions.variant = "Checkout" 
                            THEN transaction_details.quantity 
                            ELSE 0 
                        END) as total_sold')
            )
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('repacks', 'transaction_details.name', '=', 'repacks.name')
            ->join('drugs', 'repacks.drug_id', '=', 'drugs.id')
            ->groupBy('drugs.id', 'drugs.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

            return $this->successResponse($transactions, 'Top selling drugs retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve top selling drugs: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/penjualan",
     *     summary="Get weekly sales data",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Weekly sales data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Weekly sales data retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="object",
     *                     @OA\Property(property="Monday", type="number", format="float", example=1000000),
     *                     @OA\Property(property="Tuesday", type="number", format="float", example=1500000),
     *                     @OA\Property(property="Wednesday", type="number", format="float", example=2000000),
     *                     @OA\Property(property="Thursday", type="number", format="float", example=1800000),
     *                     @OA\Property(property="Friday", type="number", format="float", example=2200000),
     *                     @OA\Property(property="Saturday", type="number", format="float", example=2500000),
     *                     @OA\Property(property="Sunday", type="number", format="float", example=1200000)
     *                 ),
     *                 @OA\Property(property="highest_profit", type="number", format="float", example=2500000),
     *                 @OA\Property(property="highest_day", type="string", example="Saturday")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function penjualan()
    {
        try {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = $startDate->copy()->addDays(6)->endOfDay();
    
            $transactions = DB::table('transactions')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(CASE 
                                WHEN variant = "LPB" THEN outcome
                                WHEN variant = "LPK" THEN (SELECT SUM(total_price) FROM transaction_details WHERE transaction_id = transactions.id)
                                WHEN variant = "Checkout" THEN income
                                WHEN variant = "Trash" THEN -loss
                                ELSE 0 
                            END) as total_profit')
                )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get()
                ->keyBy('date'); 
    
            $result = [];
            $highestProfit = 0;
            $highestDay = '';
    
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
            for ($i = 0; $i < 7; $i++) {
                $currentDate = $startDate->copy()->addDays($i);
                $formattedDate = $currentDate->toDateString();
                $dayName = $days[$i];
    
                $totalProfit = $transactions->has($formattedDate) ? $transactions[$formattedDate]->total_profit : 0;
                $result[$dayName] = $totalProfit;
    
                if ($totalProfit > $highestProfit) {
                    $highestProfit = $totalProfit;
                    $highestDay = $dayName;
                }
            }
    
            return $this->successResponse([
                'data' => $result,
                'highest_profit' => $highestProfit,
                'highest_day' => $highestDay
            ], 'Weekly sales data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve sales data: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/histories",
     *     summary="Get recent transaction histories",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=30)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction histories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transaction histories retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(
     *                     @OA\Property(property="No Transaksi", type="string", example="TRX-1234567890"),
     *                     @OA\Property(property="Date", type="string", format="date-time", example="2024-01-01 10:00:00"),
     *                     @OA\Property(property="Subtotal", type="number", format="float", example=100000)
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function histories()
    {
        try {
            $histories = Transaction::where('variant', 'Checkout')
                ->select('code', 'created_at', 'income')
                ->latest()
                ->paginate(30);

            $formattedHistories = $histories->map(function ($transaction) {
                return [
                    'No Transaksi' => $transaction->code,
                    'Date' => $transaction->created_at,
                    'Subtotal' => $transaction->income
                ];
            });

            return $this->successResponse([
                'data' => $formattedHistories,
            ], 'Transaction histories retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve transaction histories: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/due-bills",
     *     summary="Get due bills",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Due bills retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Due bills retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="vendor_name", type="string", example="PT Supplier"),
     *                 @OA\Property(property="due", type="string", format="date", example="2024-01-31")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function dueBills()
    {
        try {
            $jatuhTempo = DB::table('bills')
                ->join('transactions', 'bills.transaction_id', '=', 'transactions.id')
                ->join('vendors', 'transactions.vendor_id', '=', 'vendors.id')
                ->select('bills.id', 'vendors.name as vendor_name', 'bills.due')
                ->where('bills.status', 'Belum Bayar')
                ->orderBy('bills.due', 'asc')
                ->get();

            return $this->successResponse($jatuhTempo, 'Due bills retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve due bills: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/low-stock",
     *     summary="Get low stock alerts",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Low stock alerts retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Low stock alerts retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="warehouse", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="drug_name", type="string", example="Paracetamol"),
     *                     @OA\Property(property="quantity", type="integer", example=10),
     *                     @OA\Property(property="location", type="string", example="Gudang")
     *                 )),
     *                 @OA\Property(property="clinic", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="drug_name", type="string", example="Paracetamol"),
     *                     @OA\Property(property="quantity", type="integer", example=5),
     *                     @OA\Property(property="location", type="string", example="Klinik")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function lowStock()
    {
        try {
            $gudangMenipis = DB::table('warehouse_inventory')
                ->join('drugs', 'warehouse_inventory.drug_id', '=', 'drugs.id')
                ->whereColumn('warehouse_inventory.quantity', '<=', 'drugs.minimum_capacity')
                ->select('warehouse_inventory.id', 'drugs.name as drug_name', 'warehouse_inventory.quantity', DB::raw('"Gudang" as location'))
                ->orderBy('warehouse_inventory.quantity', 'asc')
                ->get();

            $klinikMenipis = DB::table('clinic_inventory')
                ->join('drugs', 'clinic_inventory.drug_id', '=', 'drugs.id')
                ->whereColumn('clinic_inventory.quantity', '<=', 'drugs.minimum_capacity')
                ->select('clinic_inventory.id', 'drugs.name as drug_name', 'clinic_inventory.quantity', DB::raw('"Klinik" as location'))
                ->orderBy('clinic_inventory.quantity', 'asc')
                ->get();

            return $this->successResponse([
                'warehouse' => $gudangMenipis,
                'clinic' => $klinikMenipis
            ], 'Low stock alerts retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve low stock alerts: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/expiring",
     *     summary="Get expiring drugs",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Expiring drugs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Expiring drugs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="drug_name", type="string", example="Paracetamol"),
     *                 @OA\Property(property="expired", type="string", format="date", example="2024-01-31")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function expiring()
    {
        try {
            $expired = DB::table('transaction_details')
                ->join('drugs', 'transaction_details.drug_id', '=', 'drugs.id')
                ->where('transaction_details.expired', '<=', now()->addDays(30))
                ->select('drugs.name as drug_name', 'transaction_details.expired')
                ->groupBy('drugs.name', 'transaction_details.expired')
                ->orderBy('transaction_details.expired', 'asc')
                ->get();

            return $this->successResponse($expired, 'Expiring drugs retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve expiring drugs: ' . $e->getMessage(), [], 500);
        }
    }
} 