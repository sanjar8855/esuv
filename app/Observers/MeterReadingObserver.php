<?php

namespace App\Observers;

use App\Models\MeterReading;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Jobs\SendMeterReadingNotificationJob;

class MeterReadingObserver
{
    /**
     * Tasdiqlangan meter reading qo'shilganda ishlaydi.
     */
    public function created(MeterReading $meterReading)
    {
        $this->sendNotificationIfConfirmed($meterReading);
    }

    public function updated(MeterReading $meterReading)
    {
        if ($meterReading->isDirty('confirmed') && $meterReading->confirmed === true) {
            $this->sendNotificationIfConfirmed($meterReading);
        }
    }

    protected function sendNotificationIfConfirmed(MeterReading $meterReading)
    {
        \Log::info('sendNotificationIfConfirmed called: ' . $meterReading->id);

        if ($meterReading->confirmed === true) {
            \Log::info('Confirmed: ' . $meterReading->id);
            $customer = $meterReading->customer;

            $message = "✅ Hisoblagich ko'rsatkich tasdiqlandi!\n";
            $message .= "👤 Mijoz: <b>{$customer->name}</b>\n";
            $message .= "📏 Ko'rsatkich: <b>{$meterReading->reading}</b>\n";
            $message .= "📅 Sana: <b>" . date('d.m.Y', strtotime($meterReading->reading_date)) . "</b>";

            \Log::info('Message: ' . $message);

            foreach ($customer->telegramAccounts as $tgAccount) {
                \Log::info('Sending to: ' . $tgAccount->telegram_chat_id);
                SendMeterReadingNotificationJob::dispatch($tgAccount->telegram_chat_id, $message);
            }
        }
    }
}
