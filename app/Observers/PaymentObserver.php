<?php

namespace App\Observers;

use App\Models\Payment;
use App\Jobs\SendPaymentNotificationJob;

class PaymentObserver
{
    public function created(Payment $payment)
    {
        SendPaymentNotificationJob::dispatch($payment);
    }
}
