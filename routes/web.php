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
use App\Http\Controllers\SubscriberController;

Route::post('/subscribe', [SubscriberController::class, 'store'])->name('subscribe.store');

//Route::get('setwebhook', function () {
//    $response = Telegram::setWebhook(['url' =>'https://esuv.uz/api/telegram/webhook']);
//});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('can:companies')->group(function () {
        Route::resource('companies', CompanyController::class);
        Route::resource('regions', RegionController::class);
        Route::resource('cities', CityController::class);
        Route::resource('neighborhoods', NeighborhoodController::class);
        Route::resource('streets', StreetController::class);
    });
//    Route::middleware('can:locations')->group(function () {
//    });

    Route::middleware('can:dashboard')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('can:customers')->group(function () {
        // Mijozlarni import qilish formasini ko'rsatish uchun marshrut
        Route::get('/customers/import', [CustomerController::class, 'showImportForm'])
            ->name('customers.import.form');
        // Yuklangan Excel faylini qayta ishlash uchun marshrut
        Route::post('/customers/import', [CustomerController::class, 'handleImport'])
            ->name('customers.import.handle');

        Route::resource('customers', CustomerController::class);
        Route::delete('/customers/{customer}/telegram/{telegram}', [CustomerController::class, 'detachTelegramAccount'])
            ->name('customers.detachTelegram');
    });

    Route::middleware('can:tariffs')->group(function () {
        Route::resource('tariffs', TariffController::class);
    });
    Route::middleware('can:invoices')->group(function () {
        Route::resource('invoices', InvoiceController::class);
    });
    Route::middleware('can:payments')->group(function () {
        Route::resource('payments', PaymentController::class);
    });
    Route::middleware('can:water_meters')->group(function () {
        Route::resource('water_meters', WaterMeterController::class);
    });
    Route::middleware('can:meter_readings')->group(function () {
        Route::resource('meter_readings', MeterReadingController::class);
        Route::patch('/meter_readings/{id}/confirm', [MeterReadingController::class, 'confirm'])->name('meter_readings.confirm');
    });
    Route::middleware('can:users')->group(function () {
        Route::resource('users', UserController::class);
    });
    Route::middleware('can:notifications')->group(function () {
        Route::resource('notifications', NotificationController::class);
    });
});

