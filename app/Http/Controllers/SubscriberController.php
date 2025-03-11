<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;
use Telegram\Bot\Laravel\Facades\Telegram;

class SubscriberController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'required|regex:/^\+?\d{9,15}$/|unique:subscribers,phone'
        ]);

        $subscriber = Subscriber::create([
            'phone' => $request->phone
        ]);

        // Telegramga yuborish
        $this->sendToTelegram($subscriber->phone);

        return redirect()->back()->with('success', 'Telefon raqamingiz muvaffaqiyatli saqlandi!');
    }

    private function sendToTelegram($phone)
    {
        $chatId = 1002465975679;

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "📲 Yangi obunachi: <b>{$phone}</b>",
            'parse_mode' => 'HTML',
        ]);
    }
}
