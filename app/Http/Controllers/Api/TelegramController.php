<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        // Kelayotgan update'larni Telegram kutubxonasi orqali boshqarish
        $update = Telegram::commandsHandler(true);
        // Qo‘shimcha logika yoki javoblar qo‘shishingiz mumkin

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook received'
        ]);
    }
}
