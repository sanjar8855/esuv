<?php

namespace App\Observers;

use App\Models\MeterReading;
use App\Jobs\SendMeterReadingNotificationJob;
use Illuminate\Support\Facades\Log;

class MeterReadingObserver
{
    /**
     * ✅ Yaratilganda
     */
    public function created(MeterReading $meterReading): void
    {
        Log::info('Meter reading created', ['reading_id' => $meterReading->id]);

        $this->sendNotificationIfConfirmed($meterReading);
    }

    /**
     * ✅ Yangilanganda
     */
    public function updated(MeterReading $meterReading): void
    {
        Log::info('Meter reading updated', [
            'reading_id' => $meterReading->id,
            'changes' => $meterReading->getChanges()
        ]);

        // ✅ Faqat confirmed true ga o'zgarganda
        if ($meterReading->wasChanged('confirmed') && $meterReading->confirmed === true) {
            $this->sendNotificationIfConfirmed($meterReading);
        }
    }

    /**
     * ✅ Notification yuborish
     */
    protected function sendNotificationIfConfirmed(MeterReading $meterReading): void
    {
        // ✅ Faqat tasdiqlangan bo'lsa
        if ($meterReading->confirmed !== true) {
            return;
        }

        // ✅ Eager loading
        $meterReading->loadMissing('waterMeter.customer.telegramAccounts');

        // ✅ Validation
        if (!$meterReading->waterMeter) {
            Log::warning('Water meter not found for reading', [
                'reading_id' => $meterReading->id
            ]);
            return;
        }

        $customer = $meterReading->waterMeter->customer;

        if (!$customer) {
            Log::warning('Customer not found for reading', [
                'reading_id' => $meterReading->id
            ]);
            return;
        }

        if ($customer->telegramAccounts->isEmpty()) {
            Log::info('No telegram accounts for customer', [
                'customer_id' => $customer->id
            ]);
            return;
        }

        // ✅ Xabar tayyorlash
        $message = "✅ Hisoblagich ko'rsatkich tasdiqlandi!\n\n"
            . "👤 Mijoz: <b>{$customer->name}</b>\n"
            . "🔢 Hisob raqam: <b>{$customer->account_number}</b>\n"
            . "📏 Ko'rsatkich: <b>{$meterReading->reading} m³</b>\n"
            . "📅 Sana: <b>" . $meterReading->reading_date->format('d.m.Y') . "</b>";

        // ✅ Har bir telegram account ga yuborish
        foreach ($customer->telegramAccounts as $tgAccount) {
            Log::info('Dispatching meter reading notification', [
                'chat_id' => $tgAccount->telegram_chat_id,
                'reading_id' => $meterReading->id
            ]);

            SendMeterReadingNotificationJob::dispatch(
                $tgAccount->telegram_chat_id,
                $message
            );
        }
    }
}
