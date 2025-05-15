<?php

namespace App\Http\Controllers;

use App\Models\Neighborhood;
use App\Models\City;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Street;
use App\Models\Invoice; // Invoice modelini qo'shamiz
use App\Models\Payment; // Payment modelini qo'shamiz
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class NeighborhoodController extends Controller
{
    public function index(Request $request)
    {
        // Bu sahifaga faqat admin kiradi deb hisoblaymiz
        // $user = Auth::user();

        if ($request->ajax()) {
            $query = Neighborhood::query()
                ->with([
                    'city.region', // Shahar va viloyat ma'lumotlarini yuklash
                    'company'      // Mahallaning kompaniyasini yuklash
                ])
                ->select('neighborhoods.*'); // Asosiy jadval ustunlarini aniq tanlaymiz

            // 1. Ko'chalar sonini hisoblash (shu mahalladagi, kompaniyasidan qat'i nazar)
            // Chunki mahalla o'zi bir kompaniyaga tegishli (yoki tegishli emas)
            // Ko'chalar ham o'sha mahallaning kompaniyasiga tegishli bo'lishi kerak
            $query->withCount(['streets as street_count' => function(Builder $streetQuery) {
                // Ko'cha mahallaning kompaniyasiga tegishli bo'lishi kerak
                $streetQuery->whereColumn('streets.company_id', 'neighborhoods.company_id');
            }]);

            // 2. Mijozlar sonini hisoblash
            // Shu mahallaga va mahallaning kompaniyasiga tegishli aktiv mijozlar soni
            $query->addSelect(['customer_count_val' => Customer::select(DB::raw('count(*)'))
                ->where('customers.is_active', true)
                ->whereHas('street', function (Builder $streetQuery) {
                    // Mijozning ko'chasi shu mahallaga tegishli ekanligini tekshirish
                    $streetQuery->whereColumn('streets.neighborhood_id', 'neighborhoods.id');
                })
                // Mijozning kompaniyasi mahallaning kompaniyasiga mos kelishini tekshirish
                ->whereColumn('customers.company_id', 'neighborhoods.company_id')
            ]);

            return DataTables::eloquent($query)
                ->addIndexColumn() // "N" ustuni uchun
                ->addColumn('city_full_path', function (Neighborhood $neighborhood) {
                    $pathParts = [];
                    if ($neighborhood->city) {
                        $pathParts[] = e($neighborhood->city->name); // Shahar
                        if ($neighborhood->city->region) {
                            $pathParts[] = e($neighborhood->city->region->name); // Viloyat
                        }
                    }
                    $link = $neighborhood->city ? route('cities.show', $neighborhood->city->id) : '#';
                    $displayText = implode(', ', $pathParts);
                    return $displayText ? '<a href="' . $link . '" class="badge badge-outline text-blue">' . $displayText . '</a>' : '-';
                })
                ->addColumn('company_name_display', function (Neighborhood $neighborhood) {
                    return $neighborhood->company ? e($neighborhood->company->name) : '<span class="text-muted">Belgilanmagan</span>';
                })
                ->editColumn('name', function(Neighborhood $neighborhood) { // Mahalla nomi
                    return e($neighborhood->name);
                })
                ->editColumn('street_count', function(Neighborhood $neighborhood) { // Ko'chalar soni
                    return $neighborhood->street_count ?? 0;
                })
                ->editColumn('customer_count', function(Neighborhood $neighborhood) { // Mijozlar soni
                    return $neighborhood->customer_count_val ?? 0;
                })
                ->addColumn('actions', function (Neighborhood $neighborhood) {
                    $showUrl = route('neighborhoods.show', $neighborhood->id);
                    $editUrl = route('neighborhoods.edit', $neighborhood->id);
                    $deleteUrl = route('neighborhoods.destroy', $neighborhood->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    // Faqat admin ko'rishi va amallarni bajarishi mumkin
                    $buttons = '<a href="'.$showUrl.'" class="btn btn-info btn-sm">Ko‘rish</a> ';
                    $buttons .= '<a href="'.$editUrl.'" class="btn btn-warning btn-sm">Tahrirlash</a> ';
                    $buttons .= '<form action="'.$deleteUrl.'" method="POST" style="display:inline;" onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">'.$csrf.$method.'<button type="submit" class="btn btn-danger btn-sm">O‘chirish</button></form>';
                    return $buttons;
                })
                ->rawColumns(['city_full_path', 'company_name_display', 'actions'])
                ->make(true);
        }

        return view('neighborhoods.index');
    }

    public function create()
    {
        // Bu amalni faqat admin bajarishi mumkin
        // $this->authorize('create', Neighborhood::class);

        $cities = City::with('region', 'company')->orderBy('name')->get(); // Shaharlarni viloyati va kompaniyasi bilan olish
        $companies = Company::orderBy('name')->get(); // Barcha kompaniyalar

        return view('neighborhoods.create', compact('cities', 'companies'));
    }

    public function store(Request $request)
    {
        // $this->authorize('create', Neighborhood::class);

        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'company_id' => 'nullable|exists:companies,id', // Ixtiyoriy
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('neighborhoods')->where(function ($query) use ($request) {
                    return $query->where('city_id', $request->city_id)
                        ->where('company_id', $request->company_id);
                }),
            ],
        ]);

        $city = City::find($validated['city_id']);
        $companyIdToSave = $validated['company_id'];

        // Agar mahalla uchun kompaniya tanlanmagan bo'lsa, lekin shahar kompaniyaga biriktirilgan bo'lsa,
        // mahallani ham o'sha shahar kompaniyasiga biriktiramiz.
        if (empty($companyIdToSave) && $city && $city->company_id) {
            $companyIdToSave = $city->company_id;
            // Validatsiyani qayta tekshirish (agar companyId o'zgargan bo'lsa)
            $nameRule = Rule::unique('neighborhoods')->where(function ($query) use ($request, $companyIdToSave) {
                return $query->where('city_id', $request->city_id)
                    ->where('company_id', $companyIdToSave);
            });
            $request->validate(['name' => ['required', 'string', 'max:255', $nameRule]]);
        }

        try {
            // Neighborhood modelida $fillable ga 'company_id' qo'shilgan bo'lishi kerak
            Neighborhood::create([
                'name' => $validated['name'],
                'city_id' => $validated['city_id'],
                'company_id' => $companyIdToSave, // Yangilangan company_id
            ]);
            return redirect()->route('neighborhoods.index')->with('success', 'Mahalla muvaffaqiyatli qo‘shildi!');
        } catch (\Exception $e) {
            Log::error('Error storing neighborhood: ' . $e->getMessage(), $validated);
            return back()->withInput()->with('error', 'Mahalla qo‘shishda xatolik yuz berdi.');
        }
    }

    public function show(Neighborhood $neighborhood)
    {
        // Agar AJAX so‘rovi bo‘lsa
        if (request()->ajax()) {
            $streetsQuery = $neighborhood->streets()
                // Har bir ko‘cha uchun faqat aktiv mijozlar soni
                ->withCount(['customers as customer_count' => function (\Illuminate\Database\Eloquent\Builder $q) {
                    $q->where('is_active', 1);
                }]);

            return \Yajra\DataTables\Facades\DataTables::eloquent($streetsQuery)
                ->editColumn('name', function (\App\Models\Street $street) {
                    $url = route('streets.show', $street->id);
                    return '<a href="' . $url . '" class="badge badge-outline text-blue">'
                        . e($street->name) . '</a>';
                })
                ->editColumn('customer_count', function (\App\Models\Street $street) {
                    return $street->customer_count;
                })
                ->addColumn('actions', function (\App\Models\Street $street) {
                    $show = route('streets.show', $street->id);
                    $edit = route('streets.edit', $street->id);
                    $del  = route('streets.destroy', $street->id);
                    $csrf   = csrf_field();
                    $method = method_field('DELETE');

                    return
                        '<a href="' . $show . '" class="btn btn-info btn-sm">Ko‘rish</a> ' .
                        '<a href="' . $edit . '" class="btn btn-warning btn-sm">Tahrirlash</a> ' .
                        '<form action="' . $del . '" method="POST" style="display:inline;" '
                        . 'onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">'
                        . $csrf . $method
                        . '<button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>'
                        . '</form>';
                })
                // endi faqat name, customer_count va actions ustunlari raw
                ->rawColumns(['name', 'actions'])
                ->toJson();
        }

        // Oddiy GET – sahifaga ko‘chalar soni (faol mijozlar bilan) uzatiladi
        $streetsCount = $neighborhood->streets()
            ->whereHas('customers', function ($q) {
                $q->where('is_active', 1);
            })
            ->count();

        return view('neighborhoods.show', compact('neighborhood', 'streetsCount'));
    }

    public function edit(Neighborhood $neighborhood) // Route model binding
    {
        // Bu amalni faqat admin bajarishi mumkin
        // $this->authorize('update', $neighborhood);

        $cities = City::with('region', 'company')->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();

        return view('neighborhoods.edit', compact('neighborhood', 'cities', 'companies'));
    }

    public function update(Request $request, Neighborhood $neighborhood)
    {
        // $this->authorize('update', $neighborhood);

        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'company_id' => 'nullable|exists:companies,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('neighborhoods')->where(function ($query) use ($request) {
                    return $query->where('city_id', $request->city_id)
                        ->where('company_id', $request->company_id);
                })->ignore($neighborhood->id), // Joriy mahallani unikallik tekshiruvidan chiqarib tashlash
            ],
        ]);

        $city = City::find($validated['city_id']);
        // Agar formadan company_id kelmasa (bo'sh string), null qilib olamiz
        $companyIdToSave = $validated['company_id'] ?? null;


        // Agar mahalla uchun kompaniya tanlanmagan bo'lsa (yoki "Kompaniya tanlanmagan" tanlangan bo'lsa),
        // va tanlangan shaharning o'z kompaniyasi bo'lsa, mahallani o'sha shahar kompaniyasiga biriktiramiz.
        if (empty($companyIdToSave) && $city && $city->company_id) {
            $companyIdToSave = $city->company_id;
            // Agar company_id o'zgargan bo'lsa, validatsiyani qayta tekshirish kerak bo'lishi mumkin
            // (agar eski company_id va yangi company_id bilan name unikalligi boshqacha bo'lsa)
            // Hozircha bu qismni sodda qoldiramiz, chunki ignore($neighborhood->id) bor
        }
        // Agar mahalla uchun kompaniya tanlangan bo'lsa, o'sha ishlatiladi.
        // Agar shahar ham, mahalla uchun ham kompaniya tanlanmagan bo'lsa, companyIdToSave null bo'ladi.

        $updateData = [
            'name' => $validated['name'],
            'city_id' => $validated['city_id'],
            'company_id' => $companyIdToSave,
        ];

        try {
            $neighborhood->update($updateData);
            return redirect()->route('neighborhoods.index')->with('success', 'Mahalla muvaffaqiyatli yangilandi!');
        } catch (\Exception $e) {
            Log::error('Error updating neighborhood ID ' . $neighborhood->id . ': ' . $e->getMessage(), $validated);
            return back()->withInput()->with('error', 'Mahalla yangilashda xatolik yuz berdi.');
        }
    }

    public function destroy(Neighborhood $neighborhood)
    {
        $neighborhood->delete();
        return redirect()
            ->route('neighborhoods.index')
            ->with('success', 'Mahalla muvaffaqiyatli o‘chirildi!');
    }
}
