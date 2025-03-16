<?php

namespace App\Http\Controllers;

use App\Models\WaterMeter;
use App\Models\Customer;
use Illuminate\Http\Request;

class WaterMeterController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // **📌 Asosiy query**
        $waterMetersQuery = WaterMeter::with([
            'customer',
            'readings' => function ($query) {
                $query->orderBy('id', 'desc')->where('confirmed', 1);
            }
        ])->orderBy('meter_number', 'asc');

        // **📌 Admin bo‘lmasa, faqat o‘z kompaniyasidagi mijozlarning hisoblagichlarini olish**
        if (!$user->hasRole('admin') && $user->company) {
            $waterMetersQuery->whereHas('customer', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            });
        }

        // **📌 Jami hisoblagichlar sonini olish**
        $waterMetersCount = (clone $waterMetersQuery)->count();

        // **📌 Sahifalash (pagination)**
        $waterMeters = $waterMetersQuery->paginate(20)->withQueryString();

        return view('water_meters.index', compact('waterMeters', 'waterMetersCount'));
    }


    public function create()
    {
        $customers = Customer::all();
        return view('water_meters.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'meter_number' => 'required|numeric|unique:water_meters',
            'validity_period' => 'required|numeric',
            'last_reading_date' => 'nullable|date',
            'installation_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
        ]);

        WaterMeter::create($request->all());
        return redirect()->route('water_meters.index')->with('success', 'Hisoblagich muvaffaqiyatli qo‘shildi!');
    }

    public function show(WaterMeter $waterMeter)
    {
        $readings = $waterMeter->readings()
            ->orderBy('reading_date', 'desc')
            ->orderBy('reading', 'desc')
            ->paginate(5, ['*'], 'reading_page'); // ✅ Sahifalash qo‘shildi

        return view('water_meters.show', compact('waterMeter', 'readings'));
    }

    public function edit(WaterMeter $waterMeter)
    {
        $customers = Customer::all();
        return view('water_meters.edit', compact('waterMeter', 'customers'));
    }

    public function update(Request $request, WaterMeter $waterMeter)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'meter_number' => 'required|numeric|unique:water_meters,meter_number,' . $waterMeter->id,
            'validity_period' => 'required|numeric',
            'last_reading_date' => 'nullable|date',
            'installation_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
        ]);

        $waterMeter->update($request->all());
        return redirect()->route('water_meters.index')->with('success', 'Hisoblagich muvaffaqiyatli yangilandi!');
    }

    public function destroy(WaterMeter $waterMeter)
    {
        $waterMeter->delete();
        return redirect()->route('water_meters.index')->with('success', 'Hisoblagich muvaffaqiyatli o‘chirildi!');
    }
}
