<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Jobs\SendInvoiceNotificationJob;

class InvoiceObserver
{
    public function created(Invoice $invoice)
    {
        SendInvoiceNotificationJob::dispatch($invoice);
    }

    /**
     * Handle the Invoice "saved" event (yaratilganda yoki yangilanganda ishlaydi).
     */
    public function saved(Invoice $invoice): void
    {
        // Mijoz balansini yangilash
        if ($invoice->customer) { // Mijoz mavjudligini tekshirish
            // Agar $invoice->customer allaqachon yuklangan bo'lsa ishlaydi
            // Aks holda, $customer = Customer::find($invoice->customer_id); qilib topish mumkin
            $invoice->customer->updateBalance();
            \Log::info("Balance updated for customer ID {$invoice->customer_id} due to invoice ID {$invoice->id} saved.");
        }

        // Xabarnoma yuborish (faqat yangi yaratilganda yuborish uchun)
        if ($invoice->wasRecentlyCreated) {
            SendInvoiceNotificationJob::dispatch($invoice);
        }
        // Agar har qanday o'zgarishda xabarnoma kerak bo'lsa, yuqoridagi if ni olib tashlang.
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        // Mijoz balansini yangilash
        // Invoys o'chirilganda, customer relation orqali topishga harakat qilamiz
        // yoki customer_id orqali to'g'ridan-to'g'ri.
        // Eng ishonchlisi customer_id orqali qayta topish:
        if ($invoice->customer_id) {
            $customer = Customer::find($invoice->customer_id);
            if ($customer) {
                $customer->updateBalance();
                \Log::info("Balance updated for customer ID {$invoice->customer_id} due to invoice ID {$invoice->id} deleted.");
            }
        }
    }
}
