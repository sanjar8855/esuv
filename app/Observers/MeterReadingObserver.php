<?php

namespace App\Observers;

use App\Models\MeterReading;
use App\Models\Invoice;
use App\Models\Tariff;
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

        // ✅ Agar tasdiqlangan bo'lsa, invoice yaratish
        if ($meterReading->confirmed) {
            $this->createInvoiceIfNeeded($meterReading);
        }

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
            $this->createInvoiceIfNeeded($meterReading);
            $this->sendNotificationIfConfirmed($meterReading);
        }
    }

    /**
     * ✅ Invoice yaratish (agar kerak bo'lsa)
     */
    protected function createInvoiceIfNeeded(MeterReading $meterReading): void
    {
        $meterReading->loadMissing('waterMeter.customer');

        if (!$meterReading->waterMeter || !$meterReading->waterMeter->customer) {
            Log::warning('Cannot create invoice - water meter or customer not found', [
                'reading_id' => $meterReading->id
            ]);
            return;
        }

        $customer = $meterReading->waterMeter->customer;

        // Aktiv tarifni topish
        $tariff = Tariff::where('company_id', $customer->company_id)
            ->where('is_active', true)
            ->latest('valid_from')
            ->first();

        if (!$tariff) {
            Log::warning('No active tariff found for customer', [
                'customer_id' => $customer->id,
                'reading_id' => $meterReading->id
            ]);
            return;
        }

        // Oldingi tasdiqlangan ko'rsatkichni topish
        $previousReading = MeterReading::where('water_meter_id', $meterReading->water_meter_id)
            ->where('confirmed', true)
            ->where('id', '<', $meterReading->id)
            ->orderBy('reading_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$previousReading) {
            Log::info('No previous reading found - this is the first reading', [
                'reading_id' => $meterReading->id
            ]);
            return;
        }

        // Iste'molni hisoblash
        $consumption = $meterReading->reading - $previousReading->reading;

        if ($consumption <= 0) {
            Log::warning('Consumption is zero or negative', [
                'reading_id' => $meterReading->id,
                'consumption' => $consumption
            ]);
            return;
        }

        // Invoice yaratish
        $amountDue = $consumption * $tariff->price_per_m3;

        Invoice::create([
            'customer_id' => $customer->id,
            'tariff_id' => $tariff->id,
            'billing_period' => now()->format('Y-m'),
            'amount_due' => $amountDue,
            'due_date' => now()->endOfMonth(),
            'status' => 'pending',
        ]);

        Log::info('Invoice created for meter reading', [
            'reading_id' => $meterReading->id,
            'customer_id' => $customer->id,
            'consumption' => $consumption,
            'amount_due' => $amountDue
        ]);
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
