<?php

namespace App\Jobs;

use App\Models\Invoice;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInvoiceNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice;

    /**
     * Qayta urinishlar soni
     */
    public $tries = 3;

    /**
     * Timeout (soniyalarda)
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice)
    {
        // ✅ Eager loading qilish
        $this->invoice = $invoice->load('customer.telegramAccounts');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $customer = $this->invoice->customer;

        // ✅ Agar customer yo'q bo'lsa
        if (!$customer) {
            Log::warning('Invoice customer not found', ['invoice_id' => $this->invoice->id]);
            return;
        }

        // ✅ Agar telegram accounts yo'q bo'lsa
        if ($customer->telegramAccounts->isEmpty()) {
            Log::info('No telegram accounts for customer', [
                'customer_id' => $customer->id,
                'invoice_id' => $this->invoice->id
            ]);
            return;
        }

        // ✅ Xabar tayyorlash
        $statusText = match($this->invoice->status) {
            'paid' => '✅ To\'langan',
            'pending' => '⏳ To\'lanmagan',
            'overdue' => '🔴 Muddati o\'tgan',
            default => '❓ Noma\'lum'
        };

        $message = "📑 Yangi hisob varaqasi yaratildi!\n\n"
            . "🔢 Hisob raqam: {$customer->account_number}\n"
            . "📅 Oy: {$this->invoice->billing_period}\n"
            . "💰 Summa: " . number_format($this->invoice->amount_due, 0, '.', ' ') . " so'm\n"
            . "📌 Status: {$statusText}\n"
            . "📆 Muddati: {$this->invoice->due_date->format('d.m.Y')}";

        // ✅ Har bir telegram account ga yuborish
        foreach ($customer->telegramAccounts as $telegramAccount) {
            try {
                Telegram::sendMessage([
                    'chat_id' => $telegramAccount->telegram_chat_id,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]);

                Log::info('Invoice notification sent', [
                    'invoice_id' => $this->invoice->id,
                    'chat_id' => $telegramAccount->telegram_chat_id
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send invoice notification', [
                    'invoice_id' => $this->invoice->id,
                    'chat_id' => $telegramAccount->telegram_chat_id,
                    'error' => $e->getMessage()
                ]);

                // ✅ Davom etish (boshqa accountlarga yuborish)
                continue;
            }
        }
    }

    /**
     * ✅ Job fail bo'lganda
     */
    public function failed(\Throwable $exception)
    {
        Log::error('SendInvoiceNotificationJob failed', [
            'invoice_id' => $this->invoice->id,
            'error' => $exception->getMessage()
        ]);
    }
}
