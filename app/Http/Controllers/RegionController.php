<?php
namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        // 'cities' relation (bog'liqlik) nomini to'g'ri yozganingizga ishonch hosil qiling
        // Region modelida public function cities() { return $this->hasMany(City::class); } bo'lishi kerak
        $regions = Region::withCount('cities') // <-- QO'SHILGAN QISM
        ->orderBy('name', 'asc')
            ->get(); // Yoki paginate(15) agar sahifalash kerak bo'lsa

        return view('regions.index', compact('regions'));
    }

    public function create()
    {
        return view('regions.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:regions']);
        Region::create($request->all());

        return redirect()->route('regions.index')->with('success', 'Region qo‘shildi!');
    }

    public function show(Region $region)
    {
        return view('regions.show', compact('region'));
    }

    public function edit(Region $region)
    {
        return view('regions.edit', compact('region'));
    }

    public function update(Request $request, Region $region)
    {
        $request->validate(['name' => 'required|string|unique:regions,name,' . $region->id]);
        $region->update($request->all());

        return redirect()->route('regions.index')->with('success', 'Region yangilandi!');
    }

    public function destroy(Region $region)
    {
        $region->delete();
        return redirect()->route('regions.index')->with('success', 'Region o‘chirildi!');
    }
}
