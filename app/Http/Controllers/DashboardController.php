<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Tariff;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // 🔹 Aktiv foydalanuvchining kompaniyasi
        $companyId = Auth::user()->company_id;

        $tariff = Tariff::where('company_id',$companyId)
            ->where('is_active', true)
            ->latest('created_at')
            ->first() ?? new Tariff(['price_per_m3' => 0]);

        // 🔹 Shu kompaniyaga tegishli mijozlar IDlarini olish
        $customerIds = Customer::where('company_id', $companyId)->pluck('id');
        $customersCount = $customerIds->count();

        // 🔹 Mijozlarning to‘lovlarini kunlik jamlash
        $payments = Payment::whereIn('customer_id', $customerIds)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 🔹 ApexCharts uchun ma’lumotlarni tayyorlash
        $labels = $payments->pluck('date')->toArray();
        $series = $payments->pluck('total')->toArray();

        return view('pages.dashboard', compact('labels', 'series', 'tariff', 'customersCount'));
    }
}
