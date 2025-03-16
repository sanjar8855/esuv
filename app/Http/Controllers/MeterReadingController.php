<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Tariff;
use App\Models\WaterMeter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MeterReadingController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // **📌 Asosiy query**
        $meterReadingsQuery = MeterReading::with([
            'waterMeter.customer'
        ])->orderBy('id', 'desc');

        // **📌 Admin bo‘lmasa, faqat o‘z kompaniyasiga tegishli o‘qishlarni olish**
        if (!$user->hasRole('admin') && $user->company) {
            $meterReadingsQuery->whereHas('waterMeter.customer', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            });
        }

        // **📌 Jami o‘qishlar sonini olish**
        $meterReadingsCount = (clone $meterReadingsQuery)->count();

        // **📌 Sahifalash (pagination)**
        $meterReadings = $meterReadingsQuery->paginate(20)->withQueryString();

        return view('meter_readings.index', compact('meterReadings', 'meterReadingsCount'));
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
            'reading_date' => 'required|date',
            'confirmed' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096'
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('meter_readings', 'public');
        }

        // **Oxirgi tasdiqlangan o'qishni olish**
        $lastConfirmedReading = MeterReading::where('water_meter_id', $validated['water_meter_id'])
            ->where('confirmed', true)
            ->orderBy('reading_date', 'desc')
            ->first();

        if ($lastConfirmedReading && $validated['reading'] <= $lastConfirmedReading->reading) {
            return redirect()->back()->withErrors([
                'reading' => 'Yangi ko‘rsatkich (' . $validated['reading'] . ') oxirgi tasdiqlangan (' . $lastConfirmedReading->reading . ') dan katta bo‘lishi kerak.'
            ])->withInput();
        }

        // **Ko'rsatkichni saqlash**
        $meterReading = MeterReading::create($validated);

        return redirect()->route('meter_readings.index')
            ->with('success', 'Hisoblagich o‘qilishi muvaffaqiyatli qo‘shildi!');
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
        $validated = $request->validate([
            'water_meter_id' => 'required|exists:water_meters,id',
            'reading' => 'required|numeric|min:0',
            'photo_url' => 'nullable|string',
            'reading_date' => 'required|date',
            'confirmed' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096'
        ]);

        if ($request->hasFile('photo')) {
            // Eski rasmni o‘chirish
            if ($meterReading->photo) {
                Storage::disk('public')->delete($meterReading->photo);
            }

            // Yangi rasmni yuklash
            $validated['photo'] = $request->file('photo')->store('meter_readings', 'public');
        }

        // **Oldingi tasdiqlash holatini saqlab qolamiz**
        $wasUnconfirmed = !$meterReading->confirmed;

        // **Yangilash**
        $meterReading->update($validated);

        // **Agar oldin tasdiqlanmagan bo‘lib, hozir tasdiqlangan bo‘lsa, invoice yaratish**
        if ($wasUnconfirmed && $meterReading->confirmed) {
            return $this->confirm($meterReading->id);
        }

        return redirect()->route('meter_readings.index')->with('success', 'Hisoblagich o‘qilishi muvaffaqiyatli yangilandi!');
    }

    public function destroy(MeterReading $meterReading)
    {
        if ($meterReading->photo) {
            Storage::disk('public')->delete($meterReading->photo);
        }

        $meterReading->delete();
        return redirect()->route('meter_readings.index')->with('success', 'Hisoblagich o‘qilishi muvaffaqiyatli o‘chirildi!');
    }

    public function confirm($id)
    {
        $meterReading = MeterReading::findOrFail($id);

        // Agar allaqachon tasdiqlangan bo‘lsa, hech narsa qilmasin
        if ($meterReading->confirmed) {
            return back()->with('info', 'Ko‘rsatkich allaqachon tasdiqlangan.');
        }

        // **Tasdiqlash**
        $meterReading->update(['confirmed' => true]);

        // **Hisob yaratish jarayoni**
        $customer = $meterReading->waterMeter->customer;
        $tariff = Tariff::where('company_id', $customer->company_id)
            ->where('is_active', true)
            ->latest()
            ->first();

        if ($tariff) {
            // **Oxirgi tasdiqlangan ko‘rsatkichni olish**
            $previousConfirmedReading = MeterReading::where('water_meter_id', $meterReading->water_meter_id)
                ->where('reading_date', '<', $meterReading->reading_date)
                ->where('confirmed', true)
                ->orderBy('reading_date', 'desc')
                ->first();

            if ($previousConfirmedReading) {
                // **Suv iste'moli farqini hisoblash**
                $consumption = $meterReading->reading - $previousConfirmedReading->reading;
                $amount_due = $consumption * $tariff->price_per_m3;

                // **Yangi invoice yaratish**
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

        return back()->with('success', 'Ko‘rsatkich tasdiqlandi va invoice yaratildi!');
    }
}
