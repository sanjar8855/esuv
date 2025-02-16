<?php
namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::paginate(10);
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
