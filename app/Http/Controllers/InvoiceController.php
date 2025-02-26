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

        // Admin barcha tariflarni ko‘radi
        if ($user->hasRole('admin')) {
            $invoices = Invoice::with(['customer', 'tariff'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // Xodim faqat o‘z kompaniyasining mijozlariga tegishli tariflarni ko‘radi
            $customerIds = optional($user->company)->customers->pluck('id')->toArray();

            if (empty($customerIds)) {
                // Agar mijozlar yo‘q bo‘lsa, bo‘sh natija qaytariladi
                $invoices = collect(); // Bo‘sh kolleksiya qaytarish
            } else {
                $invoices = Invoice::whereIn('customer_id', $customerIds)
                    ->with(['customer', 'tariff'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
            }
        }

        return view('invoices.index', compact('invoices'));
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
        }

        // Foydalanuvchiga tegishli kompaniyaning eng so‘nggi aktiv tarifini olish
        $tariff = Tariff::where('company_id', optional($user->company)->id)
            ->where('is_active', true)
            ->orderBy('valid_from', 'desc') // Eng yangi tarifni olish uchun
            ->first();

        return view('invoices.create', compact('customers', 'tariff'));
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
