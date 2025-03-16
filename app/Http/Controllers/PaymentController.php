<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // **📌 Asosiy query**
        $paymentsQuery = Payment::with(['customer', 'invoice'])
            ->orderBy('payment_date', 'desc');

        // **📌 Admin bo‘lmasa, faqat o‘z kompaniyasiga tegishli to‘lovlarni olish**
        if (!$user->hasRole('admin') && $user->company) {
            $paymentsQuery->whereHas('customer', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            });
        }

        // **📌 Jami to‘lovlar sonini olish**
        $paymentsCount = (clone $paymentsQuery)->count();

        // **📌 Sahifalash (pagination)**
        $payments = $paymentsQuery->paginate(20)->withQueryString();

        return view('payments.index', compact('payments', 'paymentsCount'));
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
