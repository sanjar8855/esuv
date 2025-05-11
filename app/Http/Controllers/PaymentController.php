<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables; // DataTables fasadini import qiling
use Illuminate\Support\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request) // Requestni metodga inject qiling
    {
        $user = auth()->user();

        // Asosiy so'rov
        $paymentsQuery = Payment::with(['customer', 'invoice']);

        // Admin bo‘lmasa, faqat o‘z kompaniyasiga tegishli to‘lovlarni olish
        // company_id mavjudligini tekshirish yaxshi amaliyot
        if (!$user->hasRole('admin') && $user->company_id) {
            $paymentsQuery->whereHas('customer', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            });
        }

        // DataTables AJAX so'rovini tekshirish
        if ($request->ajax()) {
            // Agar DataTables o'zining saralashini yubormasa,
            // standart saralashni qo'llaymiz (eng yangi yozuvlar birinchi)
            // DataTables odatda o'z saralash parametrlarini yuboradi
            if (!$request->has('order')) {
                $paymentsQuery->orderBy('created_at', 'desc');
            }

            return DataTables::eloquent($paymentsQuery)
                ->addIndexColumn() // "N" ustuni uchun
                ->addColumn('customer_link', function (Payment $payment) {
                    if ($payment->customer) {
                        return '<a href="'.route('customers.show', $payment->customer->id).'" class="badge badge-outline text-blue">'.e($payment->customer->name).'</a>';
                    }
                    return '<span class="badge badge-outline text-danger">Mijoz yo‘q</span>';
                })
                ->addColumn('invoice_display', function (Payment $payment) {
                    return $payment->invoice ? e($payment->invoice->invoice_number) : '<span class="badge badge-outline text-danger">Invoys yo‘q</span>';
                })
                ->editColumn('amount', function (Payment $payment) {
                    return number_format($payment->amount, 0, '.', ' ') . ' UZS';
                })
                ->editColumn('payment_method_display', function (Payment $payment) {
                    switch ($payment->payment_method) {
                        case 'cash': return 'Naqd pul';
                        case 'card': return 'Plastik orqali';
                        case 'transfer': return 'Bank orqali';
                        default: return 'Noaniq';
                    }
                })
                ->editColumn('payment_date_formatted', function (Payment $payment) { // Asl payment_date uchun
                    return $payment->payment_date ? Carbon::parse($payment->payment_date)->setTimezone(config('app.timezone', 'Asia/Tashkent'))->format('d.m.Y H:i') : '-';
                })
                ->editColumn('created_at_formatted', function (Payment $payment) { // Yangi ustun created_at uchun
                    return $payment->created_at ? $payment->created_at->setTimezone(config('app.timezone', 'Asia/Tashkent'))->format('d.m.Y H:i:s') : '-';
                })
                ->editColumn('status_display', function (Payment $payment) {
                    switch ($payment->status) {
                        case 'completed': return '<span class="badge bg-green text-green-fg">To\'langan</span>';
                        case 'failed': return '<span class="badge bg-red text-red-fg">Xatolik</span>';
                        case 'pending': return '<span class="badge bg-yellow text-yellow-fg">To\'lanmoqda</span>';
                        default: return '<span class="badge bg-secondary text-secondary-fg">Noaniq</span>';
                    }
                })
                ->addColumn('actions', function (Payment $payment) {
                    $showUrl = route('payments.show', $payment->id);
                    $editUrl = route('payments.edit', $payment->id);
                    $deleteUrl = route('payments.destroy', $payment->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $currentUser = auth()->user(); // Joriy foydalanuvchi

                    $buttons = '<a href="'.$showUrl.'" class="btn btn-info btn-sm">Ko\'rish</a> ';
                    // Faqat admin tahrirlashi va o'chirishi mumkin deb hisoblaymiz
                    if ($currentUser->hasRole('admin')) {
                        $buttons .= '<a href="'.$editUrl.'" class="btn btn-warning btn-sm">Tahrirlash</a> ';
                        $buttons .= '<form action="'.$deleteUrl.'" method="POST" style="display:inline;" onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">'.$csrf.$method.'<button type="submit" class="btn btn-danger btn-sm">O‘chirish</button></form>';
                    }
                    return $buttons;
                })
                ->rawColumns(['customer_link', 'invoice_display', 'status_display', 'actions']) // HTML ishlatilgan ustunlar
                ->make(true);
        }

        // AJAX bo'lmagan so'rov uchun (sahifa birinchi ochilganda sarlavha uchun)
        $paymentsCount = (clone $paymentsQuery)->count();

        return view('payments.index', compact('paymentsCount'));
    }


    public function create()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $customers = Customer::where('is_active', true)->get();
        } else {
            $customers = Customer::where('company_id', $user->company_id)
                ->where('is_active', true)
                ->get();
        }

        return view('payments.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,card,transfer',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $remainingAmount = $request->amount;

        $pendingInvoices = Invoice::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->orderBy('billing_period', 'asc')
            ->get();

        foreach ($pendingInvoices as $invoice) {
            $invoiceBalance = $invoice->amount_due - $invoice->payments()->sum('amount');

            if ($remainingAmount <= 0) break;

            if ($remainingAmount >= $invoiceBalance) {
                Payment::create([
                    'customer_id' => $customer->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $invoiceBalance,
                    'payment_date' => now(),
                    'payment_method' => $request->payment_method,
                    'status' => 'completed',
                ]);

                $invoice->update(['status' => 'paid']);
                $remainingAmount -= $invoiceBalance;
            } else {
                Payment::create([
                    'customer_id' => $customer->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $remainingAmount,
                    'payment_date' => now(),
                    'payment_method' => $request->payment_method,
                    'status' => 'completed',
                ]);

                $remainingAmount = 0;
            }
        }

        // **📌 Agar ortiqcha to‘lov bo‘lsa, mijoz balansiga qo‘shiladi**
        if ($remainingAmount > 0) {
            Payment::create([
                'customer_id' => $customer->id,
                'invoice_id' => null, // Invoice bilan bog‘lanmagan holda saqlanadi
                'amount' => $remainingAmount,
                'payment_date' => now(),
                'payment_method' => $request->payment_method,
                'status' => 'completed',
            ]);
        }

        // **📌 Balansni yangilash**
        $customer->updateBalance();

        if ($request->has('redirect_back')) {
            return redirect()->route('customers.show', $request->customer_id)
                ->with('success', 'To‘lov muvaffaqiyatli qo‘shildi!');
        }

        return redirect()->route('payments.index')->with('success', 'To‘lov muvaffaqiyatli qo‘shildi.');
    }


    public function show(Payment $payment)
    {
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $user = auth()->user();

        // Agar foydalanuvchi admin bo‘lsa, barcha mijozlarni oladi
        if ($user->hasRole('admin')) {
            $customers = Customer::where('is_active', true)->get();
        } else {
            // Faqatgina foydalanuvchining kompaniyasiga tegishli mijozlar
            $customers = Customer::where('company_id', $user->company_id)
                ->where('is_active', true)
                ->get();
        }

        return view('payments.edit', compact('payment', 'customers'));
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,card,transfer',
        ]);

        $payment->update([
            'customer_id' => $request->customer_id,
            'amount' => $request->amount,
            'payment_date' => now(),
            'payment_method' => $request->payment_method,
        ]);

        return redirect()->route('payments.index')->with('success', 'To‘lov muvaffaqiyatli yangilandi.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return redirect()->route('payments.index')->with('success', 'To‘lov muvaffaqiyatli o‘chirildi.');
    }
}
