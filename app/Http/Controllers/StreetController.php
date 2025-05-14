<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Street;
use App\Models\Neighborhood;
use App\Models\Invoice;

// Invoice modelini qo'shamiz
use App\Models\Payment;

// Payment modelini qo'shamiz
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

// Builder'ni qo'shamiz
use Illuminate\Support\Facades\DB;

class StreetController extends Controller
{
    public function index(Request $request)
    {
        // AJAX so‘rovi bo‘lsa…
        if ($request->ajax()) {
            // 1) Har bir ko‘cha uchun faqat aktiv mijozlar sonini yuklaymiz
            $streets = Street::with('neighborhood')
                ->withCount(['customers as customer_count' => function ($q) {
                    $q->where('is_active', true);
                }])
                ->get();

            // 2) DataTables uchun collection qaytaramiz
            return DataTables::collection($streets)
                ->addColumn('neighborhood', function ($street) {
                    $url = route('neighborhoods.show', $street->neighborhood->id);
                    return '<a href="' . $url . '" class="badge badge-outline text-blue">'
                        . e($street->neighborhood->name) . '</a>';
                })
                ->editColumn('customer_count', function ($street) {
                    return $street->customer_count;
                })
                ->addColumn('actions', function ($street) {
                    $show = route('streets.show', $street->id);
                    $edit = route('streets.edit', $street->id);
                    $del  = route('streets.destroy', $street->id);
                    $csrf   = csrf_field();
                    $method = method_field('DELETE');

                    $btns  = '<a href="' . $show . '" class="btn btn-info btn-sm">Ko‘rish</a> ';
                    $btns .= '<a href="' . $edit . '" class="btn btn-warning btn-sm">Tahrirlash</a> ';
                    $btns .= '<form action="' . $del . '" method="POST" style="display:inline;" '
                        . 'onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">'
                        . $csrf . $method
                        . '<button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>'
                        . '</form>';

                    return $btns;
                })
                ->rawColumns(['neighborhood','actions'])
                ->toJson();
        }

        return view('streets.index');
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

    public function show(Request $request, Street $street)
    {
        // AJAX so‘rovi bo‘lsa, DataTables uchun mijozlarni qaytaramiz
        if ($request->ajax()) {
            $query = Customer::query()
                ->with(['company', 'waterMeter'])
                ->where('street_id', $street->id)
                ->where('is_active', true)
                ->select('customers.*');

            return DataTables::eloquent($query)
                ->addColumn('company', function (Customer $c) {
                    return $c->company
                        ? '<a href="'.route('companies.show',$c->company->id).'">'.e($c->company->name).'</a>'
                        : '-';
                })
                ->addColumn('address', fn(Customer $c) => e($c->address))
                ->addColumn('meter', function (Customer $c) {
                    return $c->waterMeter
                        ? '<a href="'.route('water_meters.show',$c->waterMeter->id).'">'.e($c->waterMeter->meter_number).'</a>'
                        : '<span class="text-muted">—</span>';
                })
                ->addColumn('balance', function (Customer $c) {
                    $balance = $c->balance ?? 0;
                    $cls = $balance < 0 ? 'balance-negative'
                        : ($balance > 0 ? 'balance-positive' : 'balance-zero');
                    return '<span class="'.$cls.'">'.number_format($balance,0,'',' ').' UZS</span>';
                })
                ->addColumn('last_reading', function (Customer $c) {
                    $last = $c->waterMeter?->readings?->first();
                return $last ? e($last->reading) : '—';
            })
                ->addColumn('actions', function (Customer $c) {
                    $show = route('customers.show',$c->id);
                    $edit = route('customers.edit',$c->id);
                    $del  = route('customers.destroy',$c->id);
                    return
                        '<a href="'.$show.'" class="btn btn-info btn-sm">Ko‘rish</a> '.
                        '<a href="'.$edit.'" class="btn btn-warning btn-sm">Tahrirlash</a> '.
                        '<form action="'.$del.'" method="POST" style="display:inline;" '.
                        'onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">'.
                        csrf_field().method_field('DELETE').
                        '<button class="btn btn-danger btn-sm">O‘chirish</button>'.
                        '</form>';
                })
                ->rawColumns(['company','meter','balance','actions'])
                ->toJson();
        }

        // Oddiy GET so‘rovi uchun ko‘chani va faol mijozlar sonini uzatamiz
        $customersCount = $street->customers()
            ->where('is_active', true)
            ->count();

        return view('streets.show', compact('street','customersCount'));
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
