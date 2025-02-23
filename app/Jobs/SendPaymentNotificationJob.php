<?php

namespace App\Jobs;

use App\Models\Payment;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;

    /**
     * Create a new job instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $customer = $this->payment->customer;

        if ($this->payment->payment_method == 'cash') {
            $payment_method = 'Naqd pul';
        } elseif ($this->payment->payment_method == 'card') {
            $payment_method = 'Plastik orqali';
        } elseif ($this->payment->payment_method == 'transfer') {
            $payment_method = 'Bank orqali';
        } else {
            $payment_method = 'Noaniq';
        }

        foreach ($customer->telegramAccounts as $telegramAccount) {
            Telegram::sendMessage([
                'chat_id' => $telegramAccount->telegram_chat_id,
                'text' => "💰 To‘lov qabul qilindi! To‘langan summa: " . $this->payment->amount . ". To‘lov turi: " . $payment_method . ". Hisob raqam: " . $this->payment->customer->account_number
            ]);
        }
    }
}
