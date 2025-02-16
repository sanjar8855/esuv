<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tariff;

class InvoiceService
{
    /**
     * Har oy avtomatik invoice yaratish
     */
    public function generateMonthlyInvoices()
    {
        $currentMonth = now()->format('Y-m');

        // **1. Hisoblagichi yo‘q mijozlar uchun avtomatik invoice**
        $customersWithoutMeters = Customer::where('is_active', true)
            ->where('has_water_meter', false)
            ->get();

        foreach ($customersWithoutMeters as $customer) {
            // Oxirgi oyning invoice mavjudligini tekshiramiz
            $existingInvoice = Invoice::where('customer_id', $customer->id)
                ->where('billing_period', $currentMonth)
                ->exists();

            if ($existingInvoice) {
                continue;
            }

            $tariff = Tariff::where('company_id', $customer->company_id)
                ->where('is_active', true)
                ->latest()
                ->first();

            if (!$tariff) {
                continue;
            }

            $previousMonth = now()->subMonth()->format('Y-m');

            // Oldingi oyning invoice'ini tekshiramiz
            $previousMonthInvoice = Invoice::where('customer_id', $customer->id)
                ->where('billing_period', $previousMonth)
                ->first();

            if (!$previousMonthInvoice) {
                $amount_due = $customer->family_members * $tariff->for_one_person;

                Invoice::create([
                    'customer_id'    => $customer->id,
                    'tariff_id'      => $tariff->id,
                    'billing_period' => $previousMonth,
                    'amount_due'     => $amount_due,
                    'due_date'       => now()->subMonth()->endOfMonth(),
                    'status'         => 'pending',
                ]);
            }

            $customer->updateBalance();
        }
    }
}
