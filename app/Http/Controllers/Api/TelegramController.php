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

        // ✅ Callback tugmalar bosilganda pagination bilan ishlash
        if (isset($update['callback_query'])) {
            $callbackData = explode(':', $update['callback_query']['data']);
            $action = $callbackData[0] ?? null;
            $page = isset($callbackData[1]) ? (int) $callbackData[1] : 1;

            $chatId = $update['callback_query']['message']['chat']['id'];

            switch ($action) {
                case "info":
                    $this->sendCustomerInfo($chatId);
                    break;
                case "invoices":
                    $this->sendInvoices($chatId, $page);
                    break;
                case "payments":
                    $this->sendPayments($chatId, $page);
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

        $username = null;
        if (isset(Telegram::getWebhookUpdate()['message']['from']['username'])) {
            $username = Telegram::getWebhookUpdate()['message']['from']['username'];
        }

        $exists = $customer->telegramAccounts()->where('telegram_chat_id', $userId)->exists();

        if (!$exists) {
            $customer->telegramAccounts()->create([
                'telegram_chat_id' => $userId,
                'username' => $username
            ]);
            $this->sendMessage($chatId, "✅ Hisobingiz bog‘landi! Quyidagi menyudan foydalaning.");
        } else {
            $this->sendMessage($chatId, "✅ Siz allaqachon ushbu hisobga bog‘langansiz.");
        }

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

    // ✅ Hisob varaqalar uchun pagination
    private function sendInvoices($chatId, $page = 1)
    {
        $customer = $this->getCustomerByChatId($chatId);
        if (!$customer) return;

        $perPage = 10;
        $total = $customer->invoices->count();
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $invoices = $customer->invoices->slice($offset, $perPage);

        $message = "📑 <b>Hisob varaqalar</b> (Sahifa: {$page}/{$totalPages})\n";
        foreach ($invoices as $invoice) {
            $message .= "🔹 <b>Invoice #{$invoice->invoice_number}</b>\n💰 Summa: <b>{$invoice->amount_due} UZS</b>\n\n";
        }

        $this->sendPaginatedMessage($chatId, $message, 'invoices', $page, $totalPages);
    }

    // ✅ To‘lovlar tarixi uchun pagination
    private function sendPayments($chatId, $page = 1)
    {
        $customer = $this->getCustomerByChatId($chatId);
        if (!$customer) return;

        $perPage = 10;
        $total = $customer->payments->count();
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $payments = $customer->payments->slice($offset, $perPage);

        $message = "💳 <b>To‘lovlar tarixi</b> (Sahifa: {$page}/{$totalPages})\n";
        foreach ($payments as $payment) {
            $date = date('d.m.Y', strtotime($payment->payment_date));
            $message .= "💵 <b>{$payment->amount} UZS</b>\n📅 Sana: <b>{$date}</b>\n\n";
        }

        $this->sendPaginatedMessage($chatId, $message, 'payments', $page, $totalPages);
    }

    // ✅ Pagination tugmalarini qo‘shib yuborish
    private function sendPaginatedMessage($chatId, $message, $type, $page, $totalPages)
    {
        $buttons = [];
        if ($page > 1) {
            $buttons[] = ['text' => '⏮️ Oldingi', 'callback_data' => "{$type}:" . ($page - 1)];
        }
        if ($page < $totalPages) {
            $buttons[] = ['text' => '⏭️ Keyingi', 'callback_data' => "{$type}:" . ($page + 1)];
        }

        $replyMarkup = ['inline_keyboard' => [$buttons]];
        $this->sendMessage($chatId, $message, $replyMarkup);
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
