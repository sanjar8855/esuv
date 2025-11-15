<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['customer'])
            ->whereHas('customer', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            });

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by confirmed
        if ($request->has('confirmed')) {
            $query->where('confirmed', $request->confirmed);
        }

        // Today's payments
        if ($request->has('today') && $request->today) {
            $query->whereDate('payment_date', today());
        }

        $query->orderBy('payment_date', 'desc');

        $perPage = $request->get('per_page', 20);
        $payments = $query->paginate($perPage);

        return response()->json([
            'data' => $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'customer_id' => $payment->customer_id,
                    'customer_name' => $payment->customer->name,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'confirmed' => $payment->confirmed,
                    'confirmed_by' => $payment->confirmed_by,
                    'confirmed_at' => $payment->confirmed_at,
                    'invoice_id' => $payment->invoice_id,
                ];
            }),
            'pagination' => [
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $payment = Payment::with(['customer', 'invoice'])
            ->whereHas('customer', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            })
            ->findOrFail($id);

        return response()->json([
            'id' => $payment->id,
            'customer_id' => $payment->customer_id,
            'customer_name' => $payment->customer->name,
            'amount' => $payment->amount,
            'payment_date' => $payment->payment_date,
            'payment_method' => $payment->payment_method,
            'status' => $payment->status,
            'confirmed' => $payment->confirmed,
            'confirmed_by' => $payment->confirmed_by,
            'confirmed_at' => $payment->confirmed_at,
            'invoice' => $payment->invoice ? [
                'id' => $payment->invoice->id,
                'invoice_number' => $payment->invoice->invoice_number,
                'amount_due' => $payment->invoice->amount_due,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|integer|min:0',
            'payment_method' => 'required|in:cash,card,transfer,online',
            'payment_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if customer belongs to user's company
        $customer = Customer::where('id', $request->customer_id)
            ->where('company_id', $request->user()->company_id)
            ->firstOrFail();

        $payment = Payment::create([
            'customer_id' => $request->customer_id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date ?? now(),
            'status' => 'pending',
            'confirmed' => false,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'To\'lov muvaffaqiyatli qo\'shildi',
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'payment_date' => $payment->payment_date,
                'status' => $payment->status,
            ],
        ], 201);
    }

    public function confirm(Request $request, $id)
    {
        $payment = Payment::whereHas('customer', function ($q) use ($request) {
            $q->where('company_id', $request->user()->company_id);
        })->findOrFail($id);

        if ($payment->confirmed) {
            return response()->json([
                'message' => 'To\'lov allaqachon tasdiqlangan',
            ], 400);
        }

        $payment->update([
            'confirmed' => true,
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
            'status' => 'completed',
        ]);

        return response()->json([
            'message' => 'To\'lov tasdiqlandi',
            'payment' => [
                'id' => $payment->id,
                'confirmed' => $payment->confirmed,
                'confirmed_at' => $payment->confirmed_at,
            ],
        ]);
    }
}
