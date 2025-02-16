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
        $invoices = Invoice::with(['customer', 'tariff'])->latest()->paginate(10);
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Yangi hisob-faktura yaratish formasi.
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)->get();
        $tariffs = Tariff::where('is_active', true)->get();
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
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Hisob-fakturani tahrirlash formasi.
     */
    public function edit(Invoice $invoice)
    {
        $customers = Customer::where('is_active', true)->get();
        $tariffs = Tariff::where('is_active', true)->get();
        return view('invoices.edit', compact('invoice', 'customers', 'tariffs'));
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
