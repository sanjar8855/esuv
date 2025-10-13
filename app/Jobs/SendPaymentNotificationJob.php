<?php

namespace App\Jobs;

use App\Models\Payment;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;

    /**
     * Qayta urinishlar soni
     */
    public $tries = 3;

    /**
     * Timeout
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Payment $payment)
    {
        // ✅ Eager loading
        $this->payment = $payment->load('customer.telegramAccounts');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $customer = $this->payment->customer;

        // ✅ Validation
        if (!$customer) {
            Log::warning('Payment customer not found', ['payment_id' => $this->payment->id]);
            return;
        }

        if ($customer->telegramAccounts->isEmpty()) {
            Log::info('No telegram accounts for customer', [
                'customer_id' => $customer->id,
                'payment_id' => $this->payment->id
            ]);
            return;
        }

        // ✅ Match expression (PHP 8+)
        $paymentMethod = match($this->payment->payment_method) {
            'cash' => 'Naqd pul',
            'card' => 'Plastik orqali',
            'transfer' => 'Bank o\'tkazmasi',
            'online' => 'Onlayn to\'lov',
            default => 'Noma\'lum'
        };

        // ✅ Xabar tayyorlash
        $message = "✅ To'lov qabul qilindi!\n\n"
            . "💰 Summa: " . number_format($this->payment->amount, 0, '.', ' ') . " so'm\n"
            . "💳 To'lov turi: {$paymentMethod}\n"
            . "🔢 Hisob raqam: {$customer->account_number}\n"
            . "📅 Sana: " . $this->payment->payment_date->format('d.m.Y H:i');

        // ✅ Har bir telegram account ga yuborish
        foreach ($customer->telegramAccounts as $telegramAccount) {
            try {
                Telegram::sendMessage([
                    'chat_id' => $telegramAccount->telegram_chat_id,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]);

                Log::info('Payment notification sent', [
                    'payment_id' => $this->payment->id,
                    'chat_id' => $telegramAccount->telegram_chat_id
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send payment notification', [
                    'payment_id' => $this->payment->id,
                    'chat_id' => $telegramAccount->telegram_chat_id,
                    'error' => $e->getMessage()
                ]);

                continue; // ✅ Davom etish
            }
        }
    }

    /**
     * ✅ Job fail bo'lganda
     */
    public function failed(\Throwable $exception)
    {
        Log::error('SendPaymentNotificationJob failed', [
            'payment_id' => $this->payment->id,
            'error' => $exception->getMessage()
        ]);
    }
}
