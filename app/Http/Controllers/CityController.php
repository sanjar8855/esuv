<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Region;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::with('region')->paginate(10);
        return view('cities.index', compact('cities'));
    }

    public function create()
    {
        $regions = Region::all();
        return view('cities.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|unique:cities',
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
        $regions = Region::all();
        return view('cities.edit', compact('city', 'regions'));
    }

    public function update(Request $request, City $city)
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|unique:cities,name,' . $city->id,
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
