<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Tariff;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $customerIds = Customer::where('company_id', $companyId)->pluck('id');
        $customersCount = $customerIds->count();

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $period = CarbonPeriod::create($start, $end);

        // Asosiy query
        $baseQuery = Customer::query();

        if (!$user->hasRole('admin') && $user->company_id) {
            $baseQuery->where('company_id', $user->company_id);
        }

        // Qarzdorlar soni
        $debtorsCount = (clone $baseQuery)->where('balance', '<', 0)->count();

        // Foydadagilar soni
        $profitCustomersCount = (clone $baseQuery)->where('balance', '>', 0)->count();

        // Jami qarz summasi
        $totalDebt = (clone $baseQuery)
            ->where('balance', '<', 0)
            ->get()
            ->sum(fn($customer) => abs($customer->balance));

        // Jami foydadagi summa
        $totalProfit = (clone $baseQuery)
            ->where('balance', '>', 0)
            ->sum('balance');

        // 🔹 Aktiv foydalanuvchining kompaniyasi

        $tariff = Tariff::where('company_id',$companyId)
            ->where('is_active', true)
            ->latest('created_at')
            ->first() ?? new Tariff(['price_per_m3' => 0]);

        // 🔹 Aktiv oydagi invoyslar
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // 🔹 Aktiv oydagi invoyslar soni va summasi
        $monthlyInvoicesCount = Invoice::whereIn('customer_id', $customerIds)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $monthlyInvoicesSum = Invoice::whereIn('customer_id', $customerIds)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount_due');

        // 🔹 Aktiv oydagi to'lovlar
        $monthlyPaymentsCount = Payment::whereIn('customer_id', $customerIds)
            ->whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->count();

        $monthlyPaymentsSum = Payment::whereIn('customer_id', $customerIds)
            ->whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->sum('amount');

        // 🔹 Aktiv oydagi invoyslar va to'lovlar uchun ma'lumotlar
        $monthlyData = Invoice::whereIn('customer_id', $customerIds)
            ->whereMonth('created_at', $start->month)
            ->whereYear('created_at', $start->year)
            ->selectRaw('DATE(created_at) as date, SUM(amount_due) as invoice_sum')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $monthlyPaymentsData = Payment::whereIn('customer_id', $customerIds)
            ->whereMonth('payment_date', $start->month)
            ->whereYear('payment_date', $start->year)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // 🔹 Mijozlarning to'lovlarini kunlik jamlash
        $payments = Payment::whereIn('customer_id', $customerIds)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 🔹 ApexCharts uchun ma'lumotlarni tayyorlash
        $labels = $payments->pluck('date')->toArray();
        $series = $payments->pluck('total')->toArray();

        $chartLabels = [];
        $chartInvoiceData = [];
        $chartPaymentData = [];

        foreach ($period as $date) {
            $dayString = $date->format('Y-m-d'); // masalan, "2025-03-01"
            $chartLabels[] = $dayString;

            $invoiceRow = $monthlyData->get($dayString); // collect-dan olamiz
            $paymentRow = $monthlyPaymentsData->get($dayString);

            $chartInvoiceData[] = $invoiceRow ? (float) $invoiceRow->invoice_sum : 0;
            $chartPaymentData[] = $paymentRow ? (float) $paymentRow->total : 0;
        }

        return view('pages.dashboard', compact(
            'debtorsCount',
            'totalDebt',
            'profitCustomersCount',
            'totalProfit',
            'labels',
            'series',
            'tariff',
            'customersCount',
            'monthlyInvoicesCount',
            'monthlyInvoicesSum',
            'monthlyPaymentsCount',
            'monthlyPaymentsSum',
            'monthlyData',
            'monthlyPaymentsData',
            'chartLabels',
            'chartInvoiceData',
            'chartPaymentData'
        ));
    }
}
