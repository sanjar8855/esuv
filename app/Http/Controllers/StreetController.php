<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Street;
use App\Models\Neighborhood;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StreetController extends Controller
{
    public function index(Request $request)
    {
        // Bu sahifaga faqat admin kiradi
        // $user = Auth::user(); // Endi shart emas

        if ($request->ajax()) {
            $query = Street::query()
                ->select('streets.*') // Asosiy jadval ustunlarini aniq tanlaymiz (JOINlarda chalkashmasligi uchun)
                // Saralash va ma'lumot olish uchun kerakli jadvallarni JOIN qilamiz
                // Kompaniya nomini olish va saralash uchun (agar company_id NULL bo'lsa ham ko'cha chiqishi uchun leftJoin)
                ->leftJoin('companies', 'streets.company_id', '=', 'companies.id')
                // Mahalla nomini olish va saralash uchun
                ->leftJoin('neighborhoods', 'streets.neighborhood_id', '=', 'neighborhoods.id')
                // Agar shahar/viloyat nomi bo'yicha ham saralash kerak bo'lsa, ularni ham JOIN qilish mumkin:
                // ->leftJoin('cities', 'neighborhoods.city_id', '=', 'cities.id')
                // ->leftJoin('regions', 'cities.region_id', '=', 'regions.id')

                // Eager loading (with) JOIN qilingan ma'lumotlarni Eloquent obyektlariga to'g'ri joylash uchun kerak bo'lishi mumkin,
                // lekin JOIN qilingan ustunlarni to'g'ridan-to'g'ri addColumn ichida ishlataveramiz.
                // Agar ->with() ishlatilsa, u alohida so'rov yuboradi.
                // JOIN qilganimizdan keyin ->with() shart bo'lmasligi mumkin, agar select() da kerakli ustunlarni olsak.
                // Hozircha ->with() ni qoldiramiz, lekin select() ga e'tibor beramiz.
                ->with([
                    'neighborhood.city.region', // Bu display uchun hali ham kerak
                    'company' // Bu ham display uchun kerak
                ]);

            $query->withCount(['customers as customer_count' => function (Builder $q) {
                $q->where('customers.is_active', true)
                    ->whereColumn('customers.company_id', 'streets.company_id');
            }]);

            return DataTables::eloquent($query)
                ->addColumn('id_display', function(Street $street){
                    return $street->id;
                })
                ->addColumn('company_name_display', function (Street $street) {
                    // Eager loaded company dan foydalanamiz
                    return $street->company ? $street->company->name : '<span class="text-muted">Kompaniya belgilanmagan</span>';
                })
                ->addColumn('neighborhood_full_path_display', function (Street $street) { // addColumn nomlarini o'zgartirdim
                    $pathParts = [];
                    if ($street->neighborhood) {
                        $pathParts[] = $street->neighborhood->name; // Mahalla
                        if ($street->neighborhood->city) {
                            $pathParts[] = $street->neighborhood->city->name; // Shahar
                            if ($street->neighborhood->city->region) {
                                $pathParts[] = $street->neighborhood->city->region->name; // Viloyat
                            }
                        }
                    }
                    $link = $street->neighborhood ? route('neighborhoods.show', $street->neighborhood->id) : '#';
                    $displayText = implode(', ', $pathParts);
                    return $displayText ? '<a href="' . $link . '" class="badge badge-outline text-blue">' . $displayText . '</a>' : '-';
                })
                ->editColumn('name', function ($street){
                    return $street->name; // Ko'cha nomi (streets.name)
                })
                ->editColumn('customer_count', function ($street) {
                    return $street->customer_count ?? 0;
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
                ->rawColumns(['neighborhood_full_path_display', 'actions', 'company_name_display'])
                ->make(true);
        }

        return view('streets.index');
    }

    public function create()
    {
        // Faqat adminlar ko'cha qo'sha oladi deb hisoblaymiz
        // Agar boshqa rollar ham qo'sha olsa, bu yerga shart qo'shish kerak
        if (!auth()->user()->hasRole('admin')) {
            // abort(403, 'Bu amal uchun ruxsatingiz yo\'q.');
            // Yoki boshqa sahifaga yo'naltirish
            return redirect()->route('streets.index')->with('error', 'Sizda ko\'cha qo\'shish uchun ruxsat yo\'q.');
        }

        $companies = Company::orderBy('name')->get(); // Barcha kompaniyalar ro'yxati
        $neighborhoods = Neighborhood::with('city.region')->orderBy('name')->get(); // Barcha mahallalar (keyinchalik kompaniyaga qarab filtrlash mumkin)

        return view('streets.create', compact('companies', 'neighborhoods'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            // abort(403);
            return redirect()->route('streets.index')->with('error', 'Sizda ko\'cha saqlash uchun ruxsat yo\'q.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('streets')->where(function ($query) use ($request) {
                    return $query->where('neighborhood_id', $request->neighborhood_id)
                        ->where('company_id', $request->company_id); // Kompaniya bo'yicha unikallik
                }),
            ],
            'neighborhood_id' => 'required|exists:neighborhoods,id',
            'company_id' => 'required|exists:companies,id', // Kompaniya tanlanishi shart
        ]);

        // Street modelida company_id fillable'ga qo'shilgan bo'lishi kerak
        Street::create($validated);

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
                        ? '<a href="'.route('companies.show',$c->company->id).'">'.$c->company->name.'</a>'
                        : '-';
                })
                ->addColumn('address', fn(Customer $c) => $c->address)
                ->addColumn('meter', function (Customer $c) {
                    return $c->waterMeter
                        ? '<a href="'.route('water_meters.show',$c->waterMeter->id).'">'.$c->waterMeter->meter_number.'</a>'
                        : '<span class="text-muted">—</span>';
                })
                ->addColumn('balance', function (Customer $c) {
                    $balance = $c->balance ?? 0;
                    $cls = $balance < 0 ? 'balance-negative'
                        : ($balance > 0 ? 'balance-positive' : 'balance-zero');
                    return '<span class="'.$cls.'">'
                        . number_format($balance, 0, '', ' ') . ' UZS</span>';
                })
                // Quyidagi qatorni qo'shing:
                ->orderColumn('balance', function ($query, $direction) {
                    // Agar customers jadvlining balance ustuni bo'lsa:
                    $query->orderBy('customers.balance', $direction);
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

    public function edit(Street $street) // Route model binding ishlatiladi
    {
        // Faqat adminlar tahrirlay oladi
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('streets.index')->with('error', 'Sizda ko\'chani tahrirlash uchun ruxsat yo\'q.');
        }

        $companies = Company::orderBy('name')->get(); // Kompaniyalar ro'yxati
        $neighborhoods = Neighborhood::with('city.region')->orderBy('name')->get(); // Mahallalar ro'yxati

        return view('streets.edit', compact('street', 'companies', 'neighborhoods'));
    }

    public function update(Request $request, Street $street)
    {
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('streets.index')->with('error', 'Sizda ko\'chani yangilash uchun ruxsat yo\'q.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('streets')->where(function ($query) use ($request) {
                    return $query->where('neighborhood_id', $request->neighborhood_id)
                        ->where('company_id', $request->company_id);
                })->ignore($street->id), // Joriy ko'chani unikallik tekshiruvidan chiqarib tashlash
            ],
            'neighborhood_id' => 'required|exists:neighborhoods,id',
            'company_id' => 'required|exists:companies,id', // Kompaniya tanlanishi shart
        ]);

        $street->update($validated);

        return redirect()->route('streets.index')->with('success', 'Ko‘cha muvaffaqiyatli yangilandi!');
    }

    public function destroy(Street $street)
    {
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('streets.index')->with('error', 'Sizda ko\'chani o\'chirish uchun ruxsat yo\'q.');
        }

        // Bu yerga qo'shimcha tekshiruvlar qo'shish mumkin,
        // masalan, agar ko'chada mijozlar bo'lsa o'chirishga ruxsat bermaslik.
        if ($street->customers()->count() > 0) {
            return redirect()->route('streets.index')->with('error', 'Bu ko\'chada mijozlar mavjudligi sababli uni o\'chirib bo\'lmaydi.');
        }

        $street->delete();

        return redirect()->route('streets.index')->with('success', 'Ko‘cha muvaffaqiyatli o‘chirildi!');
    }
}
