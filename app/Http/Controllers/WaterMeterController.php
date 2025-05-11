<?php

namespace App\Http\Controllers;

use App\Models\WaterMeter;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Carbon;

class WaterMeterController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ----- AJAX so'rovi uchun -----
        if (request()->ajax()) {
            // Asosiy query
            $query = WaterMeter::with([
                'customer', // Mijoz ma'lumotlari kerak
                'readings' => function ($q) { // Oxirgi tasdiqlangan ko'rsatkichni olish uchun
                    $q->where('confirmed', 1)->latest('reading_date')->latest('id'); // Eng oxirgisini oladi
                }
            ])->select('water_meters.*'); // Boshqa jadvallar bilan join qilganda ustun nomlari chalkashmasligi uchun

            // Admin bo'lmasa kompaniya bo'yicha filtr
            if (!$user->hasRole('admin') && $user->company) {
                $query->whereHas('customer', function ($q) use ($user) {
                    $q->where('company_id', $user->company_id);
                });
            }

            // DataTables'ga uzatish
            return DataTables::eloquent($query)
                ->addIndexColumn() // "N" ustuni uchun (boshida 1 dan boshlanadi)
                ->addColumn('customer_link', function (WaterMeter $waterMeter) { // Mijoz linki
                    if ($waterMeter->customer) {
                        // e() funksiyasi XSS hujumlaridan himoyalaydi
                        return '<a href="'.route('customers.show', $waterMeter->customer->id).'" class="badge badge-outline text-blue">'.e($waterMeter->customer->name).'</a>';
                    }
                    return '<span class="badge badge-outline text-danger">Mijoz yo‘q</span>';
                })
                ->editColumn('meter_number', function (WaterMeter $waterMeter) { // Raqamni formatlash
                    return $waterMeter->meter_number;
                })
                // Sanalarni formatlash (agar kerak bo'lsa)
//                 ->editColumn('installation_date', fn($wm) => $wm->installation_date ? \Carbon\Carbon::parse($wm->installation_date)->format('d.m.Y') : 'Noma’lum')
//                 ->editColumn('expiration_date', fn($wm) => $wm->expiration_date ? \Carbon\Carbon::parse($wm->expiration_date)->format('d.m.Y') : 'Noma’lum')
                ->addColumn('last_reading_value', function (WaterMeter $waterMeter) { // Oxirgi ko'rsatkich qiymati
                    $reading = $waterMeter->readings->first()?->reading; // Eager loading qilingan 'readings' dan olamiz
                    return $reading !== null ? number_format($reading, 0, '.', ' ') : '---';
                })
                ->addColumn('actions', function (WaterMeter $waterMeter) { // Amallar
                    $showUrl = route('water_meters.show', $waterMeter->id);
                    $editUrl = route('water_meters.edit', $waterMeter->id);
                    $deleteUrl = route('water_meters.destroy', $waterMeter->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    // Heredoc sintaksisi HTMLni qulayroq yozishga yordam beradi
                    return <<<HTML
                        <a href="{$showUrl}" class="btn btn-info btn-sm">Ko‘rish</a>
                        <a href="{$editUrl}" class="btn btn-warning btn-sm">Tahrirlash</a>
                        <form action="{$deleteUrl}" method="POST" style="display:inline;" onsubmit="return confirm('Haqiqatan ham o‘chirmoqchimisiz?');">
                            {$csrf}
                            {$method}
                            <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                        </form>
                    HTML;
                })
                ->filterColumn('customer_link', function($query, $keyword) { // Mijoz ismi bo'yicha qidirish
                    $query->whereHas('customer', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('customer_link', function ($query, $order) { // Mijoz ismi bo'yicha saralash
                    // To'g'ri saralash uchun join qilish kerak
                    $query->join('customers', 'water_meters.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $order)
                        ->select('water_meters.*'); // `select` ni qayta qo'yish kerak, join'dan keyin
                })
                ->rawColumns(['customer_link', 'actions']) // Bu ustunlarda HTML bor
                ->make(true); // JSON javobini yaratish va qaytarish
        }

        // ----- Oddiy GET so'rov uchun (AJAX emas) -----
        // Faqat umumiy sonni hisoblab, view'ni qaytaramiz
        $waterMetersQuery = WaterMeter::query();
        if (!$user->hasRole('admin') && $user->company) {
            $waterMetersQuery->whereHas('customer', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            });
        }
        $waterMetersCount = $waterMetersQuery->count();

        return view('water_meters.index', compact('waterMetersCount'));
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
