<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Region;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CityController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Avval city-larni tayyorlaymiz
        $cities = City::with('region')
            ->whereHas('neighborhoods.streets.customers', function ($q) use ($user) {
                $q->where('is_active', true);
                if (!$user->hasRole('admin') && $user->company_id) {
                    $q->where('company_id', $user->company_id);
                }
            })
            ->withCount([
                'neighborhoods as neighborhood_count' => function ($q) use ($user) {
                    $q->whereHas('streets.customers', function ($qq) use ($user) {
                        $qq->where('is_active', true);
                        if (!$user->hasRole('admin') && $user->company_id) {
                            $qq->where('company_id', $user->company_id);
                        }
                    });
                }
            ])
            ->paginate(15);

        // Endi har bir shahar uchun mijozlar sonini qo‘shamiz
        foreach ($cities as $city) {
            $customerCount = Customer::where('is_active', true)
                ->whereHas('street.neighborhood', function ($q) use ($city) {
                    $q->where('city_id', $city->id);
                })
                ->when(!$user->hasRole('admin') && $user->company_id, function ($q) use ($user) {
                    $q->where('company_id', $user->company_id);
                })
                ->count();

            $city->customer_count = $customerCount;
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
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => [
                'required',
                'string',
                Rule::unique('cities')->where(function ($query) use ($request) {
                    return $query->where('region_id', $request->region_id);
                })
            ],
        ]);

        City::create($request->all());
        return redirect()->route('cities.index')->with('success', 'Shahar qo‘shildi!');
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
