<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Region;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\LengthAwarePaginator;

class CityController extends Controller
{

    public function index()
    {
        $cities = City::with('region')
            ->orderBy('name', 'asc')
            ->paginate(20);

        foreach ($cities as $city) {
            $city->neighborhood_count = $city->neighborhoods()->count();

            $city->customer_count = Customer::where('is_active', true)
                ->whereHas('street.neighborhood', function ($q) use ($city) {
                    $q->where('city_id', $city->id);
                })
                ->count();
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
            City::create($validated);
            return redirect()->route('cities.index')->with('success', 'Shahar qo\'shildi!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Xatolik yuz berdi: ' . $e->getMessage());
        }
    }

    public function show(City $city)
    {
        $neighborhoods = $city->neighborhoods()
            ->withCount(['streets as street_count'])
            ->get();

        foreach ($neighborhoods as $neighborhood) {
            $neighborhood->customer_count = Customer::where('is_active', true)
                ->whereHas('street', function ($q) use ($neighborhood) {
                    $q->where('neighborhood_id', $neighborhood->id);
                })
                ->count();
        }

        $page    = request()->get('page', 1);
        $perPage = 15;
        $paginated = new LengthAwarePaginator(
            $neighborhoods->forPage($page, $perPage),
            $neighborhoods->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('cities.show', [
            'city'         => $city,
            'neighborhoods'=> $paginated,
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
