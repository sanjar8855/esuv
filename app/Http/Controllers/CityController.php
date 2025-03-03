<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::with('region')->orderBy('name', 'asc')->paginate(15);
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
        return view('cities.show', compact('city'));
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
