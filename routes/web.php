<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\NeighborhoodController;
use App\Http\Controllers\StreetController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TariffController;
use App\Http\Controllers\WaterMeterController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use Telegram\Bot\Laravel\Facades\Telegram;


Route::get('setwebhook', function () {
    $response = Telegram::setWebhook(['url' =>'https://cc80-213-230-88-246.ngrok-free.app/api/telegram/webhook']);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/', [HomeController::class, 'index'])->name('home');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('companies', CompanyController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('tariffs', TariffController::class);
    Route::resource('invoices', InvoiceController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('regions', RegionController::class);
    Route::resource('cities', CityController::class);
    Route::resource('neighborhoods', NeighborhoodController::class);
    Route::resource('streets', StreetController::class);
    Route::resource('water_meters', WaterMeterController::class);
    Route::resource('meter_readings', MeterReadingController::class);
    Route::resource('users', UserController::class);
    Route::resource('notifications', NotificationController::class);
});

