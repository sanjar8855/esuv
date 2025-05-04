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
        $user = Auth::user();
        // Foydalanuvchi ma'lumotlarini logga yozish (diagnostika uchun)
        Log::info('Neighborhood Index User Check:', [
            'id' => $user->id,
            'is_admin' => $user->hasRole('admin'), // Spatie Role ishlatiladi
            'company_id' => $user->company_id
        ]);

        if (request()->ajax()) {

            // 1. Asosiy query - Barcha mahallalarni oladi
            $query = Neighborhood::query()
                ->leftJoin('cities', 'neighborhoods.city_id', '=', 'cities.id')
                ->select('neighborhoods.*', 'cities.name as city_name', 'cities.id as city_id_for_route');

            // 2. Ko'chalar sonini hisoblash (FILTRSIZ - hamma uchun umumiy son)
            // Avvalgi kelishuvga binoan barcha joylashuvlar ko'rinishi kerak edi
            $query->withCount('streets as street_count'); // Filtr olib tashlandi

            // 3. Jami QARZDORLIKNI hisoblash (Customer balanslarini SUM qilish orqali)
            $query->addSelect(['total_customer_debt_sum' => Customer::select(
                DB::raw('SUM(CASE WHEN balance < 0 THEN balance ELSE 0 END)') // Faqat manfiy balans
            )
                ->where('customers.is_active', true) // Aktiv mijozlar
                ->whereHas('street', function (Builder $streetQuery) {
                    // Ko'chani mahalla bilan bog'lash (tashqi queryga)
                    $streetQuery->whereColumn('streets.neighborhood_id', 'neighborhoods.id');
                })
                // Kompaniya filtrini QAT'IY ravishda faqat admin bo'lmaganlar uchun qo'llaymiz
                ->when(!$user->hasRole('admin'), function ($customerSubQuery) use ($user) {
                    // Agar non-admin uchun company_id null bo'lsa ham, where null ishlaydi (natija 0 chiqadi)
                    $customerSubQuery->where('customers.company_id', $user->company_id);
                })
                // Admin uchun bu ->when() sharti bajarilmaydi va filtr qo'shilmaydi
            ]);

            // 4. DataTables'ga javob qaytarish
            return DataTables::eloquent($query)
                ->addColumn('city', function (Neighborhood $neighborhood) {
                    if ($neighborhood->city_name) {
                        $url = route('cities.show', $neighborhood->city_id_for_route ?? $neighborhood->city_id);
                        return '<a href="' . $url . '" class="badge badge-outline text-blue">' . e($neighborhood->city_name) . '</a>';
                    }
                    return '-';
                })
                ->editColumn('street_count', function (Neighborhood $neighborhood) {
                    // withCount dan kelgan qiymat (endi filtrsiz, jami ko'chalar soni)
                    return $neighborhood->street_count ?? 0;
                })
                ->addColumn('total_debt', function (Neighborhood $neighborhood) {
                    // Endi qo'shimcha tekshiruv kerak emas, subquery natijasini ishlatamiz
                    $debt = abs($neighborhood->total_customer_debt_sum ?? 0);
                    $colorClass = $debt > 0 ? 'total-debt-negative' : 'total-debt-zero';
                    return '<span class="' . $colorClass . '">' . number_format($debt, 0, '', ' ') . ' UZS</span>';
                })
                ->addColumn('actions', function (Neighborhood $neighborhood) {
                    $showUrl = route('neighborhoods.show', $neighborhood->id);
                    $editUrl = route('neighborhoods.edit', $neighborhood->id);
                    $deleteUrl = route('neighborhoods.destroy', $neighborhood->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $currentUser = Auth::user();

                    $buttons = '<a href="'.$showUrl.'" class="btn btn-info btn-sm">Ko‘rish</a> ';

                    // Edit/Delete faqat admin uchun
                    if ($currentUser->hasRole('admin')) {
                        $buttons .= '<a href="'.$editUrl.'" class="btn btn-warning btn-sm">Tahrirlash</a> ';
                        $buttons .= '<form action="'.$deleteUrl.'" method="POST" style="display:inline;" onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">';
                        $buttons .= $csrf . $method;
                        $buttons .= '<button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>';
                        $buttons .= '</form>';
                    }
                    return $buttons;
                })
                ->rawColumns(['city', 'actions', 'total_debt'])
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

        if (request()->ajax()) {
            $streetsQuery = $neighborhood->streets(); // Relationshipdan boshlaymiz

            // Mijozlar sonini hisoblash (filtr bilan)
            $streetsQuery->withCount(['customers as customer_count' => function (Builder $q) use ($user) {
                $q->where('is_active', 1);
                if (!$user->hasRole('admin') && $user->company_id) {
                    $q->where('company_id', $user->company_id);
                }
            }]);

            // Jami hisoblangan summani (Invoices) hisoblash (filtr bilan)
            // Bu avval to'g'ri ishlagan edi
            $streetsQuery->addSelect(['total_invoices_sum' => Invoice::selectRaw('sum(amount_due)')
                ->join('customers', 'invoices.customer_id', '=', 'customers.id')
                ->whereColumn('customers.street_id', 'streets.id')
                ->where('customers.is_active', 1)
                ->when(!$user->hasRole('admin') && $user->company_id, function ($q) use ($user) {
                    $q->where('customers.company_id', $user->company_id);
                })
            ]);

            // Jami to'langan summani (Payments) hisoblash (filtr bilan)
            // Bu ham avval to'g'ri ishlagan edi
            $streetsQuery->addSelect(['total_payments_sum' => Payment::selectRaw('sum(amount)')
                ->join('customers', 'payments.customer_id', '=', 'customers.id')
                ->whereColumn('customers.street_id', 'streets.id')
                ->where('customers.is_active', 1)
                ->when(!$user->hasRole('admin') && $user->company_id, function ($q) use ($user) {
                    $q->where('customers.company_id', $user->company_id);
                })
                // ->where('payments.status', 'completed') // Agar kerak bo'lsa
            ]);

            // DB::raw bilan balans hisoblashni olib tashladik

            return DataTables::eloquent($streetsQuery)
                ->editColumn('name', function(Street $street) {
                    $url = route('streets.show', $street->id);
                    return '<a href="' . $url . '" class="badge badge-outline text-blue">' . e($street->name) . '</a>';
                })
                ->editColumn('customer_count', function(Street $street) {
                    return $street->customer_count ?? 0;
                })
                ->addColumn('total_debt', function (Street $street) {
                    // Qarzdorlikni hisoblash (avvalgidek, endi to'g'ri qiymatlar bilan)
                    $totalPaid = $street->total_payments_sum ?? 0;
                    $totalInvoiced = $street->total_invoices_sum ?? 0;
                    $balance = $totalPaid - $totalInvoiced;
                    $debt = $balance < 0 ? abs($balance) : 0;
                    $colorClass = $debt > 0 ? 'total-debt-negative' : 'total-debt-zero';
                    return '<span class="' . $colorClass . '">' . number_format($debt, 0, '', ' ') . ' UZS</span>';
                })
                ->addColumn('actions', function(Street $street) {
                    $showUrl = route('streets.show', $street->id);
                    $editUrl = route('streets.edit', $street->id);
                    $deleteUrl = route('streets.destroy', $street->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $currentUser = Auth::user();

                    $buttons = '<a href="' . $showUrl . '" class="btn btn-info btn-sm">Ko‘rish</a> ';
                    if ($currentUser->hasRole('admin')) {
                        $buttons .= '<a href="' . $editUrl . '" class="btn btn-warning btn-sm">Tahrirlash</a> ';
                        $buttons .= '<form action="' . $deleteUrl . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">';
                        $buttons .= $csrf . $method;
                        $buttons .= '<button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>';
                        $buttons .= '</form>';
                    }
                    return $buttons;
                })
                ->rawColumns(['name', 'actions', 'total_debt'])

                // --- SARALASH UCHUN QO'SHIMCHA ---
                // 'calculated_balance' (JS dagi name) ustuni bosilganda ishlaydi
                ->orderColumn('calculated_balance', function ($query, $order) {
                    // Balans bo'yicha saralash: (Jami to'lovlar - Jami invoyslar)
                    // Bu yerda addSelect orqali qo'shilgan aliaslarni ishlatishga harakat qilamiz
                    // COALESCE null qiymatlarni 0 ga aylantiradi
                    $query->orderByRaw('(COALESCE(total_payments_sum, 0) - COALESCE(total_invoices_sum, 0)) ' . $order);
                })
                ->toJson();
        } // --- AJAX tugadi ---

        // --- Oddiy GET so'rovi ---
        $streetsCount = $neighborhood->streets()
            ->whereHas('customers', function ($q) use ($user) {
                $q->where('is_active', 1);
                if (!$user->hasRole('admin') && $user->company_id) {
                    $q->where('company_id', $user->company_id);
                }
            })
            ->count(); // Avvalgidek
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
