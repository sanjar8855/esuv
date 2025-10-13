<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Jobs\SendInvoiceNotificationJob;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    /**
     * ✅ Faqat yaratilganda
     */
    public function created(Invoice $invoice): void
    {
        Log::info('Invoice created', ['invoice_id' => $invoice->id]);

        // ✅ Eager load qilish
        $invoice->loadMissing('customer.telegramAccounts');

        // ✅ Notification yuborish
        if ($invoice->customer && $invoice->customer->telegramAccounts->isNotEmpty()) {
            SendInvoiceNotificationJob::dispatch($invoice);
        }

        // ✅ Balance yangilash
        $this->updateCustomerBalance($invoice);
    }

    /**
     * ✅ Yangilanganda
     */
    public function updated(Invoice $invoice): void
    {
        Log::info('Invoice updated', [
            'invoice_id' => $invoice->id,
            'changes' => $invoice->getChanges()
        ]);

        // ✅ Faqat amount_due yoki status o'zgarganda balance yangilash
        if ($invoice->wasChanged(['amount_due', 'status'])) {
            $this->updateCustomerBalance($invoice);
        }

        // ✅ Status o'zgarganda notification (ixtiyoriy)
        if ($invoice->wasChanged('status')) {
            $invoice->loadMissing('customer.telegramAccounts');

            if ($invoice->customer && $invoice->customer->telegramAccounts->isNotEmpty()) {
                // TODO: Status o'zgarishi haqida alohida notification
                // SendInvoiceStatusChangedJob::dispatch($invoice);
            }
        }
    }

    /**
     * ✅ O'chirilganda
     */
    public function deleted(Invoice $invoice): void
    {
        Log::info('Invoice deleted', ['invoice_id' => $invoice->id]);

        // ✅ Balance yangilash
        $this->updateCustomerBalance($invoice);
    }

    /**
     * ✅ Helper metod - cheksiz loop oldini olish
     */
    protected function updateCustomerBalance(Invoice $invoice): void
    {
        if (!$invoice->customer_id) {
            return;
        }

        $customer = Customer::find($invoice->customer_id);

        if (!$customer) {
            Log::warning('Customer not found for invoice', [
                'customer_id' => $invoice->customer_id,
                'invoice_id' => $invoice->id
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
