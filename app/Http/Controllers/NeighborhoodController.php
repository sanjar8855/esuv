<?php

namespace App\Http\Controllers;

use App\Models\Neighborhood;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NeighborhoodController extends Controller
{
    public function index()
    {
        $neighborhoods = Neighborhood::orderBy('name', 'asc')->with('city')->paginate(15);
        return view('neighborhoods.index', compact('neighborhoods'));
    }

    public function create()
    {
        $cities = City::orderBy('name', 'asc')->get();
        return view('neighborhoods.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => [
                'required',
                'string',
                Rule::unique('neighborhoods')->where(function ($query) use ($request) {
                    return $query->where('city_id', $request->city_id);
                })
            ],
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
        $cities = City::orderBy('name', 'asc')->get();
        return view('neighborhoods.edit', compact('neighborhood', 'cities'));
    }

    public function update(Request $request, Neighborhood $neighborhood)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => [
                'required',
                'string',
                Rule::unique('neighborhoods')->where(function ($query) use ($request, $neighborhood) {
                    return $query->where('city_id', $request->city_id);
                })->ignore($neighborhood->id) // O‘zidan tashqari boshqalarga unikal bo‘lishi shart
            ],
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
