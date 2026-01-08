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
use App\Http\Controllers\ImportLogController;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SaasPaymentController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\TelegramWebAppController;

Route::post('/subscribe', [SubscriberController::class, 'store'])->name('subscribe.store');

//Route::get('setwebhook', function () {
//    $response = Telegram::setWebhook(['url' =>'https://esuv.uz/api/telegram/webhook']);
//});

Route::get('/', [HomeController::class, 'index'])->name('home');

// Telegram WebApp Routes
Route::prefix('telegram-webapp')->middleware(['telegram.webapp'])->group(function () {
    Route::get('/', [TelegramWebAppController::class, 'index'])->name('telegram-webapp.index');
    Route::post('/auth', [TelegramWebAppController::class, 'authenticate'])->name('telegram-webapp.auth');
    Route::post('/logout', [TelegramWebAppController::class, 'logout'])->name('telegram-webapp.logout');
    Route::get('/user', [TelegramWebAppController::class, 'user'])->name('telegram-webapp.user');
});

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
        Route::resource('saas-payments', SaasPaymentController::class)->names('saas.payments');
        Route::get('/saas-payments-history', [SaasPaymentController::class, 'history'])
            ->name('saas.payments.history');
    });
//    Route::middleware('can:locations')->group(function () {
//    });

    Route::middleware('can:dashboard')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('can:customers')->group(function () {
        Route::get('/customers/import', [CustomerController::class, 'showImportForm'])->name('customers.import.form');

        // HISOBlagichi YO'Qlar uchun marshrut
        Route::post('/customers/import/no-meter', [CustomerController::class, 'handleImportNoMeter'])->name('customers.import.handle.no_meter');

        // HISOBlagichi BORlar uchun marshrut
        Route::post('/customers/import/with-meter', [CustomerController::class, 'handleImportWithMeter'])->name('customers.import.handle.with_meter');
        Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
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
    Route::patch('payments/{payment}/confirm', [PaymentController::class, 'confirm'])
        ->name('payments.confirm')
        ->middleware('role:company_owner');

    // Ko'plab to'lovlarni tasdiqlash
    Route::post('payments/confirm-multiple', [PaymentController::class, 'confirmMultiple'])
        ->name('payments.confirm-multiple')
        ->middleware('role:company_owner');

    // ✅ Kunlik hisobot (faqat direktor)
    Route::get('daily-reports', [DailyReportController::class, 'index'])
        ->name('daily-reports.index')
        ->middleware('role:admin,company_owner');

    Route::middleware('can:water_meters')->group(function () {
        Route::resource('water_meters', WaterMeterController::class);
    });
    Route::middleware('can:meter_readings')->group(function () {
        Route::resource('meter_readings', MeterReadingController::class);
        Route::patch('/meter_readings/{id}/confirm', [MeterReadingController::class, 'confirm'])->name('meter_readings.confirm');

        // Ko'rsatkichlarni import qilish
        Route::get('/meter-readings/import/form', [MeterReadingController::class, 'showImportForm'])->name('meter_readings.import.form');
        Route::post('/meter-readings/import', [MeterReadingController::class, 'handleImport'])->name('meter_readings.import');
    });
    Route::middleware('can:users')->group(function () {
        Route::resource('users', UserController::class);
    });
    Route::middleware('can:notifications')->group(function () {
        Route::resource('notifications', NotificationController::class);
    });

    // Import loglarini ko'rish (admin va company_owner uchun)
    Route::middleware('role:admin|company_owner')->group(function () {
        Route::get('/import-logs', [ImportLogController::class, 'index'])->name('import_logs.index');
        Route::get('/import-logs/{importLog}', [ImportLogController::class, 'show'])->name('import_logs.show');
        Route::get('/import-logs/{importLog}/export-errors', [ImportLogController::class, 'exportErrors'])->name('import_logs.export_errors');
    });
});

