<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | API Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register API routes for your application. These
 * | routes are loaded by the RouteServiceProvider and all of them will
 * | be assigned to the "api" middleware group. Make something great!
 * |
 */

// Public routes (no authentication required)
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}', [CategoryController::class, 'show']);

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile/{id}', [AuthController::class, 'update']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Read-only routes for all users
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::get('suppliers', [SupplierController::class, 'index']);
    Route::get('suppliers/{id}', [SupplierController::class, 'show']);
    Route::get('staffs', [StaffController::class, 'index']);
    Route::get('staffs/{id}', [StaffController::class, 'show']);
    Route::get('customers', [CustomerController::class, 'index']);
    Route::get('customers/{id}', [CustomerController::class, 'show']);
    Route::get('brands', [BrandController::class, 'index']);
    Route::get('brands/{id}', [BrandController::class, 'show']);
    // Route::get('products', [ProductController::class, 'index']);  // Moved to public routes
    // Route::get('products/{id}', [ProductController::class, 'show']);  // Moved to public routes
    // Route::get('categories', [CategoryController::class, 'index']);  // Moved to public routes
    // Route::get('categories/{id}', [CategoryController::class, 'show']);  // Moved to public routes
    Route::get('imports', [ImportController::class, 'index']);
    Route::get('imports/{id}', [ImportController::class, 'show']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::get('payments', [PaymentController::class, 'index']);
    Route::get('payments/{id}', [PaymentController::class, 'show']);

    // Admin-only routes for CUD operations
    Route::middleware('admin')->group(function () {
        Route::post('users', [UserController::class, 'store']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);

        Route::post('suppliers', [SupplierController::class, 'store']);
        Route::put('suppliers/{id}', [SupplierController::class, 'update']);
        Route::delete('suppliers/{id}', [SupplierController::class, 'destroy']);

        Route::post('staffs', [StaffController::class, 'store']);
        Route::put('staffs/{id}', [StaffController::class, 'update']);
        Route::delete('staffs/{id}', [StaffController::class, 'destroy']);

        Route::post('customers', [CustomerController::class, 'store']);
        Route::put('customers/{id}', [CustomerController::class, 'update']);
        Route::delete('customers/{id}', [CustomerController::class, 'destroy']);

        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{id}', [CategoryController::class, 'update']);
        Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

        Route::post('brands', [BrandController::class, 'store']);
        Route::put('brands/{id}', [BrandController::class, 'update']);
        Route::delete('brands/{id}', [BrandController::class, 'destroy']);

        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);

        Route::post('imports', [ImportController::class, 'store']);
        Route::put('imports/{id}', [ImportController::class, 'update']);
        Route::delete('imports/{id}', [ImportController::class, 'destroy']);

        Route::post('orders', [OrderController::class, 'store']);
        Route::put('orders/{id}', [OrderController::class, 'update']);
        Route::delete('orders/{id}', [OrderController::class, 'destroy']);
        Route::delete('orders/{id}/force', [OrderController::class, 'forceDestroy']);

        Route::post('payments', [PaymentController::class, 'store']);
        Route::put('payments/{id}', [PaymentController::class, 'update']);
        Route::delete('payments/{id}', [PaymentController::class, 'destroy']);
    });

    // Report routes
    Route::prefix('reports')->group(function () {
        Route::get('import-report', [ReportController::class, 'importReport']);
        Route::get('sales-report', [ReportController::class, 'salesReport']);
        Route::get('import-summary', [ReportController::class, 'importSummary']);
        Route::get('sales-summary', [ReportController::class, 'salesSummary']);
        Route::get('export-import-excel', [ReportController::class, 'exportImportExcel']);
        Route::get('export-sales-excel', [ReportController::class, 'exportSalesExcel']);
        Route::get('export-import-pdf', [ReportController::class, 'exportImportPdf']);
        Route::get('export-sales-pdf', [ReportController::class, 'exportSalesPdf']);
        Route::get('export-single-import-word', [ReportController::class, 'exportSingleImportWord']);
        Route::get('export-single-sales-word', [ReportController::class, 'exportSingleSalesWord']);
    });

    // Additional payment routes
    Route::get('payments/pending', [PaymentController::class, 'getPendingPayments']);
    Route::get('payments/summary', [PaymentController::class, 'getPaymentSummary']);
    Route::get('payments/order/{orderId}/status', [PaymentController::class, 'getOrderPaymentStatus']);
    Route::post('payments/cleanup', [PaymentController::class, 'cleanupPaymentData']);
});