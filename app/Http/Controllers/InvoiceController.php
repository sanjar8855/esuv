<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Tariff;
use App\Services\InvoiceService;

class InvoiceController extends Controller
{
    /**
     * Hisob-fakturalar ro‘yxatini chiqarish.
     */
    public function index()
    {
        $user = auth()->user();

        // **📌 Asosiy query**
        $invoicesQuery = Invoice::with(['customer', 'tariff'])
            ->orderBy('created_at', 'desc');

        // **📌 Admin bo‘lmasa, faqat o‘z kompaniyasiga tegishli invoicelarni olish**
        if (!$user->hasRole('admin') && $user->company) {
            $invoicesQuery->whereHas('customer', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            });
        }

        // **📌 Jami invoice'lar sonini olish**
        $invoicesCount = (clone $invoicesQuery)->count();

        // **📌 Sahifalash (pagination)**
        $invoices = $invoicesQuery->paginate(20)->withQueryString();

        return view('invoices.index', compact('invoices', 'invoicesCount'));
    }

    /**
     * Yangi hisob-faktura yaratish formasi.
     */
    public function create()
    {
        $user = auth()->user();

        // Admin bo'lsa, barcha aktiv mijozlarni olamiz
        if ($user->hasRole('admin')) {
            $customers = Customer::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();
            $tariffs = Tariff::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Foydalanuvchi kompaniyasi borligini tekshiramiz
            $company = optional($user->company);

            if (!$company) {
                return back()->with('error', 'Siz hech qanday kompaniyaga bog‘lanmagansiz.');
            }

            // Ushbu kompaniyaga tegishli faqat aktiv mijozlarni olamiz
            $customers = Customer::where('company_id', $company->id)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();

            // Foydalanuvchiga tegishli kompaniyaning eng so‘nggi aktiv tarifini olish
            $tariffs = Tariff::where('company_id', optional($user->company)->id)
                ->where('is_active', true)
                ->orderBy('valid_from', 'desc') // Eng yangi tarifni olish uchun
                ->get();
        }

        return view('invoices.create', compact('customers', 'tariffs'));
    }

    /**
     * Yangi hisob-fakturani saqlash.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'tariff_id' => 'required|exists:tariffs,id',
            'billing_period' => 'required|string',
            'amount_due' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue',
        ]);

        Invoice::create($request->all());

        return redirect()->route('invoices.index')->with('success', 'Hisob-faktura muvaffaqiyatli qo‘shildi!');
    }

    /**
     * Ma’lum bir hisob-fakturani ko‘rsatish.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'tariff']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Hisob-fakturani tahrirlash formasi.
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load(['customer', 'tariff']);
        return view('invoices.edit', compact('invoice'));
    }

    /**
     * Hisob-fakturani yangilash.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'tariff_id' => 'required|exists:tariffs,id',
            'billing_period' => 'required|string',
            'amount_due' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue',
        ]);

        $invoice->update($request->all());

        return redirect()->route('invoices.index')->with('success', 'Hisob-faktura yangilandi!');
    }

    /**
     * Hisob-fakturani o‘chirish.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Hisob-faktura o‘chirildi!');
    }

    public function generateInvoices()
    {
        $service = new InvoiceService();
        $service->generateMonthlyInvoices();

        return redirect()->route('invoices.index')->with('success', 'Yangi invoicelar yaratildi!');
    }
}
