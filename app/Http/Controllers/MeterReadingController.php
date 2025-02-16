<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Tariff;
use App\Models\WaterMeter;
use Illuminate\Http\Request;

class MeterReadingController extends Controller
{
    public function index()
    {
        $meterReadings = MeterReading::with('waterMeter.customer')->paginate(10);
        return view('meter_readings.index', compact('meterReadings'));
    }

    public function create()
    {
        $waterMeters = WaterMeter::with('customer')->get();
        return view('meter_readings.create', compact('waterMeters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'water_meter_id' => 'required|exists:water_meters,id',
            'reading' => 'required|numeric|min:0',
            'photo_url' => 'nullable|string',
            'reading_date' => 'required|date',
            'confirmed' => 'required|boolean',
        ]);

        $meterReading = MeterReading::create($validated);
        $customer = $meterReading->waterMeter->customer;
        $tariff = Tariff::where('company_id', $customer->company_id)
            ->where('is_active', true)
            ->latest()
            ->first();

        if ($tariff) {
            $previousReading = MeterReading::where('water_meter_id', $meterReading->water_meter_id)
                ->where('reading_date', '<', $meterReading->reading_date)
                ->orderBy('reading_date', 'desc')
                ->first();

            if ($previousReading) {
                $consumption = $meterReading->reading - $previousReading->reading;
                $amount_due = $consumption * $tariff->price_per_m3;

                Invoice::create([
                    'customer_id'    => $customer->id,
                    'tariff_id'      => $tariff->id,
                    'billing_period' => now()->format('Y-m'),
                    'amount_due'     => $amount_due,
                    'due_date'       => now()->endOfMonth(),
                    'status'         => 'pending',
                ]);
            }
        }

        return redirect()->route('meter_readings.index')->with('success', 'Hisoblagich o‘qilishi muvaffaqiyatli qo‘shildi!');
    }

    public function show(MeterReading $meterReading)
    {
        return view('meter_readings.show', compact('meterReading'));
    }

    public function edit(MeterReading $meterReading)
    {
        $waterMeters = WaterMeter::with('customer')->get();
        return view('meter_readings.edit', compact('meterReading', 'waterMeters'));
    }

    public function update(Request $request, MeterReading $meterReading)
    {
        $request->validate([
            'water_meter_id' => 'required|exists:water_meters,id',
            'reading' => 'required|numeric|min:0',
            'photo_url' => 'nullable|string',
            'reading_date' => 'required|date',
            'confirmed' => 'required|boolean',
        ]);

        $meterReading->update($request->all());
        return redirect()->route('meter_readings.index')->with('success', 'Hisoblagich o‘qilishi muvaffaqiyatli yangilandi!');
    }

    public function destroy(MeterReading $meterReading)
    {
        $meterReading->delete();
        return redirect()->route('meter_readings.index')->with('success', 'Hisoblagich o‘qilishi muvaffaqiyatli o‘chirildi!');
    }
}
