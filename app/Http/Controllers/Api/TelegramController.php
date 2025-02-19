<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request)
    {
//        $update = Telegram::commandsHandler(true); // Telegramdan kelgan ma'lumot
//
//        if (isset($update['message'])) {
//            $chatId = $update['message']['chat']['id'];
//            $text = $update['message']['text'];
//
//            if ($text === "/start") {
//                $this->sendMessage($chatId, "Salom! Bu ESuv bot.");
//            } else {
//                $this->sendMessage($chatId, "Siz yozgan matn: " . $text);
//            }
//        }

//        return response()->json(['status' => 'success']);
        return response()->json([
            'status' => 'success',
            'message' => 'Webhook received'
        ]);
    }

    private function sendMessage($chatId, $text)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }
}
