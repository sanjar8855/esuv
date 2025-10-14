<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $paymentsQuery = Payment::with(['customer', 'invoice', 'confirmedBy']); // ✅ confirmedBy qo'shildi

        if (!$user->hasRole('admin') && $user->company_id) {
            $paymentsQuery->whereHas('customer', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            });
        }

        if ($request->ajax()) {
            if (!$request->has('order')) {
                $paymentsQuery->orderBy('created_at', 'desc');
            }

            return DataTables::eloquent($paymentsQuery)
                ->addIndexColumn()
                ->addColumn('customer_link', function (Payment $payment) {
                    if ($payment->customer) {
                        return '<a href="'.route('customers.show', $payment->customer->id).'" class="badge badge-outline text-blue">'.e($payment->customer->name).'</a>';
                    }
                    return '<span class="badge badge-outline text-danger">Mijoz yo\'q</span>';
                })
                ->addColumn('invoice_display', function (Payment $payment) {
                    return $payment->invoice ? e($payment->invoice->invoice_number) : '<span class="badge badge-outline text-muted">Invoys yo\'q</span>';
                })
                ->editColumn('amount', function (Payment $payment) {
                    return number_format($payment->amount, 0, '.', ' ') . ' UZS';
                })
                ->editColumn('payment_method_display', function (Payment $payment) {
                    return match($payment->payment_method) {
                        'cash' => 'Naqd pul',
                        'card' => 'Plastik karta',
                        'transfer' => 'Bank o\'tkazmasi',
                        'online' => 'Onlayn to\'lov',
                        default => 'Noaniq'
                    };
                })
                ->editColumn('payment_date_formatted', function (Payment $payment) {
                    return $payment->payment_date ? Carbon::parse($payment->payment_date)->format('d.m.Y') : '-';
                })
                ->editColumn('created_at_formatted', function (Payment $payment) {
                    return $payment->created_at ? $payment->created_at->format('d.m.Y H:i') : '-';
                })
                // ✅ YANGI: Tasdiqlash holati
                ->addColumn('confirmed_status', function (Payment $payment) {
                    if ($payment->confirmed) {
                        $confirmedBy = $payment->confirmedBy ? e($payment->confirmedBy->name) : 'Admin';
                        $confirmedAt = $payment->confirmed_at ? $payment->confirmed_at->format('d.m.Y H:i') : '';
                        return '<span class="badge bg-success" title="'.$confirmedBy.' - '.$confirmedAt.'">✅ Tasdiqlangan</span>';
                    } else {
                        return '<span class="badge bg-warning">⏳ Kutilmoqda</span>';
                    }
                })
                ->editColumn('status_display', function (Payment $payment) {
                    return match($payment->status) {
                        'completed' => '<span class="badge bg-green">To\'langan</span>',
                        'failed' => '<span class="badge bg-red">Xatolik</span>',
                        'pending' => '<span class="badge bg-yellow">Kutilmoqda</span>',
                        default => '<span class="badge bg-secondary">Noaniq</span>'
                    };
                })
                ->addColumn('actions', function (Payment $payment) {
                    $showUrl = route('customers.show', $payment->customer_id); // ✅ To'g'ri URL
                    $currentUser = auth()->user();

                    $buttons = '<a href="'.$showUrl.'" class="btn btn-info btn-sm">Ko\'rish</a> ';

                    // ✅ Direktor uchun tasdiqlash tugmasi
                    if ($currentUser->hasRole('company_owner') && !$payment->confirmed) {
                        $confirmUrl = route('payments.confirm', $payment->id);
                        $csrf = csrf_field();
                        $method = method_field('PATCH');
                        $buttons .= '<form action="'.$confirmUrl.'" method="POST" style="display:inline;" onsubmit="return confirm(\'To\\\'lovni tasdiqlaysizmi?\');">'.$csrf.$method.'<button type="submit" class="btn btn-success btn-sm">Tasdiqlash</button></form> ';
                    }

                    // ✅ Admin uchun o'chirish
                    if ($currentUser->hasRole('admin')) {
                        $deleteUrl = route('payments.destroy', $payment->id);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $buttons .= '<form action="'.$deleteUrl.'" method="POST" style="display:inline;" onsubmit="return confirm(\'Haqiqatan ham o\\\'chirmoqchimisiz?\');">'.$csrf.$method.'<button type="submit" class="btn btn-danger btn-sm">O\'chirish</button></form>';
                    }

                    return $buttons;
                })
                ->rawColumns(['customer_link', 'invoice_display', 'confirmed_status', 'status_display', 'actions'])
                ->make(true);
        }

        $paymentsCount = (clone $paymentsQuery)->count();

        return view('payments.index', compact('paymentsCount'));
    }

    /**
     * ✅ Bitta to'lovni tasdiqlash
     */
    public function confirm(Payment $payment)
    {
        if (!auth()->user()->hasRole('company_owner')) {
            abort(403, 'Sizda to\'lovlarni tasdiqlash huquqi yo\'q.');
        }

        if ($payment->confirmed) {
            return redirect()->back()->with('warning', 'To\'lov allaqachon tasdiqlangan.');
        }

        $payment->update([
            'confirmed' => true,
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'To\'lov muvaffaqiyatli tasdiqlandi!');
    }

    /**
     * ✅ Ko'plab to'lovlarni tasdiqlash
     */
    public function confirmMultiple(Request $request)
    {
        if (!auth()->user()->hasRole('company_owner')) {
            abort(403, 'Sizda to\'lovlarni tasdiqlash huquqi yo\'q.');
        }

        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:payments,id'
        ]);

        $confirmed = Payment::whereIn('id', $request->payment_ids)
            ->where('confirmed', false)
            ->update([
                'confirmed' => true,
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

        $customerIds = Payment::whereIn('id', $request->payment_ids)->pluck('customer_id')->unique();

        foreach ($customerIds as $customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $customer->updateBalance();
            }
        }

        return redirect()->back()->with('success', "{$confirmed} ta to'lov tasdiqlandi!");
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
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,card,transfer,online',
            'confirmed' => 'sometimes|boolean',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        // ✅ Direktor emas bo'lsa, confirmed = false
        if (!auth()->user()->hasRole('company_owner')) {
            $validated['confirmed'] = false;
        } else {
            $validated['confirmed'] = $request->boolean('confirmed');

            if ($validated['confirmed']) {
                $validated['confirmed_by'] = auth()->id();
                $validated['confirmed_at'] = now();
            }
        }

        $remainingAmount = $request->amount;

        // ✅ Faqat tasdiqlangan to'lovlar invoice ga bog'lanadi
        if ($validated['confirmed']) {
            $pendingInvoices = Invoice::where('customer_id', $customer->id)
                ->where('status', 'pending')
                ->orderBy('billing_period', 'asc')
                ->get();

            foreach ($pendingInvoices as $invoice) {
                $invoiceBalance = $invoice->amount_due - $invoice->payments()->where('confirmed', true)->sum('amount');

                if ($remainingAmount <= 0) break;

                if ($remainingAmount >= $invoiceBalance) {
                    Payment::create([
                        'customer_id' => $customer->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $invoiceBalance,
                        'payment_date' => now(),
                        'payment_method' => $request->payment_method,
                        'status' => 'completed',
                        'confirmed' => true,
                        'confirmed_by' => auth()->id(),
                        'confirmed_at' => now(),
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
                        'confirmed' => true,
                        'confirmed_by' => auth()->id(),
                        'confirmed_at' => now(),
                    ]);

                    $remainingAmount = 0;
                }
            }
        }

        // ✅ Ortiqcha to'lov yoki tasdiqlanmagan to'lov
        if ($remainingAmount > 0) {
            Payment::create([
                'customer_id' => $customer->id,
                'invoice_id' => null,
                'amount' => $remainingAmount,
                'payment_date' => now(),
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'confirmed' => $validated['confirmed'],
                'confirmed_by' => $validated['confirmed'] ? auth()->id() : null,
                'confirmed_at' => $validated['confirmed'] ? now() : null,
            ]);
        }

        // ✅ Balance yangilash (Observer avtomatik)

        if ($request->has('redirect_back')) {
            return redirect()->route('customers.show', $request->customer_id)
                ->with('success', 'To\'lov muvaffaqiyatli qo\'shildi!');
        }

        return redirect()->route('payments.index')->with('success', 'To\'lov muvaffaqiyatli qo\'shildi.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['customer', 'invoice', 'confirmedBy']);
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $customers = Customer::where('is_active', true)->get();
        } else {
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
            'payment_method' => 'required|in:cash,card,transfer,online',
        ]);

        $payment->update([
            'customer_id' => $request->customer_id,
            'amount' => $request->amount,
            'payment_date' => now(),
            'payment_method' => $request->payment_method,
        ]);

        return redirect()->route('payments.index')->with('success', 'To\'lov muvaffaqiyatli yangilandi.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return redirect()->route('payments.index')->with('success', 'To\'lov muvaffaqiyatli o\'chirildi.');
    }
}
