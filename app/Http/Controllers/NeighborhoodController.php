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
        $user = auth()->user();

        // OZGARISH: Admin uchun barcha mahallalarni ko'rsatish
        $query = Neighborhood::with('city');

        // OZGARISH: Admin emas bo'lsa, filter qo'llaymiz
        if (!$user->hasRole('admin')) {
            $query->whereHas('streets.customers', function ($q) use ($user) {
                $q->where('is_active', 1);
                if ($user->company_id) {
                    $q->where('company_id', $user->company_id);
                }
            });
        }

        // Ko'chalar sonini qo'shamiz
        $query->withCount([
            'streets as street_count' => function ($q) use ($user) {
                // OZGARISH: Admin uchun barcha ko'chalarni sanash
                if (!$user->hasRole('admin')) {
                    $q->whereHas('customers', function ($qq) use ($user) {
                        $qq->where('is_active', 1);
                        if ($user->company_id) {
                            $qq->where('company_id', $user->company_id);
                        }
                    });
                }
            }
        ]);

        $neighborhoods = $query->paginate(15);

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
        $user = auth()->user();

        $streets = $neighborhood->streets()
            ->whereHas('customers', function ($q) use ($user) {
                $q->where('is_active', 1);
                if (!$user->hasRole('admin') && $user->company_id) {
                    $q->where('company_id', $user->company_id);
                }
            })
            ->withCount(['customers as customer_count' => function ($q) use ($user) {
                $q->where('is_active', 1);
                if (!$user->hasRole('admin') && $user->company_id) {
                    $q->where('company_id', $user->company_id);
                }
            }])
            ->paginate(15);

        return view('neighborhoods.show', compact('neighborhood', 'streets'));
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
