<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyReportController extends Controller
{
    /**
     * ✅ Kunlik hisobot sahifasi
     */
    public function index(Request $request)
    {
        // ✅ Ruxsat tekshirish
        if (!auth()->user()->hasRole('company_owner')) {
            abort(403, 'Sizda kunlik hisobotlarni ko\'rish huquqi yo\'q.');
        }

        // ✅ Sana tanlash (default: bugun)
        $date = $request->input('date', today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        // ✅ Kompaniya filter
        $companyId = auth()->user()->company_id;

        // ✅ O'sha kundagi to'lovlar
        $payments = Payment::whereHas('customer', function($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->whereDate('payment_date', $selectedDate)
            ->with(['customer', 'createdBy', 'confirmedBy'])
            ->latest()
            ->get();

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

        return view('daily-reports.index', compact('payments', 'stats', 'selectedDate', 'pendingPayments'));
    }
}
