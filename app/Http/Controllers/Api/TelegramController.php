<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendTelegramMessageJob;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function handleUpdates()
    {
//        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
//        $updates = $telegram->getUpdates();
//
//        foreach ($updates as $update) {
//            $chatId = $update['message']['chat']['id'];
//            $text = $update['message']['text'];
//
//            if ($text == "/start") {
//                $telegram->sendMessage([
//                    'chat_id' => $chatId,
//                    'text' => "Assalomu alaykum! Bot test rejimida ishlayapti."
//                ]);
//            }
//        }

//        return response()->json($updates);
    }


    public function handleWebhook(Request $request)
    {
        $update = Telegram::getWebhookUpdate();
        $chatId = $update['message']['chat']['id'] ?? null;
        $userId = $update['message']['from']['id'] ?? null;
        $text = trim($update['message']['text'] ?? '');

        // Agar callback tugma bosilgan bo‘lsa
        if (isset($update['callback_query'])) {
            $callbackData = $update['callback_query']['data'];
            $chatId = $update['callback_query']['message']['chat']['id'];

            // Mijoz ma'lumotlarini olish
            $customer = Customer::whereHas('telegramAccounts', function ($query) use ($chatId) {
                $query->where('telegram_chat_id', $chatId);
            })->first();

            if (!$customer) {
                $this->sendMessage($chatId, "❌ Siz bog‘langan mijoz topilmadi.");
                return;
            }

            // Tugmalarning ishlashini ta'minlash
            if ($callbackData == "payments") {
                $payments = $customer->payments()->orderByDesc('created_at')->limit(5)->get();
                $message = "💰 So‘nggi to‘lovlaringiz:\n";
                foreach ($payments as $payment) {
                    $message .= "- {$payment->amount} so‘m ({$payment->created_at->format('d.m.Y')})\n";
                }
                $this->sendMessage($chatId, $message ?: "Sizda to‘lovlar mavjud emas.");
            } elseif ($callbackData == "debts") {
                $debt = $customer->invoices()->where('status', 'unpaid')->sum('amount_due');
                $message = $debt > 0 ? "📊 Sizning jami qarzingiz: {$debt} so‘m" : "✅ Sizda qarzdorlik mavjud emas!";
                $this->sendMessage($chatId, $message);
            } elseif ($callbackData == "account_info") {
                $message = "🏠 Sizning hisob ma’lumotlaringiz:\n";
                $message .= "🔢 Hisob raqami: {$customer->account_number}\n";
                $message .= "📍 Manzil: {$customer->address}\n";
                $message .= "💳 Balans: {$customer->balance} so‘m\n";
                $this->sendMessage($chatId, $message);
            }
            return;
        }

        // Agar xabar matni /start bo'lsa
        if (strcasecmp($text, "/start") === 0) {
            $this->sendMessage($chatId, "💳 Hisob raqamingizni kiriting:");
            return;
        }

        // Agar foydalanuvchi hisob raqam kiritayotgan bo‘lsa
        if (is_numeric($text)) {
            $customer = Customer::where('account_number', $text)->first();

            if ($customer) {
                // Foydalanuvchi oldin bog‘langanmi?
                $exists = $customer->telegramAccounts()->where('telegram_chat_id', $userId)->exists();

                if (!$exists) {
                    // Yangi Telegram ID ni bog‘lash
                    $customer->telegramAccounts()->create(['telegram_chat_id' => $userId]);

                    // Tugmalar bilan javob qaytarish
                    $this->sendMessage($chatId, "✅ Sizning hisobingiz bog‘landi! Quyidagi tugmalardan foydalaning.", [
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [['text' => '💳 To‘lovlarim', 'callback_data' => 'payments']],
                                [['text' => '📊 Qarzlarim', 'callback_data' => 'debts']],
                                [['text' => 'ℹ️ Hisob ma’lumotlari', 'callback_data' => 'account_info']]
                            ]
                        ])
                    ]);
                } else {
                    $this->sendMessage($chatId, "✅ Siz allaqachon ushbu hisobga bog‘langansiz! Quyidagi tugmalardan foydalaning.", [
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [['text' => '💳 To‘lovlarim', 'callback_data' => 'payments']],
                                [['text' => '📊 Qarzlarim', 'callback_data' => 'debts']],
                                [['text' => 'ℹ️ Hisob ma’lumotlari', 'callback_data' => 'account_info']]
                            ]
                        ])
                    ]);
                }
            } else {
                $this->sendMessage($chatId, "❌ Xatolik: Hisob raqami topilmadi. Qayta urinib ko‘ring.");
            }
            return;
        }

        // Agar foydalanuvchi boshqa matn yuborsa
        $this->sendMessage($chatId, "Noto‘g‘ri buyruq. /start bosing va hisob raqamingizni kiriting.");
    }





    public function sendMessage($chatId, $text)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }

}
