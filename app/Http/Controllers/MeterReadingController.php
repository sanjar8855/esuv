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
        $meterReadings = MeterReading::with('waterMeter.customer')->orderBy('id','desc')->paginate(10);
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
            'reading_date' => 'required|date',
            'confirmed' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096'
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('meter_readings', 'public');
        }

        // **1️⃣ Oxirgi kiritilgan istalgan o'qish (tasdiqlangan yoki tasdiqlanmagan)**
        $lastReading = MeterReading::where('water_meter_id', $validated['water_meter_id'])
            ->orderByRaw('reading_date DESC, reading DESC') // Oxirgi sana va eng katta qiymat
            ->where('confirmed',true)
            ->first();

        if ($lastReading && $validated['reading'] <= $lastReading->reading) {
            return redirect()->back()->withErrors([
                'reading' => 'Yangi hisoblagich ko‘rsatkichi (' . $validated['reading'] . ') oxirgi kiritilgan (' . $lastReading->reading . ') dan katta bo‘lishi kerak.'
            ])->withInput();
        }

        // **2️⃣ Oxirgi tasdiqlangan o'qishni tekshirish**
        $lastConfirmedReading = MeterReading::where('water_meter_id', $validated['water_meter_id'])
            ->where('confirmed', true)
            ->orderBy('reading_date', 'desc')
            ->first();

        if ($lastConfirmedReading && $validated['reading'] <= $lastConfirmedReading->reading) {
            return redirect()->back()->withErrors([
                'reading' => 'Hisoblagich raqami (' . $validated['reading'] . ') tasdiqlangan oxirgi o‘qish (' . $lastConfirmedReading->reading . ') dan katta bo‘lishi kerak.'
            ])->withInput();
        }

        // **3️⃣ Ko'rsatkichni saqlash**
        $meterReading = MeterReading::create($validated);

        // **4️⃣ To‘lovni hisoblash va invoice yaratish**
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

        $previousUrl = url()->previous(); // Oldingi sahifani olamiz

        if (strpos($previousUrl, route('customers.show', $customer->id)) !== false) {
            return redirect()->route('customers.show', $customer->id)
                ->with('success', 'Hisoblagich o‘qilishi muvaffaqiyatli qo‘shildi!');
        } elseif (strpos($previousUrl, route('meter_readings.create')) !== false) {
            return redirect()->route('meter_readings.index')
                ->with('success', 'Hisoblagich o‘qilishi muvaffaqiyatli qo‘shildi!');
        } else {
            return redirect()->route('water_meters.show', $meterReading->water_meter_id)
                ->with('success', 'Hisoblagich o‘qilishi muvaffaqiyatli qo‘shildi!');
        }
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

        // Oldingi tasdiqlash holatini saqlab qolamiz
        $wasUnconfirmed = !$meterReading->confirmed;

        // Yangilash
        $meterReading->update($validated);

        // Agar oldin tasdiqlanmagan bo‘lib, hozir tasdiqlangan bo‘lsa
        if ($wasUnconfirmed && $meterReading->confirmed) {
            $customer = $meterReading->waterMeter->customer;
            $tariff = Tariff::where('company_id', $customer->company_id)
                ->where('is_active', true)
                ->latest()
                ->first();

            if ($tariff) {
                $previousReading = MeterReading::where('water_meter_id', $meterReading->water_meter_id)
                    ->where('reading_date', '<', $meterReading->reading_date)
                    ->where('confirmed', true)
                    ->orderBy('reading_date', 'desc')
                    ->first();

                if ($previousReading) {
                    $consumption = $meterReading->reading - $previousReading->reading;
                    $amount_due = $consumption * $tariff->price_per_m3;

                    // Yangi invoice yaratish
                    Invoice::create([
                        'customer_id'    => $customer->id,
                        'tariff_id'      => $tariff->id,
                        'billing_period' => now()->format('Y-m'),
                        'amount_due'     => $amount_due,
                        'due_date'       => now()->endOfMonth(),
                        'status'         => 'pending',
                    ]);

                    // 📩 Telegram xabari yuborish
                    $this->sendTelegramNotification($customer, $amount_due, $meterReading->reading);
                }
            }
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
        $reading = MeterReading::findOrFail($id);
        $reading->update(['confirmed' => true]);

        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'html' => view('customers.partials.reading-status', compact('reading'))->render()
            ]);
        }

        return back()->with('success', 'Ko‘rsatkich tasdiqlandi!');
    }
}
