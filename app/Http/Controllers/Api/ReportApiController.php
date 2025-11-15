<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportApiController extends Controller
{
    /**
     * Get daily payments report
     */
    public function dailyPayments(Request $request)
    {
        $date = $request->get('date', today());

        $payments = Payment::whereHas('customer', function ($q) use ($request) {
            $q->where('company_id', $request->user()->company_id);
        })
            ->whereDate('payment_date', $date)
            ->where('confirmed', true)
            ->get();

        $totalAmount = $payments->sum('amount');
        $totalCount = $payments->count();

        // Group by payment method
        $byPaymentMethod = $payments->groupBy('payment_method')->map(function ($items, $method) {
            return [
                'method' => $method,
                'count' => $items->count(),
                'amount' => $items->sum('amount'),
            ];
        })->values();

        return response()->json([
            'date' => $date,
            'total_amount' => $totalAmount,
            'total_count' => $totalCount,
            'by_payment_method' => $byPaymentMethod,
            'payments' => $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'customer_name' => $payment->customer->name,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_date' => $payment->payment_date,
                ];
            }),
        ]);
    }

    /**
     * Get customer debts report
     */
    public function customerDebts(Request $request)
    {
        $customers = Customer::where('company_id', $request->user()->company_id)
            ->where('balance', '<', 0)
            ->orderBy('balance', 'asc')
            ->get();

        $totalDebt = abs($customers->sum('balance'));

        return response()->json([
            'total_debt' => $totalDebt,
            'total_customers' => $customers->count(),
            'customers' => $customers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'account_number' => $customer->account_number,
                    'balance' => $customer->balance,
                    'debt' => abs($customer->balance),
                ];
            }),
        ]);
    }

    /**
     * Get dashboard statistics
     */
    public function dashboardStats(Request $request)
    {
        $companyId = $request->user()->company_id;

        // Total customers
        $totalCustomers = Customer::where('company_id', $companyId)->count();
        $activeCustomers = Customer::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();

        // Today's payments
        $todayPayments = Payment::whereHas('customer', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->whereDate('payment_date', today())
            ->where('confirmed', true)
            ->get();

        $todayPaymentsCount = $todayPayments->count();
        $todayPaymentsSum = $todayPayments->sum('amount');

        // Pending payments (not confirmed)
        $pendingPayments = Payment::whereHas('customer', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->where('confirmed', false)
            ->count();

        // Total debt
        $totalDebt = abs(Customer::where('company_id', $companyId)
            ->where('balance', '<', 0)
            ->sum('balance'));

        $customersWithDebt = Customer::where('company_id', $companyId)
            ->where('balance', '<', 0)
            ->count();

        // Unpaid invoices
        $unpaidInvoices = Invoice::whereHas('customer', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->whereIn('status', ['pending', 'overdue'])
            ->count();

        // Current tariff
        $currentTariff = Tariff::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('valid_from', '<=', today())
            ->where(function ($q) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', today());
            })
            ->first();

        // Recent payments (last 5)
        $recentPayments = Payment::with('customer')
            ->whereHas('customer', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->where('confirmed', true)
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'customer_name' => $payment->customer->name,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                ];
            });

        return response()->json([
            'customers' => [
                'total' => $totalCustomers,
                'active' => $activeCustomers,
                'with_debt' => $customersWithDebt,
            ],
            'payments' => [
                'today_count' => $todayPaymentsCount,
                'today_sum' => $todayPaymentsSum,
                'pending' => $pendingPayments,
            ],
            'debts' => [
                'total_debt' => $totalDebt,
                'customers_count' => $customersWithDebt,
            ],
            'invoices' => [
                'unpaid' => $unpaidInvoices,
            ],
            'current_tariff' => $currentTariff ? [
                'name' => $currentTariff->name,
                'price_per_m3' => $currentTariff->price_per_m3,
                'for_one_person' => $currentTariff->for_one_person,
            ] : null,
            'recent_payments' => $recentPayments,
        ]);
    }
}
