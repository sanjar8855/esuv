<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tariff;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * ✅ Hisoblagichsiz mijozlar uchun oylik invoice yaratish
     */
    public function generateMonthlyInvoices()
    {
        $currentMonth = now()->format('Y-m');
        $previousMonth = now()->subMonth()->format('Y-m');

        Log::info('Starting monthly invoice generation', [
            'current_month' => $currentMonth,
            'previous_month' => $previousMonth
        ]);

        // ✅ Mijozlarni eager loading bilan olish
        $customersWithoutMeters = Customer::where('is_active', true)
            ->where('has_water_meter', false)
            ->with([
                'company',
                'invoices' => function($query) use ($currentMonth, $previousMonth) {
                    $query->whereIn('billing_period', [$currentMonth, $previousMonth]);
                }
            ])
            ->get();

        if ($customersWithoutMeters->isEmpty()) {
            Log::info('No customers without meters found');
            return 0;
        }

        // ✅ Tariflar
        $companyIds = $customersWithoutMeters->pluck('company_id')->unique();
        $tariffs = Tariff::whereIn('company_id', $companyIds)
            ->where('is_active', true)
            ->get()
            ->groupBy('company_id');

        $createdCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($customersWithoutMeters as $customer) {
            try {
                // ✅ Joriy oyning invoice tekshiruvi
                $hasCurrentInvoice = $customer->invoices
                    ->where('billing_period', $currentMonth)
                    ->isNotEmpty();

                if ($hasCurrentInvoice) {
                    $skippedCount++;
                    continue;
                }

                // ✅ Tariff
                $tariff = $tariffs->get($customer->company_id)?->first();

                if (!$tariff) {
                    Log::warning('No active tariff', [
                        'customer_id' => $customer->id,
                        'company_id' => $customer->company_id
                    ]);
                    $errorCount++;
                    continue;
                }

                // ✅ Validation
                if (!$customer->family_members || $customer->family_members <= 0) {
                    Log::warning('Invalid family members', [
                        'customer_id' => $customer->id
                    ]);
                    $errorCount++;
                    continue;
                }

                // ✅ Oldingi oyning invoice tekshiruvi
                $hasPreviousInvoice = $customer->invoices
                    ->where('billing_period', $previousMonth)
                    ->isNotEmpty();

                if (!$hasPreviousInvoice) {
                    $this->createInvoice($customer, $tariff, $previousMonth);
                    $createdCount++;
                }

            } catch (\Exception $e) {
                Log::error('Failed to generate invoice', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
                $errorCount++;
            }
        }

        Log::info('Monthly invoice generation completed', [
            'created' => $createdCount,
            'skipped' => $skippedCount,
            'errors' => $errorCount,
            'total' => $customersWithoutMeters->count()
        ]);

        return $createdCount;
    }

    /**
     * ✅ Invoice yaratish (helper)
     */
    protected function createInvoice(Customer $customer, Tariff $tariff, string $billingPeriod)
    {
        $amountDue = $customer->family_members * $tariff->for_one_person;

        $dueDate = Carbon::createFromFormat('Y-m', $billingPeriod)->endOfMonth();

        return Invoice::create([
            'customer_id' => $customer->id,
            'tariff_id' => $tariff->id,
            'billing_period' => $billingPeriod,
            'amount_due' => $amountDue,
            'due_date' => $dueDate,
            'status' => 'pending',
        ]);
    }

    /**
     * ✅ Hisoblagichli mijozlar uchun invoice yaratish
     */
    public function generateInvoicesForMeteredCustomers()
    {
        $currentMonth = now()->format('Y-m');
        $previousMonth = now()->subMonth()->format('Y-m');

        Log::info('Starting invoice generation for metered customers');

        $customersWithMeters = Customer::where('is_active', true)
            ->where('has_water_meter', true)
            ->with([
                'waterMeter.readings' => function($query) use ($previousMonth) {
                    $query->where('confirmed', true)
                        ->whereRaw("DATE_FORMAT(reading_date, '%Y-%m') = ?", [$previousMonth])
                        ->orderBy('reading_date', 'desc')
                        ->limit(2);
                },
                'invoices' => function($query) use ($currentMonth) {
                    $query->where('billing_period', $currentMonth);
                }
            ])
            ->get();

        if ($customersWithMeters->isEmpty()) {
            return 0;
        }

        $companyIds = $customersWithMeters->pluck('company_id')->unique();
        $tariffs = Tariff::whereIn('company_id', $companyIds)
            ->where('is_active', true)
            ->get()
            ->groupBy('company_id');

        $createdCount = 0;

        foreach ($customersWithMeters as $customer) {
            try {
                if ($customer->invoices->isNotEmpty()) {
                    continue;
                }

                $tariff = $tariffs->get($customer->company_id)?->first();

                if (!$tariff || !$customer->waterMeter) {
                    continue;
                }

                $readings = $customer->waterMeter->readings;

                if ($readings->count() < 2) {
                    continue;
                }

                $consumption = $readings->first()->reading - $readings->skip(1)->first()->reading;

                if ($consumption < 0) {
                    continue;
                }

                Invoice::create([
                    'customer_id' => $customer->id,
                    'tariff_id' => $tariff->id,
                    'billing_period' => $currentMonth,
                    'amount_due' => $consumption * $tariff->price_per_m3,
                    'due_date' => now()->endOfMonth(),
                    'status' => 'pending',
                ]);

                $createdCount++;

            } catch (\Exception $e) {
                Log::error('Failed to generate invoice for metered customer', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Invoice generation for metered customers completed', [
            'created' => $createdCount
        ]);

        return $createdCount;
    }
}
