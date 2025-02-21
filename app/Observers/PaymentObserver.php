<?php
namespace App\Observers;

use App\Models\Payment;
use App\Models\CustomerTelegramAccount;
use Telegram\Bot\Laravel\Facades\Telegram;

class PaymentObserver
{
    public function created(Payment $payment)
    {
        $customer = $payment->customer;

        foreach ($customer->telegramAccounts as $telegramAccount) {
            Telegram::sendMessage([
                'chat_id' => $telegramAccount->telegram_chat_id,
                'text' => "💰 To‘lov qabul qilindi! To‘langan summa: " . $payment->amount
            ]);
        }
    }
}
