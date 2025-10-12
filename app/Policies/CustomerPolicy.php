<?php
// app/Policies/CustomerPolicy.php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Ko'rish ruxsati
     */
    public function view(User $user, Customer $customer)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $customer->company_id === $user->company_id;
    }

    /**
     * Tahrirlash ruxsati
     */
    public function update(User $user, Customer $customer)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $customer->company_id === $user->company_id;
    }

    /**
     * O'chirish ruxsati
     */
    public function delete(User $user, Customer $customer)
    {
        // Faqat admin yoki company_owner o'chirishi mumkin
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('company_owner') && $customer->company_id === $user->company_id) {
            return true;
        }

        return false;
    }
}
