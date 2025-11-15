<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WaterMeter;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WaterMeterApiController extends Controller
{
    public function index(Request $request)
    {
        $query = WaterMeter::with(['customer'])
            ->whereHas('customer', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            });

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by expiring soon
        if ($request->has('expiring_soon')) {
            $query->where('expiration_date', '<=', now()->addMonths(3));
        }

        $query->orderBy('installation_date', 'desc');

        $perPage = $request->get('per_page', 20);
        $meters = $query->paginate($perPage);

        return response()->json([
            'data' => $meters->map(function ($meter) {
                return [
                    'id' => $meter->id,
                    'customer_id' => $meter->customer_id,
                    'customer_name' => $meter->customer->name,
                    'meter_number' => $meter->meter_number,
                    'installation_date' => $meter->installation_date,
                    'expiration_date' => $meter->expiration_date,
                    'last_reading_date' => $meter->last_reading_date,
                ];
            }),
            'pagination' => [
                'total' => $meters->total(),
                'current_page' => $meters->currentPage(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $meter = WaterMeter::with(['customer', 'readings'])
            ->whereHas('customer', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            })
            ->findOrFail($id);

        return response()->json([
            'id' => $meter->id,
            'customer_name' => $meter->customer->name,
            'meter_number' => $meter->meter_number,
            'installation_date' => $meter->installation_date,
            'expiration_date' => $meter->expiration_date,
            'last_reading_date' => $meter->last_reading_date,
            'readings' => $meter->readings->take(10)->map(function ($reading) {
                return [
                    'id' => $reading->id,
                    'reading' => $reading->reading,
                    'reading_date' => $reading->reading_date,
                    'confirmed' => $reading->confirmed,
                    'photo_url' => $reading->photo_url,
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'meter_number' => 'required|string|unique:water_meters,meter_number',
            'installation_date' => 'required|date',
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

        $validityPeriod = config('water_meter.default_validity_period', 8);
        $expirationDate = \Carbon\Carbon::parse($request->installation_date)
            ->addYears($validityPeriod);

        $meter = WaterMeter::create([
            'customer_id' => $request->customer_id,
            'meter_number' => $request->meter_number,
            'installation_date' => $request->installation_date,
            'expiration_date' => $expirationDate,
        ]);

        // Update customer
        $customer->update(['has_water_meter' => true]);

        return response()->json([
            'message' => 'Hisoblagich qo\'shildi',
            'meter' => [
                'id' => $meter->id,
                'meter_number' => $meter->meter_number,
                'expiration_date' => $meter->expiration_date,
            ],
        ], 201);
    }
}
