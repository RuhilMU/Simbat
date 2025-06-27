<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DrugController;
use App\Http\Controllers\Api\V1\ManufactureController;
use App\Http\Controllers\Api\V1\RepackController;
use App\Http\Controllers\Api\V1\VendorController;
use App\Http\Controllers\Api\V1\VariantController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ManagementController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// API V1 Routes
Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // Auth routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);

        // User management routes
        Route::apiResource('users', UserController::class);
        Route::put('/profile', [UserController::class, 'updateProfile']);

        // Master data routes
        Route::apiResource('categories', CategoryController::class);
        Route::get('/categories/search', [CategoryController::class, 'search']);

        // Drugs
        Route::apiResource('drugs', DrugController::class);
        Route::get('/drugs/search', [DrugController::class, 'search']);

        // Manufacturers
        Route::apiResource('manufactures', ManufactureController::class);
        Route::get('manufactures/search', [ManufactureController::class, 'search']);

        // Variants
        Route::apiResource('variants', VariantController::class);
        Route::get('variants/search', [VariantController::class, 'search']);

        // Vendors
        Route::apiResource('vendors', VendorController::class);
        Route::get('vendors/search', [VendorController::class, 'search']);

        // Repacks
        Route::apiResource('repacks', RepackController::class);
        Route::get('repacks/search', [RepackController::class, 'search']);

        // ==================== Dashboard Routes ====================
        Route::prefix('dashboard')->group(function () {
            Route::get('/obat', [DashboardController::class, 'obat']);
            Route::get('/penjualan', [DashboardController::class, 'penjualan']);
            Route::get('/histories', [DashboardController::class, 'histories']);
            Route::get('/due-bills', [DashboardController::class, 'dueBills']);
            Route::get('/low-stock', [DashboardController::class, 'lowStock']);
            Route::get('/expiring', [DashboardController::class, 'expiring']);
        });

        // ==================== Inventory Routes ====================
        Route::prefix('inventory')->group(function () {
            // Inflows
            Route::get('/inflows', [InventoryController::class, 'getInflows']);
            Route::get('/inflows/{id}', [InventoryController::class, 'getInflowDetail']);
            Route::post('/inflows', [InventoryController::class, 'createInflow']);

            // Clinic Inflows
            Route::get('/clinic-inflows', [InventoryController::class, 'getClinicInflows']);
            Route::get('/clinic-inflows/{id}', [InventoryController::class, 'getClinicInflowDetail']);
            Route::post('/clinic-inflows', [InventoryController::class, 'createClinicInflow']);

            // Vendors & Drugs
            Route::get('/vendors', [InventoryController::class, 'getVendors']);
            Route::get('/drugs', [InventoryController::class, 'getDrugs']);

            // Warehouse Stocks
            Route::get('/stocks', [InventoryController::class, 'getStocks']);
            Route::get('/stocks/{id}', [InventoryController::class, 'getStockDetail']);
            Route::get('/stocks/search', [InventoryController::class, 'searchStocks']);

            // Clinic Stocks
            Route::get('/clinic-stocks', [InventoryController::class, 'getClinicStocks']);
            Route::get('/clinic-stocks/{id}', [InventoryController::class, 'getClinicStockDetail']);
            Route::get('/clinic-stocks/search', [InventoryController::class, 'searchClinicStocks']);
            Route::post('/transfer-to-clinic', [InventoryController::class, 'transferToClinic']);
        });

        // Management routes
        Route::prefix('management')->group(function () {
            // Bills
            Route::get('/search', [ManagementController::class, 'searchManagement']);
            Route::get('/bills', [ManagementController::class, 'getBills']);
            Route::get('/bills/{id}', [ManagementController::class, 'getBillDetail']);
            Route::post('/bills/{id}/pay', [ManagementController::class, 'payBill']);

            // Returns
            Route::get('/returns', [ManagementController::class, 'getReturns']);
            Route::get('/returns/{id}', [ManagementController::class, 'getReturnDetail']);
            Route::post('/returns', [ManagementController::class, 'createReturn']);
            Route::post('/returns/{id}/complete', [ManagementController::class, 'completeReturn']);

            // Trash
            Route::get('/trash', [ManagementController::class, 'getTrash']);
            Route::get('/trash/{id}', [ManagementController::class, 'getTrashDetail']);
            Route::post('/trash', [ManagementController::class, 'createTrash']);
        });

        // Report Routes
        Route::prefix('reports')->group(function () {
            Route::get('/drugs', [ReportController::class, 'getDrugReport']);
            Route::get('/drugs/{id}', [ReportController::class, 'getDrugDetailReport']);
            Route::get('/transactions', [ReportController::class, 'getTransactionReport']);
            Route::get('/transactions/search', [ReportController::class, 'searchTransactions']);
        });

        // Checkout routes
        Route::prefix('checkout')->group(function () {
            Route::get('/drugs', [CheckoutController::class, 'getAvailableDrugs']);
            Route::post('/', [CheckoutController::class, 'checkout']);
            Route::get('/history', [CheckoutController::class, 'history']);
        });

        // Transaction routes
        Route::prefix('transactions')->group(function () {
            Route::get('/top-selling', [TransactionController::class, 'topSelling']);
        });
    });
});

