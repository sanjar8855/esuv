<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Tariff;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth; // Auth ni import qiling
use Yajra\DataTables\Facades\DataTables; // DataTables fasadini import qiling
use Illuminate\Support\Carbon;

class InvoiceController extends Controller
{
    /**
     * Hisob-fakturalar ro‘yxatini chiqarish.
     */
    public function index(Request $request) // Requestni metodga inject qiling
    {
        $user = auth()->user();

        // Asosiy so'rov
        $invoicesQuery = Invoice::with(['customer', 'tariff']); // tariff'ni ham yuklaymiz, kerak bo'lsa ishlatamiz

        // Admin bo‘lmasa, faqat o‘z kompaniyasiga tegishli invoicelarni olish
        // company_id mavjudligini tekshirish yaxshi amaliyot
        if (!$user->hasRole('admin') && $user->company_id) {
            $invoicesQuery->whereHas('customer', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            });
        }

        // DataTables AJAX so'rovini tekshirish
        if ($request->ajax()) {
            // Agar DataTables o'zining saralashini yubormasa,
            // standart saralashni qo'llaymiz (eng yangi invoyslar birinchi)
            if (!$request->has('order')) {
                $invoicesQuery->orderBy('created_at', 'desc');
            }

            return DataTables::eloquent($invoicesQuery)
                ->addIndexColumn() // "N" ustuni uchun
                ->addColumn('customer_link', function (Invoice $invoice) {
                    if ($invoice->customer) {
                        return '<a href="'.route('customers.show', $invoice->customer->id).'" class="badge badge-outline text-blue">'.e($invoice->customer->name).'</a>';
                    }
                    return '<span class="badge badge-outline text-danger">Mijoz yo‘q</span>';
                })
                // invoice_number, billing_period, amount_due to'g'ridan-to'g'ri modeldan keladi
                // agar formatlash kerak bo'lsa, editColumn ishlatamiz
                ->editColumn('invoice_number', function(Invoice $invoice) {
                    return e($invoice->invoice_number);
                })
                ->editColumn('billing_period', function(Invoice $invoice) {
                    return e($invoice->billing_period);
                })
                ->editColumn('amount_due', function (Invoice $invoice) {
                    return number_format($invoice->amount_due, 0, '.', ' ') . ' UZS';
                })
                ->editColumn('status_display', function (Invoice $invoice) { // Status uchun alohida nom
                    switch ($invoice->status) {
                        case 'pending': return '<span class="badge bg-yellow text-yellow-fg">To\'liq to‘lanmagan</span>';
                        case 'paid': return '<span class="badge bg-green text-green-fg">To‘langan</span>';
                        case 'overdue': return '<span class="badge bg-red text-red-fg">Muddati o‘tgan</span>';
                        default: return '<span class="badge bg-secondary text-secondary-fg">Noaniq</span>';
                    }
                })
                ->editColumn('created_at_formatted', function (Invoice $invoice) { // created_at uchun format
                    return $invoice->created_at ? $invoice->created_at->setTimezone(config('app.timezone', 'Asia/Tashkent'))->format('d.m.Y H:i:s') : '-';
                })
                ->addColumn('actions', function (Invoice $invoice) {
                    $showUrl = route('invoices.show', $invoice->id);
                    $editUrl = route('invoices.edit', $invoice->id);
                    $deleteUrl = route('invoices.destroy', $invoice->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $currentUser = auth()->user();

                    $buttons = '<a href="'.$showUrl.'" class="btn btn-info btn-sm">Ko‘rish</a> ';
                    // Faqat admin tahrirlashi va o'chirishi mumkin deb hisoblaymiz
                    if ($currentUser->hasRole('admin')) {
                        $buttons .= '<a href="'.$editUrl.'" class="btn btn-warning btn-sm">Tahrirlash</a> ';
                        $buttons .= '<form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">'.$csrf.$method.'<button type="submit" class="btn btn-danger btn-sm">O‘chirish</button></form>';
                    }
                    return $buttons;
                })
                ->rawColumns(['customer_link', 'status_display', 'actions']) // HTML ishlatilgan ustunlar
                ->make(true);
        }

        // AJAX bo'lmagan so'rov uchun (sahifa birinchi ochilganda sarlavha uchun)
        $invoicesCount = (clone $invoicesQuery)->count(); // To'g'ri jami sonni olish uchun

        return view('invoices.index', compact('invoicesCount'));
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
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'tariff_id' => 'required|exists:tariffs,id',
            'billing_period' => 'required|date_format:Y-m',
            'amount_due' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue',
        ]);

        Invoice::create($validatedData);

        // Agar forma mijozning show sahifasidan yuborilgan bo'lsa, o'sha yerga qaytamiz
        if ($request->has('redirect_to_customer_show') && $request->customer_id) {
            return redirect()->route('customers.show', $request->customer_id)
                ->with('success', 'Hisob-faktura muvaffaqiyatli qo‘shildi!');
        }

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
