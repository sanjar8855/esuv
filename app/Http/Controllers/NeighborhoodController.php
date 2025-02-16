<?php

namespace App\Http\Controllers;

use App\Models\Neighborhood;
use App\Models\City;
use Illuminate\Http\Request;

class NeighborhoodController extends Controller
{
    public function index()
    {
        $neighborhoods = Neighborhood::with('city')->paginate(10);
        return view('neighborhoods.index', compact('neighborhoods'));
    }

    public function create()
    {
        $cities = City::all();
        return view('neighborhoods.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|unique:neighborhoods',
        ]);

        Neighborhood::create($request->all());
        return redirect()->route('neighborhoods.index')->with('success', 'Mahalla muvaffaqiyatli qo‘shildi!');
    }

    public function show(Neighborhood $neighborhood)
    {
        return view('neighborhoods.show', compact('neighborhood'));
    }

    public function edit(Neighborhood $neighborhood)
    {
        $cities = City::all();
        return view('neighborhoods.edit', compact('neighborhood', 'cities'));
    }

    public function update(Request $request, Neighborhood $neighborhood)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|unique:neighborhoods,name,' . $neighborhood->id,
        ]);

        $neighborhood->update($request->all());
        return redirect()->route('neighborhoods.index')->with('success', 'Mahalla muvaffaqiyatli yangilandi!');
    }

    public function destroy(Neighborhood $neighborhood)
    {
        $neighborhood->delete();
        return redirect()->route('neighborhoods.index')->with('success', 'Mahalla muvaffaqiyatli o‘chirildi!');
    }
}
