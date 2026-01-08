<?php

namespace App\Http\Controllers;

use App\Models\Neighborhood;
use App\Models\Street;
use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Tariff;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MassEntryController extends Controller
{
    /**
     * Index sahifasi - forma
     */
    public function index()
    {
        $user = auth()->user();

        // Faqat admin va company_owner kirishi mumkin
        if (!$user->hasAnyRole(['admin', 'company_owner'])) {
            abort(403, 'Sizda bu sahifaga kirish uchun ruxsat yo\'q.');
        }

        // MFY larni olish
        $neighborhoods = Neighborhood::query()
            ->when(!$user->hasRole('admin'), function ($q) use ($user) {
                // Admin bo'lmasa, faqat o'z kompaniyasining MFY lari
                $q->whereHas('city.region.companies', function ($query) use ($user) {
                    $query->where('companies.id', $user->company_id);
                });
            })
            ->orderBy('name')
            ->get();

        return view('mass_entry.index', compact('neighborhoods'));
    }

    /**
     * AJAX - MFY ga qarab ko'chalar ro'yxati
     */
    public function getStreets(Request $request)
    {
        $neighborhoodId = $request->input('neighborhood_id');

        $streets = Street::where('neighborhood_id', $neighborhoodId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($streets);
    }

    /**
     * AJAX - Ko'chaga qarab mijozlar ro'yxati
     */
    public function loadCustomers(Request $request)
    {
        $streetId = $request->input('street_id');
        $user = auth()->user();

        // Faqat hisoblagichli mijozlar
        $customers = Customer::where('street_id', $streetId)
            ->where('has_water_meter', true)
            ->where('is_active', true)
            ->when(!$user->hasRole('admin'), function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->with(['waterMeter.readings' => function ($query) {
                // Eng oxirgi ko'rsatkich
                $query->where('confirmed', true)
                    ->latest('reading_date')
                    ->latest('id')
                    ->limit(1);
            }])
            ->orderBy('address')
            ->orderBy('name')
            ->get();

        // Ma'lumotlarni tayyorlash
        $data = $customers->map(function ($customer) {
            $lastReading = $customer->waterMeter && $customer->waterMeter->readings->isNotEmpty()
                ? $customer->waterMeter->readings->first()
                : null;

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'address' => $customer->address ?? '-',
                'account_number' => $customer->account_number,
                'last_reading' => $lastReading ? $lastReading->reading : 0,
                'last_reading_date' => $lastReading ? $lastReading->reading_date->format('d.m.Y') : '-',
                'balance' => $customer->balance,
                'water_meter_id' => $customer->waterMeter ? $customer->waterMeter->id : null,
            ];
        });

        return response()->json($data);
    }

    /**
     * Saqlash - ko'rsatkich va to'lov
     */
    public function save(Request $request)
    {
        $user = auth()->user();

        // Validatsiya
        $request->validate([
            'entries' => 'required|array',
            'entries.*.customer_id' => 'required|exists:customers,id',
            'entries.*.water_meter_id' => 'required|exists:water_meters,id',
            'entries.*.new_reading' => 'nullable|numeric|min:0',
            'entries.*.payment_amount' => 'nullable|numeric|min:0',
        ]);

        $successCount = 0;
        $errors = [];
        $streetId = null;
        $neighborhoodId = null;

        DB::beginTransaction();

        try {
            foreach ($request->entries as $entry) {
                try {
                    $customer = Customer::with('waterMeter')->findOrFail($entry['customer_id']);

                    // Street va neighborhood ni eslab qolish
                    if (!$streetId) {
                        $streetId = $customer->street_id;
                        $street = Street::find($streetId);
                        $neighborhoodId = $street ? $street->neighborhood_id : null;
                    }

                    // Ruxsat tekshiruvi
                    if (!$user->hasRole('admin') && $customer->company_id != $user->company_id) {
                        throw new \Exception("Sizda bu mijozni o'zgartirish huquqi yo'q.");
                    }

                    $hasChanges = false;

                    // 1. Ko'rsatkich kiritish
                    if (!empty($entry['new_reading'])) {
                        $this->createMeterReading($customer, $entry);
                        $hasChanges = true;
                    }

                    // 2. To'lov kiritish
                    if (!empty($entry['payment_amount'])) {
                        $this->createPayment($customer, $entry);
                        $hasChanges = true;
                    }

                    if ($hasChanges) {
                        $successCount++;
                    }

                } catch (\Exception $e) {
                    $errors[] = "Mijoz #{$entry['customer_id']}: " . $e->getMessage();
                    Log::error('Mass entry error', [
                        'customer_id' => $entry['customer_id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            if ($successCount > 0) {
                return redirect()->route('mass_entry.index')
                    ->with('success', "{$successCount} ta ma'lumot muvaffaqiyatli saqlandi!")
                    ->with('selected_neighborhood', $neighborhoodId)
                    ->with('selected_street', $streetId);
            } else {
                return redirect()->back()
                    ->withErrors(['general' => 'Hech qanday ma\'lumot saqlanmadi.'])
                    ->withInput();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mass entry transaction error', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->withErrors(['general' => 'Xatolik yuz berdi: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Ko'rsatkich yaratish
     */
    private function createMeterReading(Customer $customer, array $entry)
    {
        $waterMeter = $customer->waterMeter;

        if (!$waterMeter) {
            throw new \Exception("Hisoblagich topilmadi");
        }

        // Oxirgi ko'rsatkichni olish
        $lastReading = MeterReading::where('water_meter_id', $waterMeter->id)
            ->where('confirmed', true)
            ->latest('reading_date')
            ->latest('id')
            ->first();

        $newReading = (float) $entry['new_reading'];

        // Validatsiya: yangi ko'rsatkich oxirgisidan katta bo'lishi kerak
        if ($lastReading && $newReading <= $lastReading->reading) {
            throw new \Exception("Yangi ko'rsatkich oxirgisidan ({$lastReading->reading}) katta bo'lishi kerak");
        }

        // Ko'rsatkichni yaratish
        MeterReading::create([
            'water_meter_id' => $waterMeter->id,
            'reading' => $newReading,
            'reading_date' => now()->toDateString(),
            'confirmed' => true,
        ]);

        // Invoice yaratish (agar consumption bo'lsa)
        if ($lastReading) {
            $consumption = $newReading - $lastReading->reading;

            if ($consumption > 0) {
                // Aktiv tarifni olish
                $tariff = Tariff::where('company_id', $customer->company_id)
                    ->where('is_active', true)
                    ->latest('valid_from')
                    ->first();

                if ($tariff) {
                    Invoice::create([
                        'customer_id' => $customer->id,
                        'tariff_id' => $tariff->id,
                        'billing_period' => now()->format('Y-m'),
                        'amount_due' => $consumption * $tariff->price_per_m3,
                        'due_date' => now()->endOfMonth(),
                        'status' => 'pending',
                    ]);
                }
            }
        }
    }

    /**
     * To'lov yaratish
     */
    private function createPayment(Customer $customer, array $entry)
    {
        $amount = (float) $entry['payment_amount'];

        if ($amount <= 0) {
            return;
        }

        Payment::create([
            'customer_id' => $customer->id,
            'invoice_id' => null, // Umumiy to'lov
            'amount' => $amount,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash', // Default
            'confirmed' => true,
        ]);
    }
}
