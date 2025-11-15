<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerApiController extends Controller
{
    /**
     * Get customers list with pagination, search, and filter
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Customer::with(['street.neighborhood.city.region'])
            ->where('company_id', $request->user()->company_id);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter by street
        if ($request->has('street_id')) {
            $query->where('street_id', $request->street_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by has_water_meter
        if ($request->has('has_water_meter')) {
            $query->where('has_water_meter', $request->has_water_meter);
        }

        // Filter by balance (debt)
        if ($request->has('has_debt')) {
            $query->where('balance', '<', 0);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $customers = $query->paginate($perPage);

        return response()->json([
            'data' => $customers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'balance' => $customer->balance,
                    'account_number' => $customer->account_number,
                    'has_water_meter' => $customer->has_water_meter,
                    'family_members' => $customer->family_members,
                    'is_active' => $customer->is_active,
                    'street_name' => $customer->street->name ?? null,
                    'neighborhood_name' => $customer->street->neighborhood->name ?? null,
                    'city_name' => $customer->street->neighborhood->city->name ?? null,
                ];
            }),
            'pagination' => [
                'total' => $customers->total(),
                'per_page' => $customers->perPage(),
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'from' => $customers->firstItem(),
                'to' => $customers->lastItem(),
            ],
        ]);
    }

    /**
     * Get single customer
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $customer = Customer::with([
            'street.neighborhood.city.region',
            'waterMeter',
            'invoices' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
            'payments' => function ($query) {
                $query->orderBy('payment_date', 'desc')->limit(10);
            }
        ])
            ->where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'balance' => $customer->balance,
            'account_number' => $customer->account_number,
            'has_water_meter' => $customer->has_water_meter,
            'family_members' => $customer->family_members,
            'is_active' => $customer->is_active,
            'pdf_file' => $customer->pdf_file,
            'street_id' => $customer->street_id,
            'street_name' => $customer->street->name ?? null,
            'neighborhood_name' => $customer->street->neighborhood->name ?? null,
            'city_name' => $customer->street->neighborhood->city->name ?? null,
            'region_name' => $customer->street->neighborhood->city->region->name ?? null,
            'water_meter' => $customer->waterMeter ? [
                'id' => $customer->waterMeter->id,
                'meter_number' => $customer->waterMeter->meter_number,
                'installation_date' => $customer->waterMeter->installation_date,
                'expiration_date' => $customer->waterMeter->expiration_date,
                'last_reading_date' => $customer->waterMeter->last_reading_date,
            ] : null,
            'recent_invoices' => $customer->invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount_due' => $invoice->amount_due,
                    'status' => $invoice->status,
                    'billing_period' => $invoice->billing_period,
                    'due_date' => $invoice->due_date,
                ];
            }),
            'recent_payments' => $customer->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'confirmed' => $payment->confirmed,
                ];
            }),
        ]);
    }

    /**
     * Create new customer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'street_id' => 'required|exists:streets,id',
            'has_water_meter' => 'required|boolean',
            'family_members' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::create([
            'company_id' => $request->user()->company_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'street_id' => $request->street_id,
            'has_water_meter' => $request->has_water_meter,
            'family_members' => $request->family_members,
            'balance' => 0,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Mijoz muvaffaqiyatli yaratildi',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'account_number' => $customer->account_number,
                'balance' => $customer->balance,
            ],
        ], 201);
    }

    /**
     * Update customer
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'sometimes|required|string',
            'street_id' => 'sometimes|required|exists:streets,id',
            'has_water_meter' => 'sometimes|required|boolean',
            'family_members' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer->update($request->only([
            'name',
            'phone',
            'address',
            'street_id',
            'has_water_meter',
            'family_members',
            'is_active',
        ]));

        return response()->json([
            'message' => 'Mijoz muvaffaqiyatli yangilandi',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'account_number' => $customer->account_number,
                'balance' => $customer->balance,
            ],
        ]);
    }

    /**
     * Delete customer
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $customer = Customer::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        // Check if customer has unpaid invoices
        if ($customer->balance < 0) {
            return response()->json([
                'message' => 'Mijozni o\'chirish mumkin emas. Qarz mavjud.',
            ], 400);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Mijoz muvaffaqiyatli o\'chirildi',
        ]);
    }
}
