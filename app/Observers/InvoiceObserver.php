<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Jobs\SendInvoiceNotificationJob;

class InvoiceObserver
{
    public function created(Invoice $invoice)
    {
        SendInvoiceNotificationJob::dispatch($invoice);
    }
}
