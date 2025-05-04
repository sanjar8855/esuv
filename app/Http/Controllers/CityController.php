<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Region;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Kerak bo'lmasa ham
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class CityController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Asosiy query - Endi barcha shaharlarni oladi
        $citiesQuery = City::with('region'); // Region ma'lumotini eager load qilamiz

        // Admin bo'lmaganlar uchun asosiy filtr OLIB TASHLANDI
        // if (!$user->hasRole('admin')) {
        //     $citiesQuery->whereHas('neighborhoods.streets.customers', function ($q) use ($user) {
        //         $q->where('is_active', true);
        //         if ($user->company_id) {
        //             $q->where('company_id', $user->company_id);
        //         }
        //     });
        // }

        // Neighborhood'lar sonini hisoblash (BU YERDA FILTR QOLADI)
        $citiesQuery->withCount([
            'neighborhoods as neighborhood_count' => function (Builder $q) use ($user) {
                // Faqat admin bo'lmaganlar uchun filtr qo'llaniladi
                if (!$user->hasRole('admin')) {
                    $q->whereHas('streets.customers', function ($qq) use ($user) {
                        $qq->where('is_active', true);
                        if ($user->company_id) {
                            $qq->where('company_id', $user->company_id);
                        }
                    });
                }
                // Agar admin bo'lsa, filtr qo'llanilmaydi va barcha mahallalar sanaladi
            }
        ]);

        // Shaharlarni sahifalash (pagination)
        // Endi bu so'rov barcha shaharlarni oladi (lekin sahifalab)
        $cities = $citiesQuery->paginate(20);

        // Har bir shahar uchun mijozlar sonini hisoblash (joriy sahifa uchun)
        // Bu qism avvalgidek qoladi, chunki u mantiqan to'g'ri ishlaydi
        // (garchi N+1 muammosi bo'lishi mumkin) va kompaniya filtrini to'g'ri qo'llaydi.
        // `withCount` bilan buni qilish ancha murakkab bo'lishi mumkin edi.
        foreach ($cities as $city) {
            $customerQuery = Customer::where('is_active', true)
                ->whereHas('street.neighborhood', function ($q) use ($city) {
                    // Mijozning ko'chasi shu shaharga tegishli mahallada ekanligini tekshirish
                    $q->where('city_id', $city->id);
                });

            // Faqat admin bo'lmasa company_id bo'yicha filterlash
            if (!$user->hasRole('admin') && $user->company_id) {
                $customerQuery->where('company_id', $user->company_id);
            }

            // Hisoblangan sonni shahar obyektiga qo'shish
            $city->customer_count = $customerQuery->count();
        }

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
