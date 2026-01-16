<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Tariff;
use App\Models\Invoice;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\Neighborhood;
use App\Models\Street;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        // ✅ Asosiy statistika
        $customersCount = Customer::where('company_id', $companyId)->count();

        $debtorsCount = Customer::where('company_id', $companyId)
            ->where('balance', '<', 0)
            ->count();

        $totalDebt = abs(Customer::where('company_id', $companyId)
            ->where('balance', '<', 0)
            ->sum('balance'));

        $profitCustomersCount = Customer::where('company_id', $companyId)
            ->where('balance', '>', 0)
            ->count();

        $totalProfit = Customer::where('company_id', $companyId)
            ->where('balance', '>', 0)
            ->sum('balance');

        // ✅ Tariff
        $tariff = Tariff::where('company_id', $companyId)
                ->where('is_active', true)
                ->latest('created_at')
                ->first() ?? new Tariff(['price_per_m3' => 0]);

        // ✅ Oyning boshidan oxirigacha
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $period = CarbonPeriod::create($start, $end);

        // ✅ Invoyslar/To'lovlar grafigi ma'lumotlari (ixtiyoriy - faqat kerak bo'lsa)
        $customerIds = Customer::where('company_id', $companyId)->pluck('id');

        $monthlyInvoicesData = Invoice::whereIn('customer_id', $customerIds)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, SUM(amount_due) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $monthlyPaymentsData = Payment::whereIn('customer_id', $customerIds)
            ->whereBetween('payment_date', [$start, $end])
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartInvoiceData = [];
        $chartPaymentData = [];

        foreach ($period as $date) {
            $dayString = $date->format('Y-m-d');
            $chartLabels[] = $dayString;

            $invoiceRow = $monthlyInvoicesData->get($dayString);
            $paymentRow = $monthlyPaymentsData->get($dayString);

            $chartInvoiceData[] = $invoiceRow ? (float)$invoiceRow->total : 0;
            $chartPaymentData[] = $paymentRow ? (float)$paymentRow->total : 0;
        }

        // ✅ MFY (Mahalla) statistikasi
        $neighborhoodStats = Neighborhood::select(
                'neighborhoods.id',
                'neighborhoods.name',
                \DB::raw('COUNT(DISTINCT streets.id) as streets_count'),
                \DB::raw('COUNT(customers.id) as customers_count'),
                \DB::raw('SUM(ABS(CASE WHEN customers.balance < 0 THEN customers.balance ELSE 0 END)) as total_debt')
            )
            ->join('streets', 'neighborhoods.id', '=', 'streets.neighborhood_id')
            ->join('customers', 'streets.id', '=', 'customers.street_id')
            ->where('customers.company_id', $companyId)
            ->groupBy('neighborhoods.id', 'neighborhoods.name')
            ->orderByDesc('total_debt')
            ->get()
            ->map(function ($item) {
                return [
                    'neighborhood_id' => $item->id,
                    'neighborhood_name' => $item->name,
                    'streets_count' => $item->streets_count,
                    'customers_count' => $item->customers_count,
                    'total_debt' => $item->total_debt,
                ];
            });

        // ✅ Barcha ko'chalar ro'yxati (filter, search, pagination)
        $streetsQuery = Customer::select(
                'street_id',
                'streets.name as street_name',
                'streets.neighborhood_id',
                'neighborhoods.name as neighborhood_name',
                \DB::raw('COUNT(customers.id) as customers_count'),
                \DB::raw('SUM(ABS(CASE WHEN customers.balance < 0 THEN customers.balance ELSE 0 END)) as total_debt')
            )
            ->join('streets', 'customers.street_id', '=', 'streets.id')
            ->join('neighborhoods', 'streets.neighborhood_id', '=', 'neighborhoods.id')
            ->where('customers.company_id', $companyId)
            ->groupBy('street_id', 'streets.name', 'streets.neighborhood_id', 'neighborhoods.name');

        // Filter: MFY bo'yicha
        if ($request->filled('neighborhood_id')) {
            $streetsQuery->where('streets.neighborhood_id', $request->neighborhood_id);
        }

        // Search: Ko'cha nomi bo'yicha
        if ($request->filled('search')) {
            $streetsQuery->where('streets.name', 'LIKE', '%' . $request->search . '%');
        }

        // Sort: Qarzdorlik bo'yicha
        $sortBy = $request->get('sort_by', 'total_debt');
        $sortDir = $request->get('sort_dir', 'desc');

        if ($sortBy === 'total_debt') {
            $streetsQuery->orderBy('total_debt', $sortDir);
        } elseif ($sortBy === 'customers_count') {
            $streetsQuery->orderBy('customers_count', $sortDir);
        } elseif ($sortBy === 'street_name') {
            $streetsQuery->orderBy('streets.name', $sortDir);
        }

        $allStreets = $streetsQuery->paginate(50)->appends($request->except('page'));

        // MFY ro'yxati (filter uchun)
        $neighborhoods = Neighborhood::whereHas('streets', function($q) use ($companyId) {
                $q->whereHas('customers', function($q2) use ($companyId) {
                    $q2->where('company_id', $companyId);
                });
            })
            ->orderBy('name')
            ->get();

        return view('pages.dashboard', compact(
            'debtorsCount',
            'totalDebt',
            'profitCustomersCount',
            'totalProfit',
            'tariff',
            'customersCount',
            'chartLabels',
            'chartInvoiceData',
            'chartPaymentData',
            'neighborhoodStats',
            'allStreets',
            'neighborhoods'
        ));
    }
}
