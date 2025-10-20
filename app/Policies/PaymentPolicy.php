<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine if the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('payments');
    }

    /**
     * Determine if the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        // Admin har qanday payment ko'ra oladi
        if ($user->hasRole('admin')) {
            return true;
        }

        // Boshqa foydalanuvchilar faqat o'z kompaniyasining payment larini ko'ra oladi
        return $payment->customer && $payment->customer->company_id === $user->company_id;
    }

    /**
     * Determine if the user can create payments.
     */
    public function create(User $user): bool
    {
        return $user->can('payments');
    }

    /**
     * Determine if the user can update the payment.
     */
    public function update(User $user, Payment $payment): bool
    {
        // Admin har doim tahrirlashi mumkin
        if ($user->hasRole('admin')) {
            return true;
        }

        // Company owner faqat o'z kompaniyasining payment'larini tahrirlashi mumkin
        // va faqat tasdiqlanmaganlarni
        if ($user->hasRole('company_owner')) {
            return $payment->customer
                && $payment->customer->company_id === $user->company_id
                && !$payment->confirmed;
        }

        return false;
    }

    /**
     * Determine if the user can delete the payment.
     */
    public function delete(User $user, Payment $payment): bool
    {
        // Faqat admin o'chirishi mumkin
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can confirm the payment.
     */
    public function confirm(User $user, Payment $payment): bool
    {
        // Faqat company_owner tasdiqlashi mumkin
        if (!$user->hasRole('company_owner')) {
            return false;
        }

        // Faqat o'z kompaniyasining payment'larini
        return $payment->customer && $payment->customer->company_id === $user->company_id;
    }
}
