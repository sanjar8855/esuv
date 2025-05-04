<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Region;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Kerak bo'lmasa ham
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CityController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. Asosiy query - Faqat shaharlarni olamiz (sahifalab)
        $citiesQuery = City::with('region')->orderBy('name', 'asc');
        $cities = $citiesQuery->paginate(20); // Masalan, 20 ta

        // 2. Har bir shahar uchun alohida hisob-kitob (N+1 usuli)
        foreach ($cities as $city) {

            // a) Mahallalar sonini hisoblash (alohida so'rov)
            try {
                // City modelida 'neighborhoods' relation mavjud deb hisoblaymiz
                $city->neighborhood_count = $city->neighborhoods()->count();
            } catch (\Exception $e) {
                // Agar relation topilmasa yoki xatolik bo'lsa
                Log::error("Error counting neighborhoods for city ID {$city->id}: " . $e->getMessage());
                $city->neighborhood_count = 0; // Xatolik bo'lsa 0 ko'rsatamiz
            }

            // b) Mijozlar sonini hisoblash (alohida so'rov)
            // Admin bo'lmagan va kompaniyasi YO'Qlar uchun 0 qiymatini belgilash
            if (!$user->hasRole('admin') && !$user->company_id) {
                $city->customer_count = 0;
            } else {
                // Qolgan hollar uchun (Admin yoki Kompaniyasi BOR xodim)
                $customerQuery = Customer::where('is_active', true)
                    ->whereHas('street.neighborhood', function ($q) use ($city) {
                        $q->where('city_id', $city->id);
                    });

                // Faqat admin bo'lmagan VA kompaniyasi BOR xodim uchun filtr
                if (!$user->hasRole('admin') && $user->company_id) {
                    $customerQuery->where('company_id', $user->company_id);
                }
                // Admin uchun filtr qo'llanmaydi

                $city->customer_count = $customerQuery->count();
            }
        } // foreach tugadi

        // Natijani view'ga uzatish
        // $cities kolleksiyasi endi har bir shahar uchun hisoblangan
        // neighborhood_count va customer_count atributlariga ega
        return view('cities.index', compact('cities'));
    }

    public function create()
    {
        $regions = Region::orderBy('name', 'asc')->get();
        return view('cities.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => [
                'required',
                'string',
                Rule::unique('cities')->where(function ($query) use ($request) {
                    return $query->where('region_id', $request->region_id);
                })
            ],
        ]);

        try {
            // $request->all() o'rniga faqat validatsiya qilingan ma'lumotlarni ishlatamiz
            City::create($validated);
            return redirect()->route('cities.index')->with('success', 'Shahar qo\'shildi!');
        } catch (\Exception $e) {
            // Xatolik bo'lsa uni qaytaramiz
            return back()->withInput()->with('error', 'Xatolik yuz berdi: ' . $e->getMessage());
        }
    }

    public function show(City $city)
    {
        $user = auth()->user();

        // Faqat kerakli neighborhoodlarni olish
        $neighborhoods = $city->neighborhoods()
            ->whereHas('streets.customers', function ($q) use ($user) {
                $q->where('is_active', 1);
                if (!$user->hasRole('admin') && $user->company_id) {
                    $q->where('company_id', $user->company_id);
                }
            })
            ->withCount([
                'streets as street_count' => function ($q) use ($user) {
                    $q->whereHas('customers', function ($qq) use ($user) {
                        $qq->where('is_active', 1);
                        if (!$user->hasRole('admin') && $user->company_id) {
                            $qq->where('company_id', $user->company_id);
                        }
                    });
                }
            ])
            ->get(); // paginate emas, get() qilamiz

        // Endi har bir mahalla uchun mijozlar sonini hisoblaymiz
        foreach ($neighborhoods as $neighborhood) {
            $customerCount = \App\Models\Customer::where('is_active', 1)
                ->whereHas('street', function ($q) use ($neighborhood) {
                    $q->where('neighborhood_id', $neighborhood->id);
                })
                ->when(!$user->hasRole('admin') && $user->company_id, function ($q) use ($user) {
                    $q->where('company_id', $user->company_id);
                })
                ->count();

            $neighborhood->customer_count = $customerCount;
        }

        // Qo‘lda paginate qilish
        $page = request()->get('page', 1);
        $perPage = 15;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $neighborhoods->forPage($page, $perPage),
            $neighborhoods->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('cities.show', [
            'city' => $city,
            'neighborhoods' => $paginated,
        ]);
    }

    public function edit(City $city)
    {
        $regions = Region::orderBy('name', 'asc')->get();
        return view('cities.edit', compact('city', 'regions'));
    }

    public function update(Request $request, City $city)
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => [
                'required',
                'string',
                Rule::unique('cities')->where(function ($query) use ($request, $city) {
                    return $query->where('region_id', $request->region_id);
                })->ignore($city->id) // O‘zidan tashqari boshqalarga unikal bo‘lishi shart
            ],
        ]);

        $city->update($request->all());
        return redirect()->route('cities.index')->with('success', 'Shahar yangilandi!');
    }

    public function destroy(City $city)
    {
        $city->delete();
        return redirect()->route('cities.index')->with('success', 'Shahar o‘chirildi!');
    }
}
