<?php
// app/Observers/PaymentObserver.php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Customer;
use App\Jobs\SendPaymentNotificationJob;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    /**
     * ✅ Yaratilganda
     */
    public function created(Payment $payment): void
    {
        Log::info('Payment created', ['payment_id' => $payment->id]);

        // ✅ Eager loading
        $payment->loadMissing('customer.telegramAccounts');

        // ✅ Notification (faqat tasdiqlangan to'lovlar uchun)
        if ($payment->confirmed && $payment->customer && $payment->customer->telegramAccounts->isNotEmpty()) {
            SendPaymentNotificationJob::dispatch($payment);
        }

        // ✅ Balance yangilash (faqat tasdiqlangan to'lovlar)
        if ($payment->confirmed) {
            $this->updateCustomerBalance($payment);
        }
    }

    /**
     * ✅ Yangilanganda
     */
    public function updated(Payment $payment): void
    {
        Log::info('Payment updated', [
            'payment_id' => $payment->id,
            'changes' => $payment->getChanges()
        ]);

        // ✅ Agar confirmed true ga o'zgardi
        if ($payment->wasChanged('confirmed') && $payment->confirmed) {
            // Balance yangilash
            $this->updateCustomerBalance($payment);

            // Notification yuborish
            $payment->loadMissing('customer.telegramAccounts');
            if ($payment->customer && $payment->customer->telegramAccounts->isNotEmpty()) {
                SendPaymentNotificationJob::dispatch($payment);
            }
        }

        // ✅ Agar amount o'zgarsa va tasdiqlangan bo'lsa
        if ($payment->wasChanged('amount') && $payment->confirmed) {
            $this->updateCustomerBalance($payment);
        }
    }

    /**
     * ✅ O'chirilganda
     */
    public function deleted(Payment $payment): void
    {
        Log::info('Payment deleted', ['payment_id' => $payment->id]);

        // ✅ Faqat tasdiqlangan to'lovlar balansni o'zgartiradi
        if ($payment->confirmed) {
            $this->updateCustomerBalance($payment);
        }
    }

    /**
     * ✅ Helper metod
     */
    protected function updateCustomerBalance(Payment $payment): void
    {
        if (!$payment->customer_id) {
            return;
        }

        $customer = Customer::find($payment->customer_id);

        if (!$customer) {
            Log::warning('Customer not found for payment', [
                'customer_id' => $payment->customer_id,
                'payment_id' => $payment->id
            ]);
            return;
        }

        // ✅ Observer siz
        Customer::withoutEvents(function () use ($customer) {
            $customer->updateBalance();
        });

        Log::info('Customer balance updated', [
            'customer_id' => $customer->id,
            'new_balance' => $customer->balance
        ]);
    }
}
