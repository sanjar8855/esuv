<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine if the user can view any invoices.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('invoices');
    }

    /**
     * Determine if the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // Admin har qanday invoice ko'ra oladi
        if ($user->hasRole('admin')) {
            return true;
        }

        // Boshqa foydalanuvchilar faqat o'z kompaniyasining invoice larini ko'ra oladi
        return $invoice->customer && $invoice->customer->company_id === $user->company_id;
    }

    /**
     * Determine if the user can create invoices.
     */
    public function create(User $user): bool
    {
        return $user->can('invoices');
    }

    /**
     * Determine if the user can update the invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Faqat admin tahrirlashi mumkin
        if ($user->hasRole('admin')) {
            return true;
        }

        // Company owner faqat o'z kompaniyasining invoice'larini tahrirlashi mumkin
        if ($user->hasRole('company_owner')) {
            return $invoice->customer && $invoice->customer->company_id === $user->company_id;
        }

        return false;
    }

    /**
     * Determine if the user can delete the invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Faqat admin o'chirishi mumkin
        return $user->hasRole('admin');
    }
}
