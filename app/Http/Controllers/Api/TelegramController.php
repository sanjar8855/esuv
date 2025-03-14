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
        $text = trim($update['message']['text'] ?? '');

        // ✅ Callback tugmalar bosilganda pagination bilan ishlash
        if (isset($update['callback_query'])) {
            $callbackData = explode(':', $update['callback_query']['data']);
            $action = $callbackData[0] ?? null;
            $page = isset($callbackData[1]) ? (int)$callbackData[1] : 1;

            $chatId = $update['callback_query']['message']['chat']['id'] ?? null;

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
                case "meter_history":
                    $this->sendMeterHistory($chatId, $page);
                    break;
                case "settings":
                    $this->sendSettingsMenu($chatId);
                    break;
                case "switch_account":
                    $selectedCustomerId = isset($callbackData[1]) ? (int)$callbackData[1] : null;
                    $this->switchAccount($chatId, $selectedCustomerId);

                    Telegram::editMessageReplyMarkup([
                        'chat_id' => $chatId,
                        'message_id' => $update['callback_query']['message']['message_id'],
                        'reply_markup' => json_encode(['inline_keyboard' => []], JSON_UNESCAPED_UNICODE),
                    ]);
                    break;
                case "add_new_account":
                    $this->sendMessage($chatId, "🔢 Yangi hisob raqamini kiriting:");
                    break;
            }
            return;
        }

        // ✅ **Agar foydalanuvchi /start bossa**
        if (strcasecmp($text, "/start") === 0) {
            // 🔎 **Foydalanuvchining bog‘langan akkaunti bormi?**
            $linkedCustomer = Customer::whereHas('telegramAccounts', function ($query) use ($userId) {
                $query->where('telegram_chat_id', $userId);
            })->first();

            if (!$linkedCustomer) {
                // 🚨 **Agar akkaunt topilmasa, hisob raqamini so‘raymiz va menyuni olib tashlaymiz**
                $this->sendMessage($chatId, "🔢 Iltimos, hisob raqamingizni kiriting:", json_encode(['remove_keyboard' => true]));
                return;
            }

            // ✅ **Agar akkaunt topilsa, asosiy menyuni ko‘rsatamiz**
            $this->sendMainMenu($chatId);
            return;
        }

        // 🔎 **Foydalanuvchi bog‘langan hisobga ega bo‘lmasa, faqat raqam jo‘natishni talab qilish**
        $linkedCustomer = Customer::whereHas('telegramAccounts', function ($query) use ($userId) {
            $query->where('telegram_chat_id', $userId);
        })->first();

        if (!$linkedCustomer) {
            // 🚨 **Faqat raqam kiritish so‘raladi**
            if (!is_numeric($text)) {
                $this->sendMessage($chatId, "❌ Noto‘g‘ri ma’lumot. 🔢 Iltimos, hisob raqamingizni kiriting:");
                return;
            }

            // ✅ **Agar raqam bo‘lsa, bog‘lashni harakat qiladi**
            $customer = Customer::where('account_number', $text)->first();

            if (!$customer) {
                // ❌ **Hisob raqami topilmadi, menyuni ko‘rsatmaslik kerak**
                $this->sendMessage($chatId, "❌ Xatolik: Hisob raqami topilmadi. Qayta urinib ko‘ring.");
                return;
            }

            // ✅ **Agar hisob topilsa, bog‘laymiz**
            $this->linkAccount($chatId, $userId, $text);
            return;
        }

        if ($text === "➕ Hisoblagichga ko‘rsatgich qo‘shish") {
            $customer = $this->getCustomerByChatId($chatId);

            if (!$customer || !$customer->waterMeter) {
                $this->sendMessage($chatId, "❌ Sizda hisoblagich mavjud emas yoki topilmadi.");
                return;
            }

            // 🔄 Foydalanuvchi holatini saqlab qo‘yamiz
            cache()->put("awaiting_meter_reading_{$chatId}", $customer->id, now()->addMinutes(5));

            // 📩 Foydalanuvchidan yangi ko‘rsatgichni kiritishni so‘raymiz
            $this->sendMessage($chatId, "🔢 Hisoblagichga yangi ko‘rsatgichni kiriting:");
            return;
        }

        // ✅ **Asosiy menyudan tugmalar bosilganda**
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
            case "📈 Hisoblagich tarixi":
                $this->sendMeterHistory($chatId);
                break;
            case "⚙️ Sozlamalar":
                $this->sendSettingsMenu($chatId);
                break;
            case "➕ Hisoblagichga ko‘rsatgich qo‘shish": // ✅ Yangi tugma
                $customer = $this->getCustomerByChatId($chatId);

                if (!$customer || !$customer->waterMeter) {
                    $this->sendMessage($chatId, "❌ Sizda hisoblagich mavjud emas yoki topilmadi.");
                    return;
                }

                // 🔄 Foydalanuvchi holatini saqlab qo‘yamiz
                cache()->put("awaiting_meter_reading_{$chatId}", $customer->id, now()->addMinutes(5));

                // 📩 Foydalanuvchidan yangi ko‘rsatgichni kiritishni so‘raymiz
                $this->sendMessage($chatId, "🔢 Hisoblagichga yangi ko‘rsatgichni kiriting:");
                return;
            default:
                $this->sendMessage($chatId, "❌ Noto‘g‘ri buyruq. Iltimos, tugmalardan foydalaning.");
        }

        // 🔎 Agar foydalanuvchi yangi ko‘rsatgich kiritayotgan bo‘lsa
        if (cache()->has("awaiting_meter_reading_{$chatId}")) {
            $customerId = cache()->get("awaiting_meter_reading_{$chatId}");
            $customer = Customer::find($customerId);

            if (!$customer || !$customer->waterMeter) {
                $this->sendMessage($chatId, "❌ Xatolik: Hisoblagich topilmadi.");
                cache()->forget("awaiting_meter_reading_{$chatId}");
                return;
            }

            // 🔢 Faqat raqamlar jo‘natilganligini tekshiramiz
            if (!is_numeric($text) || $text < 0) {
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

            // ✅ Ko‘rsatgichni saqlaymiz (tasdiqlanmagan holatda)
            $customer->waterMeter->readings()->create([
                'reading' => $text,
                'reading_date' => now(),
                'confirmed' => false, // ❌ Yangi qo‘shilgan o‘qish tasdiqlanmagan bo‘ladi
            ]);

            cache()->forget("awaiting_meter_reading_{$chatId}");

            $this->sendMessage($chatId, "✅ Hisoblagich uchun yangi ko‘rsatgich qo‘shildi. Admin tasdiqlaganidan keyin u hisobga olinadi.");
            return;
        }

        //  **Agar yuqoridagi shartlar ishlamasa, shundagina noto‘g‘ri buyruq ekanligini bildiramiz**
        $this->sendMessage($chatId, "❌ Noto‘g‘ri buyruq. Iltimos, tugmalardan foydalaning.");

    }


    // ✅ Hisob raqamini Telegramga bog‘lash
    private function linkAccount($chatId, $userId, $accountNumber)
    {
        $customer = Customer::where('account_number', $accountNumber)->first();

        if (!$customer) {
            $this->sendMessage($chatId, "❌ Xatolik: Hisob raqami topilmadi. Qayta urinib ko‘ring.");
            $this->sendMainMenu($chatId);
            return;
        }

        $username = Telegram::getWebhookUpdate()['message']['from']['username'] ?? null;

        $telegramAccount = TelegramAccount::firstOrCreate(
            ['telegram_chat_id' => $userId],
            ['username' => $username]
        );

        // ✅ Many-to-Many bog‘langanini tekshirish
        if ($customer->telegramAccounts()->wherePivot('telegram_account_id', $telegramAccount->id)->exists()) {
            $this->sendMessage($chatId, "⚠️ Bu hisob raqami allaqachon bog‘langan.");
            $this->sendMainMenu($chatId);
            return;
        }

        // ✅ Yangi bog‘lanishni saqlash
        $customer->telegramAccounts()->attach($telegramAccount->id);

        // ✅ Yangi hisobni avtomatik active qilish
        cache()->put("active_customer_id_{$chatId}", $customer->id, now()->addDays(30));

        $this->sendMessage($chatId, "✅ Yangi hisob bog‘landi! Siz hozir ushbu hisob bilan ishlayapsiz.");

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
            ->toJson();

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

        $replyMarkup = json_encode(['inline_keyboard' => $buttons], JSON_UNESCAPED_UNICODE);

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
            $this->sendMessage($chatId, "✅ Hisob muvaffaqiyatli o‘zgartirildi!\n📌 Yangi hisob: <b>{$customer->name}</b>", []);
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
        $message .= "🏠 Manzil: <b>{$customer->street->neighborhood->city->region->name},{$customer->street->neighborhood->city->name},{$customer->street->neighborhood->name},{$customer->street->name}</b>\n";
        $message .= "💳 Hisob raqami: <b>{$customer->account_number}</b>\n";
        $message .= "👫 Oila a'zolar soni: <b>{$customer->family_members}</b>\n";
        $message .= "🔹 Telegram akkauntlar: <b>";

        foreach ($customer->telegramAccounts as $tg) {
            $message .= "<a href='https://t.me/{$tg->username}'>🆔 {$tg->username}</a>, ";
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

        $perPage = 10;
        $total = $customer->invoices->count();
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $invoices = $customer->invoices->slice($offset, $perPage);

        $message = "📑 <b>Hisob varaqalar</b> (Sahifa: {$page}/{$totalPages})\n";
        foreach ($invoices as $invoice) {
            $message .= "🔹 <b>Invoice #{$invoice->invoice_number}</b>\n 📆 Qaysi oy uchun: <b>{$invoice->billing_period}</b>\n 💰 Summa: <b>{$invoice->amount_due} UZS</b>\n\n";
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
            if ($payment->payment_method == 'cash') {
                $payment_method = 'Naqd pul';
            } elseif ($payment->payment_method == 'card') {
                $payment_method = 'Plastik orqali';
            } elseif ($payment->payment_method == 'transfer') {
                $payment_method = 'Bank orqali';
            } else {
                $payment_method = 'Noaniq';
            }
            $message .= "💳 Hisob raqami: <b>{$payment->customer->account_number}</b>\n";
            $message .= "💵 Miqdori: <b>{$payment->amount} UZS</b>\n";
            $message .= "💰 To'lov turi: <b>{$payment_method}</b>\n";
            $message .= "📅 Sana: <b>{$date}</b>\n\n";
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

        $perPage = 10;
        $readings = $customer->waterMeter->readings->sortByDesc('reading_date')->values();
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
        // 🔄 Foydalanuvchining aktiv hisobini tekshirish
        $activeCustomerId = cache()->get("active_customer_id_{$chatId}");

        if ($activeCustomerId) {
            $customer = Customer::with(['company', 'street.neighborhood.city.region', 'invoices', 'payments'])
                ->find($activeCustomerId);

            // 🔴 Tekshiramiz: Bu mijoz hali Telegram akkaunt bilan bog‘langanmi?
            if (!$customer || !$customer->telegramAccounts()->where('telegram_chat_id', $chatId)->exists()) {
                // ❌ Mijoz endi bog‘langan bo‘lmasa, unga xabar yuboramiz
                $this->sendMessage($chatId, "🚨 Hisobingiz botdan o‘chirildi! 🔢 Yangi hisob raqamini kiritib qayta bog‘lang.");
                cache()->forget("active_customer_id_{$chatId}");
                return null;
            }

            return $customer;
        }

        // ❌ Agar aktiv hisob bo‘lmasa, eski metod orqali olish
        return Customer::whereHas('telegramAccounts', function ($query) use ($chatId) {
            $query->where('telegram_chat_id', $chatId);
        })->with(['company', 'street.neighborhood.city.region', 'invoices', 'payments'])->first();
    }

    // ✅ Xabar yuborish
    private function sendMessage($chatId, $text, $replyMarkup = null)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup) {
            $params['reply_markup'] = $replyMarkup;
        }

        Telegram::sendMessage($params);
    }
}
