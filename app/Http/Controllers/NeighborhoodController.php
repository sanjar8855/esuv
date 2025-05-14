<?php

namespace App\Http\Controllers;

use App\Models\Neighborhood;
use App\Models\City;
use App\Models\Customer;
use App\Models\Street;
use App\Models\Invoice; // Invoice modelini qo'shamiz
use App\Models\Payment; // Payment modelini qo'shamiz
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class NeighborhoodController extends Controller
{
    public function index()
    {
        // Diagnostika uchun log
        Log::info('Neighborhood Index accessed');

        if (request()->ajax()) {
            $query = Neighborhood::query()
                ->leftJoin('cities', 'neighborhoods.city_id', '=', 'cities.id')
                ->select(
                    'neighborhoods.*',
                    'cities.name as city_name',
                    'cities.id as city_id_for_route'
                )
                // Ko'chalar soni
                ->withCount('streets as street_count')
                // Jami qarzdorlikni hisoblash (faol mijozlarning manfiy balanslari)
                ->addSelect(['total_customer_count' => Customer::selectRaw('COUNT(*)')
                    ->where('customers.is_active', 1)
                    ->whereHas('street', function (Builder $q) {
                        $q->whereColumn('streets.neighborhood_id', 'neighborhoods.id');
                    })
                ]);

            return DataTables::eloquent($query)
                ->addColumn('city', function (Neighborhood $n) {
                    $url = route('cities.show', $n->city_id_for_route);
                    return '<a href="' . $url . '" class="badge badge-outline text-blue">'
                        . e($n->city_name) . '</a>';
                })
                ->editColumn('street_count', fn(Neighborhood $n) => $n->street_count ?: 0)
                ->addColumn('total_customers', function (Neighborhood $n) {
                    return $n->total_customer_count;
                })
                ->addColumn('actions', function (Neighborhood $n) {
                    $show = route('neighborhoods.show', $n->id);
                    $edit = route('neighborhoods.edit', $n->id);
                    $del  = route('neighborhoods.destroy', $n->id);
                    $csrf   = csrf_field();
                    $method = method_field('DELETE');

                    return
                        '<a href="' . $show . '" class="btn btn-info btn-sm">Ko‘rish</a> ' .
                        '<a href="' . $edit . '" class="btn btn-warning btn-sm">Tahrirlash</a> ' .
                        '<form action="' . $del . '" method="POST" style="display:inline;" '
                        . 'onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">'
                        . $csrf . $method
                        . '<button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>'
                        . '</form>';
                })
                ->rawColumns(['city', 'actions'])
                ->toJson();
        }

        return view('neighborhoods.index');
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
            'name'    => [
                'required',
                'string',
                Rule::unique('neighborhoods')->where(fn($q) =>
                $q->where('city_id', $request->city_id)
                )
            ],
        ]);

        Neighborhood::create($request->all());

        return redirect()
            ->route('neighborhoods.index')
            ->with('success', 'Mahalla muvaffaqiyatli qo‘shildi!');
    }

    public function show(Neighborhood $neighborhood)
    {
        // Agar AJAX so‘rovi bo‘lsa
        if (request()->ajax()) {
            $streetsQuery = $neighborhood->streets()
                // Har bir ko‘cha uchun faqat aktiv mijozlar soni
                ->withCount(['customers as customer_count' => function (\Illuminate\Database\Eloquent\Builder $q) {
                    $q->where('is_active', 1);
                }]);

            return \Yajra\DataTables\Facades\DataTables::eloquent($streetsQuery)
                ->editColumn('name', function (\App\Models\Street $street) {
                    $url = route('streets.show', $street->id);
                    return '<a href="' . $url . '" class="badge badge-outline text-blue">'
                        . e($street->name) . '</a>';
                })
                ->editColumn('customer_count', function (\App\Models\Street $street) {
                    return $street->customer_count;
                })
                ->addColumn('actions', function (\App\Models\Street $street) {
                    $show = route('streets.show', $street->id);
                    $edit = route('streets.edit', $street->id);
                    $del  = route('streets.destroy', $street->id);
                    $csrf   = csrf_field();
                    $method = method_field('DELETE');

                    return
                        '<a href="' . $show . '" class="btn btn-info btn-sm">Ko‘rish</a> ' .
                        '<a href="' . $edit . '" class="btn btn-warning btn-sm">Tahrirlash</a> ' .
                        '<form action="' . $del . '" method="POST" style="display:inline;" '
                        . 'onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">'
                        . $csrf . $method
                        . '<button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>'
                        . '</form>';
                })
                // endi faqat name, customer_count va actions ustunlari raw
                ->rawColumns(['name', 'actions'])
                ->toJson();
        }

        // Oddiy GET – sahifaga ko‘chalar soni (faol mijozlar bilan) uzatiladi
        $streetsCount = $neighborhood->streets()
            ->whereHas('customers', function ($q) {
                $q->where('is_active', 1);
            })
            ->count();

        return view('neighborhoods.show', compact('neighborhood', 'streetsCount'));
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
            'name'    => [
                'required',
                'string',
                Rule::unique('neighborhoods')->where(fn($q) =>
                $q->where('city_id', $request->city_id)
                )->ignore($neighborhood->id)
            ],
        ]);

        $neighborhood->update($request->all());

        return redirect()
            ->route('neighborhoods.index')
            ->with('success', 'Mahalla muvaffaqiyatli yangilandi!');
    }

    public function destroy(Neighborhood $neighborhood)
    {
        $neighborhood->delete();
        return redirect()
            ->route('neighborhoods.index')
            ->with('success', 'Mahalla muvaffaqiyatli o‘chirildi!');
    }
}
