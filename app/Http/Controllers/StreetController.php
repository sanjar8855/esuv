<?php

namespace App\Http\Controllers;

use App\Models\Street;
use App\Models\Neighborhood;
use Illuminate\Http\Request;

class StreetController extends Controller
{
    public function index()
    {
        $streets = Street::with('neighborhood')->paginate(10);
        return view('streets.index', compact('streets'));
    }

    public function create()
    {
        $neighborhoods = Neighborhood::all();
        return view('streets.create', compact('neighborhoods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'neighborhood_id' => 'required|exists:neighborhoods,id',
            'name' => 'required|string|unique:streets',
        ]);

        Street::create($request->all());
        return redirect()->route('streets.index')->with('success', 'Ko‘cha muvaffaqiyatli qo‘shildi!');
    }

    public function show(Street $street)
    {
        return view('streets.show', compact('street'));
    }

    public function edit(Street $street)
    {
        $neighborhoods = Neighborhood::all();
        return view('streets.edit', compact('street', 'neighborhoods'));
    }

    public function update(Request $request, Street $street)
    {
        $request->validate([
            'neighborhood_id' => 'required|exists:neighborhoods,id',
            'name' => 'required|string|unique:streets,name,' . $street->id,
        ]);

        $street->update($request->all());
        return redirect()->route('streets.index')->with('success', 'Ko‘cha muvaffaqiyatli yangilandi!');
    }

    public function destroy(Street $street)
    {
        $street->delete();
        return redirect()->route('streets.index')->with('success', 'Ko‘cha muvaffaqiyatli o‘chirildi!');
    }
}
