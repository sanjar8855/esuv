<?php
// app/Http/Controllers/DailyReportController.php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Company;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyReportController extends Controller
{
    /**
     * ✅ Kunlik hisobot (Admin va Company Owner)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // ✅ Ruxsat tekshirish
        if (!$user->hasRole('admin') && !$user->hasRole('company_owner')) {
            abort(403, 'Sizda kunlik hisobotlarni ko\'rish huquqi yo\'q.');
        }

        // ✅ Sana (default: bugun)
        $date = $request->input('date', today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        // ✅ Kompaniya filter
        if ($user->hasRole('admin')) {
            // Admin - barcha kompaniyalarni ko'radi
            $companies = Company::orderBy('name')->get();

            // Tanlangan kompaniya (default: hammasi)
            $selectedCompanyId = $request->input('company_id', 'all');

            if ($selectedCompanyId === 'all') {
                $companyId = null; // Barcha kompaniyalar
            } else {
                $companyId = $selectedCompanyId;
            }
        } else {
            // Company Owner - faqat o'z kompaniyasini ko'radi
            $companies = collect(); // Bo'sh collection
            $companyId = $user->company_id;
            $selectedCompanyId = $companyId;
        }

        // ✅ To'lovlar query
        $paymentsQuery = Payment::with(['customer', 'createdBy', 'confirmedBy'])
            ->whereDate('payment_date', $selectedDate);

        // ✅ Kompaniya filter
        if ($companyId) {
            $paymentsQuery->whereHas('customer', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }

        $payments = $paymentsQuery->latest()->get();

        // ✅ Statistika
        $confirmedPayments = $payments->where('confirmed', true);
        $pendingPayments = $payments->where('confirmed', false);

        $stats = [
            'total_count' => $payments->count(),
            'confirmed_count' => $confirmedPayments->count(),
            'pending_count' => $pendingPayments->count(),

            'total_amount' => $payments->sum('amount'),
            'confirmed_amount' => $confirmedPayments->sum('amount'),
            'pending_amount' => $pendingPayments->sum('amount'),
        ];

        // ✅ Kompaniya bo'yicha statistika (Admin uchun)
        $companyStats = [];
        if ($user->hasRole('admin') && $selectedCompanyId === 'all') {
            $companyStats = Payment::with('customer.company')
                ->whereDate('payment_date', $selectedDate)
                ->get()
                ->groupBy('customer.company_id')
                ->map(function($companyPayments) {
                    $confirmed = $companyPayments->where('confirmed', true);
                    $pending = $companyPayments->where('confirmed', false);

                    return [
                        'company' => $companyPayments->first()->customer->company ?? null,
                        'total_count' => $companyPayments->count(),
                        'confirmed_count' => $confirmed->count(),
                        'pending_count' => $pending->count(),
                        'total_amount' => $companyPayments->sum('amount'),
                        'confirmed_amount' => $confirmed->sum('amount'),
                        'pending_amount' => $pending->sum('amount'),
                    ];
                })
                ->sortByDesc('total_amount');
        }

        return view('daily-reports.index', compact(
            'payments',
            'stats',
            'selectedDate',
            'pendingPayments',
            'companies',
            'selectedCompanyId',
            'companyStats'
        ));
    }
}
