<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Controllers\Api\WaterMeterApiController;
use App\Http\Controllers\Api\MeterReadingApiController;
use App\Http\Controllers\Api\MasterDataApiController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\Api\FileUploadApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::post('/login', [AuthController::class, 'login']);

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Customers
    Route::get('/customers', [CustomerApiController::class, 'index']);
    Route::get('/customers/{id}', [CustomerApiController::class, 'show']);
    Route::post('/customers', [CustomerApiController::class, 'store']);
    Route::put('/customers/{id}', [CustomerApiController::class, 'update']);
    Route::delete('/customers/{id}', [CustomerApiController::class, 'destroy']);

    // Payments
    Route::get('/payments', [PaymentApiController::class, 'index']);
    Route::get('/payments/{id}', [PaymentApiController::class, 'show']);
    Route::post('/payments', [PaymentApiController::class, 'store']);
    Route::patch('/payments/{id}/confirm', [PaymentApiController::class, 'confirm']);

    // Invoices
    Route::get('/invoices', [InvoiceApiController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceApiController::class, 'show']);
    Route::post('/invoices', [InvoiceApiController::class, 'store']);

    // Water Meters
    Route::get('/water-meters', [WaterMeterApiController::class, 'index']);
    Route::get('/water-meters/{id}', [WaterMeterApiController::class, 'show']);
    Route::post('/water-meters', [WaterMeterApiController::class, 'store']);

    // Meter Readings
    Route::get('/meter-readings', [MeterReadingApiController::class, 'index']);
    Route::get('/meter-readings/{id}', [MeterReadingApiController::class, 'show']);
    Route::post('/meter-readings', [MeterReadingApiController::class, 'store']);
    Route::patch('/meter-readings/{id}/confirm', [MeterReadingApiController::class, 'confirm']);

    // Master Data
    Route::get('/regions', [MasterDataApiController::class, 'regions']);
    Route::get('/cities', [MasterDataApiController::class, 'cities']);
    Route::get('/neighborhoods', [MasterDataApiController::class, 'neighborhoods']);
    Route::get('/streets', [MasterDataApiController::class, 'streets']);
    Route::get('/tariffs', [MasterDataApiController::class, 'tariffs']);

    // Reports
    Route::get('/reports/daily-payments', [ReportApiController::class, 'dailyPayments']);
    Route::get('/reports/customer-debts', [ReportApiController::class, 'customerDebts']);
    Route::get('/reports/dashboard-stats', [ReportApiController::class, 'dashboardStats']);

    // File Upload
    Route::post('/upload', [FileUploadApiController::class, 'upload']);
});

// Telegram Webhook (No auth required)
Route::post('telegram/webhook', [TelegramController::class, 'handleWebhook']);
