<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use App\Models\WaterMeter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class MeterReadingApiController extends Controller
{
    public function index(Request $request)
    {
        $query = MeterReading::with(['waterMeter.customer'])
            ->whereHas('waterMeter.customer', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            });

        // Filter by water meter
        if ($request->has('water_meter_id')) {
            $query->where('water_meter_id', $request->water_meter_id);
        }

        // Filter by confirmed
        if ($request->has('confirmed')) {
            $query->where('confirmed', $request->confirmed);
        }

        $query->orderBy('reading_date', 'desc');

        $perPage = $request->get('per_page', 20);
        $readings = $query->paginate($perPage);

        return response()->json([
            'data' => $readings->map(function ($reading) {
                return [
                    'id' => $reading->id,
                    'water_meter_id' => $reading->water_meter_id,
                    'customer_name' => $reading->waterMeter->customer->name ?? null,
                    'meter_number' => $reading->waterMeter->meter_number ?? null,
                    'reading' => $reading->reading,
                    'reading_date' => $reading->reading_date,
                    'confirmed' => $reading->confirmed,
                    'photo_url' => $reading->photo_url ? url($reading->photo_url) : null,
                ];
            }),
            'pagination' => [
                'total' => $readings->total(),
                'current_page' => $readings->currentPage(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $reading = MeterReading::with(['waterMeter.customer'])
            ->whereHas('waterMeter.customer', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            })
            ->findOrFail($id);

        return response()->json([
            'id' => $reading->id,
            'water_meter_id' => $reading->water_meter_id,
            'customer_name' => $reading->waterMeter->customer->name,
            'meter_number' => $reading->waterMeter->meter_number,
            'reading' => $reading->reading,
            'reading_date' => $reading->reading_date,
            'confirmed' => $reading->confirmed,
            'photo_url' => $reading->photo_url ? url($reading->photo_url) : null,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'water_meter_id' => 'required|exists:water_meters,id',
            'reading' => 'required|integer|min:0',
            'reading_date' => 'nullable|date',
            'photo' => 'nullable|image|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify water meter belongs to user's company
        $waterMeter = WaterMeter::with('customer')
            ->whereHas('customer', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            })
            ->findOrFail($request->water_meter_id);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('meter_readings', 'public');
        }

        $reading = MeterReading::create([
            'water_meter_id' => $request->water_meter_id,
            'reading' => $request->reading,
            'reading_date' => $request->reading_date ?? now(),
            'photo_url' => $photoPath ? 'storage/' . $photoPath : null,
            'confirmed' => false,
        ]);

        return response()->json([
            'message' => 'Ko\'rsatkich qo\'shildi',
            'reading' => [
                'id' => $reading->id,
                'reading' => $reading->reading,
                'reading_date' => $reading->reading_date,
                'photo_url' => $reading->photo_url ? url($reading->photo_url) : null,
            ],
        ], 201);
    }

    public function confirm(Request $request, $id)
    {
        $reading = MeterReading::with('waterMeter.customer')
            ->whereHas('waterMeter.customer', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            })
            ->findOrFail($id);

        if ($reading->confirmed) {
            return response()->json([
                'message' => 'Ko\'rsatkich allaqachon tasdiqlangan',
            ], 400);
        }

        $reading->update(['confirmed' => true]);

        // Update water meter last reading date
        $reading->waterMeter->update([
            'last_reading_date' => $reading->reading_date,
        ]);

        return response()->json([
            'message' => 'Ko\'rsatkich tasdiqlandi',
        ]);
    }
}
