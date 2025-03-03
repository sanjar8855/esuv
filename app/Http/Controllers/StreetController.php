<?php

namespace App\Http\Controllers;

use App\Models\Street;
use App\Models\Neighborhood;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StreetController extends Controller
{
    public function index()
    {
        $streets = Street::with('neighborhood')->paginate(15);
        return view('streets.index', compact('streets'));
    }

    public function create()
    {
        $neighborhoods = Neighborhood::orderBy('name', 'asc')->get();
        return view('streets.create', compact('neighborhoods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'neighborhood_id' => 'required|exists:neighborhoods,id',
            'name' => [
                'required',
                'string',
                Rule::unique('streets')->where(function ($query) use ($request) {
                    return $query->where('neighborhood_id', $request->neighborhood_id);
                })
            ],
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
        $neighborhoods = Neighborhood::orderBy('name', 'asc')->get();
        return view('streets.edit', compact('street', 'neighborhoods'));
    }

    public function update(Request $request, Street $street)
    {
        $request->validate([
            'neighborhood_id' => 'required|exists:neighborhoods,id',
            'name' => [
                'required',
                'string',
                Rule::unique('streets')->where(function ($query) use ($request, $street) {
                    return $query->where('neighborhood_id', $request->neighborhood_id);
                })->ignore($street->id) // O‘zidan tashqari boshqalarga unikal bo‘lishi shart
            ],
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
