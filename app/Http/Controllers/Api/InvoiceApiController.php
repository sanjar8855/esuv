<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'tariff'])
            ->whereHas('customer', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            });

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 20);
        $invoices = $query->paginate($perPage);

        return response()->json([
            'data' => $invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'customer_name' => $invoice->customer->name,
                    'invoice_number' => $invoice->invoice_number,
                    'amount_due' => $invoice->amount_due,
                    'billing_period' => $invoice->billing_period,
                    'due_date' => $invoice->due_date,
                    'status' => $invoice->status,
                ];
            }),
            'pagination' => [
                'total' => $invoices->total(),
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $invoice = Invoice::with(['customer', 'tariff'])
            ->whereHas('customer', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            })
            ->findOrFail($id);

        return response()->json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'customer_name' => $invoice->customer->name,
            'amount_due' => $invoice->amount_due,
            'billing_period' => $invoice->billing_period,
            'due_date' => $invoice->due_date,
            'status' => $invoice->status,
            'tariff' => [
                'name' => $invoice->tariff->name,
                'price_per_m3' => $invoice->tariff->price_per_m3,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'tariff_id' => 'required|exists:tariffs,id',
            'amount_due' => 'required|integer|min:0',
            'billing_period' => 'required|string',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::where('id', $request->customer_id)
            ->where('company_id', $request->user()->company_id)
            ->firstOrFail();

        $invoice = Invoice::create([
            'customer_id' => $request->customer_id,
            'tariff_id' => $request->tariff_id,
            'amount_due' => $request->amount_due,
            'billing_period' => $request->billing_period,
            'due_date' => $request->due_date,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Faktura yaratildi',
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount_due' => $invoice->amount_due,
            ],
        ], 201);
    }
}
