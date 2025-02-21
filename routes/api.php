<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TelegramController;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::post('telegram/webhook', [TelegramController::class, 'handleWebhook']);

//Route::post('/telegram-webhook', function (Request $request) {
//    $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
//    $update = $telegram->commandsHandler(true);
//    return response()->json(['status' => 'ok']);
//});
//
//Route::get('setwebhook', function () {
//    $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
//    $response = $telegram->getMe();
//    return response()->json($response);

//    $response = Telegram::setWebhook(['url' =>'https://cc80-213-230-88-246.ngrok-free.app/api/telegram/webhook']);
//});
//
//Route::get('/telegram-updates', [TelegramController::class, 'handleUpdates']);
//Route::get('/send-mass-message', [TelegramController::class, 'sendMassMessage']);
