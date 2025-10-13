<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\TelegramAccount;
use Illuminate\Http\Request;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $update = Telegram::getWebhookUpdate();

        $chatId = $update['message']['chat']['id'] ?? ($update['callback_query']['message']['chat']['id'] ?? null);
        $userId = $update['message']['from']['id'] ?? null;

        if (!$chatId) {
            \Log::warning('Webhook received without chat_id', ['update' => $update]);
            return response()->json(['status' => 'error', 'message' => 'No chat_id'], 400);
        }

        if (!$userId) {
            \Log::warning('Webhook received without user_id', ['chat_id' => $chatId]);
            return response()->json(['status' => 'error', 'message' => 'No user_id'], 400);
        }

        $text = trim($update['message']['text'] ?? '');

        if ($text === '/start') {
            $this->handleStart($chatId, $userId);
            return response()->json(['status' => 'ok']);
        }

        // ✅ Callback tugmalar bosilganda pagination bilan ishlash
        if (isset($update['callback_query'])) {
            if (is_string($update['callback_query']['data'])) {
                $callbackData = explode(':', $update['callback_query']['data']);
            } else {
                $callbackData = [];
            }
            $action = $callbackData[0] ?? null;
            $page = isset($callbackData[1]) ? (int)$callbackData[1] : 1;

            switch ($action) {
                case "info":
                    $this->sendCustomerInfo($chatId);
                    return;
                case "invoices":
                    $this->sendInvoices($chatId, $page);
                    return;
                case "payments":
                    $this->sendPayments($chatId, $page);
                    return;
                case "meter_history":
                    $this->sendMeterHistory($chatId, $page);
                    return;
                case "settings":
                    $this->sendSettingsMenu($chatId);
                    return;
                case "switch_account":
                    $customerId = $callbackData[1] ?? null;
                    $this->switchAccount($chatId, $customerId);
                    return;
                case "add_new_account":
                    $this->sendMessage($chatId, "🔢 Yangi hisob raqamini kiriting:");
                    cache()->put("awaiting_account_link_{$chatId}", true, now()->addMinutes(5));
                    return;
            }
        }

        // ✅ Foydalanuvchi hisob raqamini bog‘laganligini tekshiramiz
        $linkedCustomer = Customer::whereHas('telegramAccounts', function ($query) use ($userId) {
            $query->where('telegram_chat_id', $userId);
        })->first();

        if (!$linkedCustomer) {
            if (!is_numeric($text)) {
                $this->sendMessage($chatId, "❌ Noto‘g‘ri ma’lumot. 🔢 Iltimos, hisob raqamingizni kiriting:");
                return;
            }

            $customer = Customer::where('account_number', $text)->first();
            if (!$customer) {
                $this->sendMessage($chatId, "❌ Xatolik: Hisob raqami topilmadi. Qayta urinib ko‘ring.");
                return;
            }

            $this->linkAccount($chatId, $userId, $text);
            return;
        }

        // ✅ Agar foydalanuvchi ko‘rsatgich kiritayotgan bo‘lsa
        if (cache()->has("awaiting_meter_reading_{$chatId}")) {
            $this->processMeterReading($chatId, $text);
            return;
        }

        // ✅ Asosiy menyudan tugmalar bosilganda
        $knownCommands = [
            "📋 Ma'lumotlarim" => "sendCustomerInfo",
            "📑 Hisob varaqalar" => "sendInvoices",
            "💳 To‘lovlarim" => "sendPayments",
            "📈 Hisoblagich tarixi" => "sendMeterHistory",
            "⚙️ Sozlamalar" => "sendSettingsMenu",
        ];

        if (array_key_exists($text, $knownCommands)) {
            $method = $knownCommands[$text];
            $this->$method($chatId);
            return;
        }

        if ($text === "➕ Hisoblagichga ko‘rsatgich qo‘shish") {
            $customer = $this->getCustomerByChatId($chatId);
            if (!$customer || !$customer->waterMeter) {
                $this->sendMessage($chatId, "❌ Sizda hisoblagich mavjud emas yoki topilmadi.");
                return;
            }

            cache()->put("awaiting_meter_reading_{$chatId}", $customer->id, now()->addMinutes(5));
            $this->sendMessage($chatId, "🔢 Hisoblagichga yangi ko‘rsatgichni kiriting:");
            return;
        }

        // ✅ Faqat haqiqatan ham noto‘g‘ri buyruq bo‘lsa, xabar chiqarish
        $this->sendMessage($chatId, "❌ Noto‘g‘ri buyruq. Iltimos, menyudagi tugmalardan foydalaning.");

        return response()->json(['status' => 'ok']);
    }

    private function handleStart($chatId, $userId)
    {
        // Mijoz allaqachon bog'langanmi?
        $linkedCustomer = Customer::whereHas('telegramAccounts', function ($query) use ($userId) {
            $query->where('telegram_chat_id', $userId);
        })->first();

        if ($linkedCustomer) {
            // ✅ Agar bog'langan bo'lsa, asosiy menyuni ko'rsatish
            $this->sendMessage($chatId, "👋 Xush kelibsiz!\n\n📌 Quyidagi tugmalardan foydalaning:");
            $this->sendMainMenu($chatId);
        } else {
            // ✅ Agar bog'lanmagan bo'lsa, hisob raqam so'rash
            $this->sendMessage(
                $chatId,
                "👋 <b>Salom! Suv ta'minoti botiga xush kelibsiz!</b>\n\n"
                . "🔢 Botdan foydalanish uchun hisob raqamingizni kiriting:\n\n"
                . "Masalan: <code>1234567</code>"
            );
        }
    }

    // ✅ Hisob raqamini Telegramga bog‘lash
    private function linkAccount($chatId, $userId, $accountNumber)
    {
        $customer = Customer::where('account_number', $accountNumber)->first();

        if (!$customer) {
            $this->sendMessage($chatId, "❌ Xatolik: Hisob raqami topilmadi. Qayta urinib ko'ring.");
            return;
        }

        // ✅ Username va first_name olish
        $from = Telegram::getWebhookUpdate()['message']['from'] ?? [];
        $username = $from['username'] ?? null;
        $firstName = $from['first_name'] ?? 'User';
        $lastName = $from['last_name'] ?? '';

        // ✅ Agar username yo'q bo'lsa, chat_id ishlatish
        $displayName = $username ?? "user_{$userId}";

        $telegramAccount = TelegramAccount::firstOrCreate(
            ['telegram_chat_id' => $userId],
            [
                'username' => $displayName,  // ✅ Fallback
                'first_name' => $firstName,
                'last_name' => $lastName
            ]
        );

        // ✅ Bog'langanligini tekshirish
        if ($customer->telegramAccounts()->where('telegram_account_id', $telegramAccount->id)->exists()) {
            $this->sendMessage($chatId, "⚠️ Bu hisob raqami allaqachon bog'langan.");
            $this->sendMainMenu($chatId);
            return;
        }

        // ✅ Bog'lash
        $customer->telegramAccounts()->attach($telegramAccount->id);
        cache()->put("active_customer_id_{$chatId}", $customer->id, now()->addDays(30));

        $this->sendMessage($chatId, "✅ Hisob muvaffaqiyatli bog'landi!\n👤 Hisob: <b>{$customer->name}</b>");
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
            ])
            ->row([
                Keyboard::button('📈 Hisoblagich tarixi'),
                Keyboard::button('⚙️ Sozlamalar'),
            ])
            ->row([
                Keyboard::button('➕ Hisoblagichga ko‘rsatgich qo‘shish') // ✅ Yangi tugma
            ])
            ->toArray();

        $this->sendMessage($chatId, "📌 Asosiy menyu", $menu);
    }

    private function sendSettingsMenu($chatId)
    {
        $customerAccounts = Customer::whereHas('telegramAccounts', function ($query) use ($chatId) {
            $query->where('telegram_chat_id', $chatId);
        })->get();

        if ($customerAccounts->isEmpty()) {
            $this->sendMessage($chatId, "❌ Siz hech qanday hisob bog‘lamagansiz.");
            return;
        }

        $activeCustomerId = cache()->get("active_customer_id_{$chatId}");
        $buttons = [];
        foreach ($customerAccounts as $customer) {
            $isActive = $customer->id == $activeCustomerId ? '✅ ' : '🔹';
            $buttons[] = [[
                'text' => "{$isActive}{$customer->name}",
                'callback_data' => "switch_account:{$customer->id}"
            ]];
        }

        // Yangi hisob qo'shish tugmasini qo'shish
        $buttons[] = [['text' => "➕ Yangi hisob qo‘shish", 'callback_data' => "add_new_account"]];

        $replyMarkup = ['inline_keyboard' => $buttons];

        $this->sendMessage($chatId, "⚙️ Sozlamalar: Qaysi hisobni ishlatmoqchisiz?", $replyMarkup);
    }

    private function switchAccount($chatId, $selectedCustomerId)
    {
        if (!$selectedCustomerId) {
            $this->sendMessage($chatId, "❌ Xatolik: Hisobni almashtirish uchun mijozni tanlang.");
            return;
        }

        // 🔄 Laravel Cache orqali saqlash
        cache()->put("active_customer_id_{$chatId}", $selectedCustomerId, now()->addDays(30));

        $customer = Customer::find($selectedCustomerId);
        if ($customer) {
            $this->sendMessage($chatId, "✅ Hisob muvaffaqiyatli o‘zgartirildi!\n📌 Yangi hisob: <b>{$customer->name}</b>", null);
        } else {
            $this->sendMessage($chatId, "❌ Xatolik: Tanlangan mijoz topilmadi.");
        }
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
        $message .= "🏠 Manzil: <b>{$customer->street->neighborhood->city->region->name}, {$customer->street->neighborhood->city->name}, {$customer->street->neighborhood->name}, {$customer->street->name}</b>\n";
        $message .= "💳 Hisob raqami: <b>{$customer->account_number}</b>\n";
        $message .= "👫 Oila a'zolar soni: <b>{$customer->family_members}</b>\n";
        $message .= "🔹 Telegram akkauntlar: <b>";

        foreach ($customer->telegramAccounts as $tg) {
            if (is_array($tg->username)) {
                $message .= "<a href='https://t.me/" . implode(', ', $tg->username) . "'>🆔 " . implode(', ', $tg->username) . "</a>, ";
            } else {
                $message .= "<a href='https://t.me/{$tg->username}'>🆔 {$tg->username}</a>, ";
            }
        }
        $message .= "</b> \n";

        $message .= "{$balanceText}";

        $this->sendMessage($chatId, $message);
    }

    // ✅ Hisob varaqalar uchun pagination
    private function sendInvoices($chatId, $page = 1)
    {
        $customer = $this->getCustomerByChatId($chatId);
        if (!$customer) return;

        $perPage = 6;
        $maxPages = 50;

        // ✅ Eager loaded collection dan foydalanish
        $allInvoices = $customer->invoices->sortByDesc('created_at')->take($maxPages * $perPage);
        $total = $allInvoices->count();
        $totalPages = max(1, ceil($total / $perPage));

        if ($page < 1) $page = 1;
        if ($page > $totalPages) $page = $totalPages;

        $offset = ($page - 1) * $perPage;
        $invoices = $allInvoices->slice($offset, $perPage);

        if ($invoices->isEmpty()) {
            $this->sendMessage($chatId, "📑 Sizda hozircha hisob varaq mavjud emas.");
            return;
        }

        $message = "📑 <b>Hisob varaqalar</b> (Sahifa: {$page}/{$totalPages})\n\n";

        foreach ($invoices as $invoice) {
            $statusIcon = match($invoice->status) {
                'paid' => '✅',
                'pending' => '⏳',
                'overdue' => '🔴',
                default => '❓'
            };

            $message .= "🔹 Invoice #{$invoice->invoice_number}\n";
            $message .= "📆 Oy: <b>{$invoice->billing_period}</b>\n";
            $message .= "💰 Summa: <b>" . number_format($invoice->amount_due, 0, '.', ' ') . " UZS</b>\n";
            $message .= "📌 Holat: {$statusIcon} <b>" . ($invoice->status == 'paid' ? 'To\'langan' : 'To\'lanmagan') . "</b>\n\n";
        }

        $this->sendPaginatedMessage($chatId, $message, 'invoices', $page, $totalPages);
    }

    private function getPaymentMethodName($method)
    {
        return match($method) {
            'cash' => 'Naqd pul 💵',
            'card' => 'Plastik karta 💳',
            'transfer' => 'Bank o\'tkazmasi 🏦',
            'online' => 'Onlayn to\'lov 🌐',
            default => 'Noma\'lum'
        };
    }

    // ✅ To‘lovlar tarixi uchun pagination
    private function sendPayments($chatId, $page = 1)
    {
        $customer = $this->getCustomerByChatId($chatId);
        if (!$customer) return;

        $perPage = 6;
        $allPayments = $customer->payments->sortByDesc('payment_date');
        $total = $allPayments->count();
        $totalPages = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;
        $payments = $allPayments->slice($offset, $perPage);

        if ($payments->isEmpty()) {
            $this->sendMessage($chatId, "💳 Sizda hozircha to'lov tarixi mavjud emas.");
            return;
        }

        $message = "💳 <b>To'lovlar tarixi</b> (Sahifa: {$page}/{$totalPages})\n\n";

        foreach ($payments as $payment) {
            // ✅ Match expression
            $paymentMethod = $this->getPaymentMethodName($payment->payment_method);

            $message .= "💵 Summa: <b>" . number_format($payment->amount, 0, '.', ' ') . " UZS</b>\n";
            $message .= "💳 Usul: <b>{$paymentMethod}</b>\n";
            $message .= "📅 Sana: <b>" . $payment->payment_date->format('d.m.Y') . "</b>\n\n";
        }

        $this->sendPaginatedMessage($chatId, $message, 'payments', $page, $totalPages);
    }

    private function sendMeterHistory($chatId, $page = 1)
    {
        $customer = $this->getCustomerByChatId($chatId);

        if (!$customer || !$customer->waterMeter) {
            $this->sendMessage($chatId, "❌ Sizda hisoblagich mavjud emas yoki topilmadi.");
            return;
        }

        $perPage = 6;
        $readings = $customer->waterMeter->readings->sortByDesc('id')->sortByDesc('reading_date')->values();
        $total = $readings->count();
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $paginatedReadings = $readings->slice($offset, $perPage);

        $message = "📈 <b>Hisoblagich tarixi</b> (Sahifa: {$page}/{$totalPages})\n";
        foreach ($paginatedReadings as $reading) {
            $date = date('d.m.Y', strtotime($reading->reading_date));
            $message .= "📅 Sana: <b>{$date}</b>\n📏 Ko‘rsatkich: <b>{$reading->reading}</b>\n Holat: <b>" . ($reading->confirmed ? '✅ Tasdiqlangan ' : '❌ Tasdiqlanmagan') . "</b> \n\n";
        }

        $this->sendPaginatedMessage($chatId, $message, 'meter_history', $page, $totalPages);
    }

    // ✅ Pagination tugmalarini qo‘shib yuborish
    private function sendPaginatedMessage($chatId, $message, $type, $page, $totalPages)
    {
        $buttons = [];

        if ($page > 1) {
            $buttons[] = [['text' => '⏮️ Oldingi', 'callback_data' => "{$type}:" . ($page - 1)]];
        }
        if ($page < $totalPages) {
            $buttons[] = [['text' => '⏭️ Keyingi', 'callback_data' => "{$type}:" . ($page + 1)]];
        }

        $replyMarkup = count($buttons) > 0 ? ['inline_keyboard' => $buttons] : null;

        $this->sendMessage($chatId, $message, $replyMarkup);
    }

    // ✅ Telegram Chat ID bo‘yicha mijozni topish
    private function getCustomerByChatId($chatId)
    {
        $activeCustomerId = cache()->get("active_customer_id_{$chatId}");

        if ($activeCustomerId) {
            // ✅ telegramAccounts ni ham yuklash
            $customer = Customer::with([
                'company',
                'street.neighborhood.city.region',
                'invoices' => function($query) {
                    $query->latest()->limit(50); // ✅ Faqat oxirgi 50 ta
                },
                'payments' => function($query) {
                    $query->latest()->limit(50);
                },
                'waterMeter.readings' => function($query) {
                    $query->latest()->limit(50);
                },
                'telegramAccounts' // ✅ Qo'shildi
            ])->find($activeCustomerId);

            // ✅ Endi qo'shimcha query yo'q
            if (!$customer || !$customer->telegramAccounts->where('telegram_chat_id', $chatId)->isNotEmpty()) {
                $this->sendMessage($chatId, "🚨 Hisobingiz botdan o'chirildi! 🔢 Yangi hisob raqamini kiritib qayta bog'lang.");
                cache()->forget("active_customer_id_{$chatId}");
                return null;
            }

            return $customer;
        }

        // ✅ Agar aktiv hisob bo'lmasa
        return Customer::whereHas('telegramAccounts', function ($query) use ($chatId) {
            $query->where('telegram_chat_id', $chatId);
        })->with([
            'company',
            'street.neighborhood.city.region',
            'invoices' => function($query) {
                $query->latest()->limit(50);
            },
            'payments' => function($query) {
                $query->latest()->limit(50);
            },
            'waterMeter.readings' => function($query) {
                $query->latest()->limit(50);
            },
            'telegramAccounts'
        ])->first();
    }

    // ✅ Xabar yuborish
    private function sendMessage($chatId, $text, $replyMarkup = null)
    {
        try {
            $params = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ];

            if ($replyMarkup) {
                $params['reply_markup'] = json_encode($replyMarkup);
            }

            Telegram::sendMessage($params);

        } catch (\Telegram\Bot\Exceptions\TelegramSDKException $e) {
            \Log::error('Telegram API error', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);

            // ✅ User bot ni bloklagan bo'lsa
            if (str_contains($e->getMessage(), 'blocked by the user')) {
                \Log::warning('Bot blocked by user', ['chat_id' => $chatId]);
                // TODO: TelegramAccount ni deactivate qilish
            }

        } catch (\Exception $e) {
            \Log::error('Failed to send telegram message', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function processMeterReading($chatId, $text)
    {
        $customerId = cache()->get("awaiting_meter_reading_{$chatId}");
        $customer = Customer::find($customerId);

        if (!$customer || !$customer->waterMeter) {
            $this->sendMessage($chatId, "❌ Xatolik: Hisoblagich topilmadi.");
            cache()->forget("awaiting_meter_reading_{$chatId}");
            return;
        }

        // 🔢 Faqat raqamlar jo‘natilganligini tekshirish
        if (!preg_match('/^\d+$/', $text)) {
            $this->sendMessage($chatId, "❌ Noto‘g‘ri ma'lumot. 🔢 Iltimos, faqat son kiriting:");
            return;
        }

        // 🔄 So‘nggi tasdiqlangan ko‘rsatgichni olamiz
        $lastReading = $customer->waterMeter->readings()
            ->where('confirmed', true)
            ->orderBy('reading_date', 'desc')
            ->first();

        if ($lastReading && $text <= $lastReading->reading) {
            $this->sendMessage($chatId, "❌ Xatolik: Yangi ko‘rsatgich ({$text}) oxirgi tasdiqlangan ({$lastReading->reading}) dan katta bo‘lishi kerak.");
            return;
        }

        try {
            // ✅ Ko‘rsatgichni saqlash
            $customer->waterMeter->readings()->create([
                'reading' => $text,
                'reading_date' => now(),
                'confirmed' => false, // Yangi qo‘shilgan o‘qish tasdiqlanmagan bo‘ladi
            ]);
        } catch (\Exception $e) {
            \Log::error("Meter reading error: ".$e->getMessage());
            $this->sendMessage($chatId, "❌ Xatolik yuz berdi. Iltimos keyinroq urunib ko'ring.");
            return;
        }

        cache()->forget("awaiting_meter_reading_{$chatId}");

        $this->sendMessage($chatId, "✅ Hisoblagich uchun yangi ko‘rsatgich qo‘shildi. Admin tasdiqlaganidan keyin hisobga olinadi.");
    }

}
