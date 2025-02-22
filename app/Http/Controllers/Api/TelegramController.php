<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $update = Telegram::getWebhookUpdate();
        $chatId = $update['message']['chat']['id'] ?? null;
        $userId = $update['message']['from']['id'] ?? null;
        $text = trim($update['message']['text'] ?? '');

        // ✅ Callback tugmalar bosilganda
        if (isset($update['callback_query'])) {
            $callbackData = $update['callback_query']['data'];
            $chatId = $update['callback_query']['message']['chat']['id'];

            switch ($callbackData) {
                case "info":
                    $this->sendCustomerInfo($chatId);
                    break;
                case "invoices":
                    $this->sendInvoices($chatId);
                    break;
                case "payments":
                    $this->sendPayments($chatId);
                    break;
            }
            return;
        }

        // ✅ Foydalanuvchi /start bosganda
        if (strcasecmp($text, "/start") === 0) {
            $this->sendMessage($chatId, "🔢 Iltimos, hisob raqamingizni kiriting:");
            return;
        }

        // ✅ Agar foydalanuvchi hisob raqam kiritayotgan bo‘lsa
        if (is_numeric($text)) {
            $this->linkAccount($chatId, $userId, $text);
            return;
        }

        // ✅ Asosiy menyudan tugmalar bosilganda
        switch ($text) {
            case "📋 Ma'lumotlarim":
                $this->sendCustomerInfo($chatId);
                break;
            case "📑 Hisob varaqalar":
                $this->sendInvoices($chatId);
                break;
            case "💳 To‘lovlarim":
                $this->sendPayments($chatId);
                break;
            default:
                $this->sendMessage($chatId, "❌ Noto‘g‘ri buyruq. Iltimos, tugmalardan foydalaning.");
        }
    }

    // ✅ Hisob raqamini Telegramga bog‘lash
    private function linkAccount($chatId, $userId, $accountNumber)
    {
        $customer = Customer::where('account_number', $accountNumber)->first();

        if (!$customer) {
            $this->sendMessage($chatId, "❌ Xatolik: Hisob raqami topilmadi. Qayta urinib ko‘ring.");
            return;
        }

        // Foydalanuvchi oldin bog‘langanmi?
        $exists = $customer->telegramAccounts()->where('telegram_chat_id', $userId)->exists();

        if (!$exists) {
            // Yangi Telegram ID ni bog‘lash
            $customer->telegramAccounts()->create(['telegram_chat_id' => $userId]);
            $this->sendMessage($chatId, "✅ Hisobingiz bog‘landi! Quyidagi menyudan foydalaning.");
        } else {
            $this->sendMessage($chatId, "✅ Siz allaqachon ushbu hisobga bog‘langansiz.");
        }

        // Asosiy menyuni chiqarish
        $this->sendMainMenu($chatId);
    }

    // ✅ Asosiy menyuni jo‘natish
    private function sendMainMenu($chatId)
    {
        $menu = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false)
            ->row([
                Keyboard::button('📋 Ma\'lumotlarim'),
                Keyboard::button('📑 Hisob varaqalar'),
                Keyboard::button('💳 To‘lovlarim'),
            ]);

        $this->sendMessage($chatId, "📌 Asosiy menyu", $menu);
    }

    // ✅ Mijoz ma'lumotlarini jo‘natish
    private function sendCustomerInfo($chatId)
    {
        $customer = $this->getCustomerByChatId($chatId);
        if (!$customer) return;

        $balanceText = $customer->balance < 0 ? "Qarzdorlik: <b>{$customer->balance} so‘m</b>" : "Balans: <b>{$customer->balance} so‘m</b>";

        $message = "🆔 <b>Hisob ma'lumotlaringiz</b>\n";
        $message .= "👤 Ism: <b>{$customer->name}</b>\n";
        $message .= "📞 Telefon: <b>{$customer->phone}</b>\n";
        $message .= "🏠 Manzil: <b>{$customer->street->neighborhood->city->name}, {$customer->street->name}</b>\n";
        $message .= "💳 Hisob raqami: <b>{$customer->account_number}</b>\n";
        $message .= "{$balanceText}";

        $this->sendMessage($chatId, $message);
    }

    // ✅ Mijozning invoice ma'lumotlarini jo‘natish
    private function sendInvoices($chatId)
    {
        $customer = $this->getCustomerByChatId($chatId);
        if (!$customer) return;

        if ($customer->invoices->isEmpty()) {
            $this->sendMessage($chatId, "📑 Sizda invoyslar mavjud emas.");
            return;
        }

        $message = "📑 <b>Hisob varaqalar</b>\n";
        foreach ($customer->invoices as $invoice) {
            $status = $invoice->status == "paid" ? "✅ To‘langan" : "⏳ To‘lanmagan";
            $message .= "🔹 <b>Invoice #{$invoice->invoice_number}</b>\n";
            $message .= "🗓 Oy: <b>{$invoice->billing_period}</b>\n";
            $message .= "💰 Summa: <b>{$invoice->amount_due} UZS</b>\n";
            $message .= "📌 Status: <b>{$status}</b>\n\n";
        }

        $this->sendMessage($chatId, $message);
    }

    // ✅ Mijozning to‘lov ma'lumotlarini jo‘natish
    private function sendPayments($chatId)
    {
        $customer = $this->getCustomerByChatId($chatId);
        if (!$customer) return;

        if ($customer->payments->isEmpty()) {
            $this->sendMessage($chatId, "💳 Sizda to‘lovlar mavjud emas.");
            return;
        }

        $message = "💳 <b>To‘lovlar tarixi</b>\n";
        foreach ($customer->payments as $payment) {
            $date = $payment->payment_date instanceof \Carbon\Carbon
                ? $payment->payment_date->format('d.m.Y')
                : date('d.m.Y', strtotime($payment->payment_date));
            $message .= "💵 <b>{$payment->amount} UZS</b>\n";
            $message .= "📅 Sana: <b>{$date}</b>\n";
            $message .= "💳 Usul: <b>{$payment->payment_method}</b>\n";
            $message .= "📌 Status: <b>{$payment->status}</b>\n\n";
        }

        $this->sendMessage($chatId, $message);
    }

    // ✅ Telegram Chat ID bo‘yicha mijozni topish
    private function getCustomerByChatId($chatId)
    {
        return Customer::whereHas('telegramAccounts', function ($query) use ($chatId) {
            $query->where('telegram_chat_id', $chatId);
        })->with(['company', 'street.neighborhood.city.region', 'invoices', 'payments'])->first();
    }

    // ✅ Xabar yuborish
    private function sendMessage($chatId, $text, $replyMarkup = null)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => $replyMarkup ? json_encode($replyMarkup) : null
        ]);
    }
}
