<?php

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

        // ✅ Notification yuborish
        if ($payment->customer && $payment->customer->telegramAccounts->isNotEmpty()) {
            SendPaymentNotificationJob::dispatch($payment);
        }

        // ✅ Balance yangilash
        $this->updateCustomerBalance($payment);
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

        // ✅ Faqat amount yoki status o'zgarganda balance yangilash
        if ($payment->wasChanged(['amount', 'status'])) {
            $this->updateCustomerBalance($payment);
        }

        // ✅ Status o'zgarganda notification (ixtiyoriy)
        if ($payment->wasChanged('status')) {
            $payment->loadMissing('customer.telegramAccounts');

            if ($payment->customer && $payment->customer->telegramAccounts->isNotEmpty()) {
                // TODO: Status o'zgarishi haqida alohida notification
                // SendPaymentStatusChangedJob::dispatch($payment);
            }
        }
    }

    /**
     * ✅ O'chirilganda
     */
    public function deleted(Payment $payment): void
    {
        Log::info('Payment deleted', ['payment_id' => $payment->id]);

        // ✅ Balance yangilash
        $this->updateCustomerBalance($payment);
    }

    /**
     * ✅ Helper metod - cheksiz loop oldini olish
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

        // ✅ Observer ni vaqtincha o'chirish (cheksiz loop oldini olish)
        Customer::withoutEvents(function () use ($customer) {
            $customer->updateBalance();
        });

        Log::info('Balance updated for customer', [
            'customer_id' => $customer->id,
            'new_balance' => $customer->balance
        ]);
    }
}
