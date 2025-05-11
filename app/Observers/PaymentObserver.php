<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Customer;
use App\Jobs\SendPaymentNotificationJob;

class PaymentObserver
{
    public function created(Payment $payment)
    {
        SendPaymentNotificationJob::dispatch($payment);
    }

    /**
     * Handle the Payment "saved" event (yaratilganda yoki yangilanganda ishlaydi).
     */
    public function saved(Payment $payment): void
    {
        // Mijoz balansini yangilash
        if ($payment->customer) {
            $payment->customer->updateBalance();
            \Log::info("Balance updated for customer ID {$payment->customer_id} due to payment ID {$payment->id} saved.");
        }

        // Xabarnoma yuborish (faqat yangi yaratilganda yuborish uchun)
        if ($payment->wasRecentlyCreated) {
            SendPaymentNotificationJob::dispatch($payment);
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        // Mijoz balansini yangilash
        if ($payment->customer_id) {
            $customer = Customer::find($payment->customer_id);
            if ($customer) {
                $customer->updateBalance();
                \Log::info("Balance updated for customer ID {$payment->customer_id} due to payment ID {$payment->id} deleted.");
            }
        }
    }
}
