<?php

namespace App\Jobs;

use App\Models\Invoice;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInvoiceNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice;

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $customer = $this->invoice->customer;

        foreach ($customer->telegramAccounts as $telegramAccount) {
            Telegram::sendMessage([
                'chat_id' => $telegramAccount->telegram_chat_id,
                'text' => "📑 Yangi hisob varaqasi yaratildi! \n\n📅 Oy: " . $this->invoice->billing_period .
                    "\n💰 Summa: " . number_format($this->invoice->amount_due, 2) . " so‘m" .
                    "\n📌 Status: " . ($this->invoice->status == 'paid' ? "✅ To‘langan" : "⏳ To‘lanmagan")
            ]);
        }
    }
}
