<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Tariff;
use App\Models\Invoice;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        // Shu kompaniyaga tegishli mijozlar IDlarini olish
        $customerIds = Customer::where('company_id', $companyId)->pluck('id');
        $customersCount = $customerIds->count();

        $allCustomers = Customer::where('company_id', $companyId)->get();

        // Faqat qarzdor mijozlar va ularning qarzdorlik summasini aniqlash
        $debtors = $allCustomers->filter(function ($customer) {
            $totalDue = $customer->invoices()->sum('amount_due'); // barcha invoyslarni olamiz
            $totalPaid = $customer->payments()->sum('amount');     // barcha to'lovlar
            return ($totalPaid - $totalDue) < 0;
        });

        $debtorsCount = $debtors->count();

        $totalDebt = $debtors->sum(function ($customer) {
            $totalDue = $customer->invoices()->sum('amount_due');
            $totalPaid = $customer->payments()->sum('amount');
            return abs($totalPaid - $totalDue);
        });

        // Oyning boshidan oxirigacha bo'lgan sanalar oralig‘i
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $period = CarbonPeriod::create($start, $end);

        // Asosiy query: agar foydalanuvchi admin bo‘lmasa, faqat o‘z kompaniyasidagi mijozlar
        $baseQuery = Customer::query();
        if (!$user->hasRole('admin') && $user->company_id) {
            $baseQuery->where('company_id', $user->company_id);
        }

        // Foyda beruvchi mijozlar soni
        $profitCustomersCount = (clone $baseQuery)
            ->where('balance', '>', 0)
            ->count();

        // Jami foyda summasi
        $totalProfit = (clone $baseQuery)
            ->where('balance', '>', 0)
            ->sum('balance');

        // Tariff ma'lumotlari: faol tarifni olamiz, agar mavjud bo‘lmasa, 0 qiymatli model qaytaramiz
        $tariff = Tariff::where('company_id', $companyId)
                ->where('is_active', true)
                ->latest('created_at')
                ->first() ?? new Tariff(['price_per_m3' => 0]);

        // Hozirgi oy uchun
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Hozirgi oydagi invoyslar soni va summasi
        $monthlyInvoicesCount = Invoice::whereIn('customer_id', $customerIds)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $monthlyInvoicesSum = Invoice::whereIn('customer_id', $customerIds)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount_due');

        // Hozirgi oydagi to'lovlar soni va summasi
        $monthlyPaymentsCount = Payment::whereIn('customer_id', $customerIds)
            ->whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->count();

        $monthlyPaymentsSum = Payment::whereIn('customer_id', $customerIds)
            ->whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->sum('amount');

        // Hozirgi oydagi invoyslar (kunlik) – grouping by sana alias "date"
        $monthlyData = Invoice::whereIn('customer_id', $customerIds)
            ->whereMonth('created_at', $start->month)
            ->whereYear('created_at', $start->year)
            ->selectRaw('DATE(created_at) as date, SUM(amount_due) as invoice_sum')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Hozirgi oydagi to'lovlar (kunlik)
        $monthlyPaymentsData = Payment::whereIn('customer_id', $customerIds)
            ->whereMonth('payment_date', $start->month)
            ->whereYear('payment_date', $start->year)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Grafik uchun ma'lumotlar: har bir kun uchun invoys va to'lov summalarini massivga joylaymiz
        $chartLabels = [];
        $chartInvoiceData = [];
        $chartPaymentData = [];

        foreach ($period as $date) {
            $dayString = $date->format('Y-m-d');
            $chartLabels[] = $dayString;

            $invoiceRow = $monthlyData->get($dayString);
            $paymentRow = $monthlyPaymentsData->get($dayString);

            $chartInvoiceData[] = $invoiceRow ? (float)$invoiceRow->invoice_sum : 0;
            $chartPaymentData[] = $paymentRow ? (float)$paymentRow->total : 0;
        }

        // Agar qo'shimcha grafiklar uchun eski so'rovlar kerak bo'lsa:
        $labels = Payment::whereIn('customer_id', $customerIds)
            ->selectRaw('DATE(payment_date) as date')
            ->groupByRaw('DATE(payment_date)')
            ->orderByRaw('DATE(payment_date) ASC')
            ->pluck('date')
            ->toArray();

        $series = Payment::whereIn('customer_id', $customerIds)
            ->selectRaw('SUM(amount) as total, DATE(payment_date) as date')
            ->groupByRaw('DATE(payment_date)')
            ->orderByRaw('DATE(payment_date) ASC')
            ->pluck('total')
            ->toArray();

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $period = CarbonPeriod::create($start, $end);

// Tasdiqlangan o'qishlar bo'yicha kunlik hisobot, faqat aktiv kompaniya mijozlaridan
        $confirmedData = MeterReading::whereBetween('reading_date', [$start, $end])
            ->where('confirmed', true)
            ->whereHas('waterMeter', function ($query) use ($companyId) {
                $query->whereHas('customer', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                });
            })
            ->selectRaw('DATE(reading_date) as date, SUM(reading) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

// Tasdiqlanmagan o'qishlar bo'yicha kunlik hisobot, faqat aktiv kompaniya mijozlaridan
        $unconfirmedData = MeterReading::whereBetween('reading_date', [$start, $end])
            ->where('confirmed', false)
            ->whereHas('waterMeter', function ($query) use ($companyId) {
                $query->whereHas('customer', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                });
            })
            ->selectRaw('DATE(reading_date) as date, SUM(reading) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartConfirmedData = [];
        $chartUnconfirmedData = [];

        foreach ($period as $date) {
            $dayString = $date->format('Y-m-d');
            $chartLabels[] = $dayString;
            $chartConfirmedData[] = $confirmedData->has($dayString) ? (int)$confirmedData->get($dayString)->count : 0;
            $chartUnconfirmedData[] = $unconfirmedData->has($dayString) ? (int)$unconfirmedData->get($dayString)->count : 0;
        }

        // Qarzdorlik bo‘yicha top 5 ko‘chani olish
        $topStreets = Customer::where('company_id', $companyId)
            ->where('balance', '<', 0)
            ->whereHas('street') // faqat ko‘chasi borlar
            ->with('street')
            ->get()
            ->groupBy('street_id')
            ->map(function ($customers, $streetId) {
                $totalDebt = $customers->sum(fn($c) => abs($c->balance));
                return [
                    'street_name' => $customers->first()->street->name ?? 'Nomaʼlum',
                    'total_debt' => $totalDebt,
                ];
            })
            ->sortByDesc('total_debt')
            ->take(5)
            ->values();

        // Maksimal qarzdorlik (foiz hisoblash uchun)
        $maxDebt = $topStreets->max('total_debt');

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
            'chartPaymentData',
            'chartLabels',
            'chartConfirmedData',
            'chartUnconfirmedData',
            'topStreets',
            'maxDebt'
        ));
    }
}
