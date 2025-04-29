<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Street;
use App\Models\Neighborhood;
use App\Models\Invoice; // Invoice modelini qo'shamiz
use App\Models\Payment; // Payment modelini qo'shamiz
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder; // Builder'ni qo'shamiz
use Illuminate\Support\Facades\DB;

class StreetController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // --- Collection usuli bilan ishlash ---
        if (request()->ajax()) {

            // 1. Asosiy query (filtrlar bilan, lekin subquerylarsiz)
            $query = Street::query()
                ->with('neighborhood') // with() endi ishlatsak bo'ladi
                // ->leftJoin('neighborhoods', 'streets.neighborhood_id', '=', 'neighborhoods.id') // Endi with() ishlatamiz
                // ->select('streets.*', 'neighborhoods.name as neighborhood_name', ...) // Select ham kerak emas
            ;

            // Mijozlar sonini hisoblash (filtr bilan) - Bu kerak
            $query->withCount(['customers as customer_count' => function ($q) use ($user) {
                $q->where('is_active', 1);
                if (!$user->hasRole('admin') && $user->company_id) {
                    $q->where('company_id', $user->company_id);
                }
            }]);

            // Admin bo'lmaganlar uchun asosiy filtr - Bu kerak
            if (!$user->hasRole('admin')) {
                $query->whereHas('customers', function ($q) use ($user) {
                    $q->where('is_active', 1);
                    if ($user->company_id) {
                        $q->where('company_id', $user->company_id);
                    }
                });
            }

            // addSelect subquerylarini olib tashladik

            // 2. DataTables so'rov parametrlarini hisobga olgan holda KO'CHALARNI OLISH
            // Biz DataTables::collection ishlatganimiz uchun pagination/search/order ni
            // query builderda qo'llashimiz kerak emas, DataTables o'zi qiladi.
            // Lekin KATTA ma'lumotlar to'plamida bu sekin bo'lishi mumkin.
            // Hozircha barcha mos keladigan ko'chalarni olamiz:
            $streets = $query->get();
 Log::info('Fetched Streets:', $streets->pluck('id', 'name')->toArray()); // 1-tekshiruv: Qaysi ko'chalar olindi?
            // 3. Olingan ko'chalar uchun qarzdorlikni alohida hisoblash
            $streetIds = $streets->pluck('id')->toArray();

            if (!empty($streetIds)) {
                // Jami invoyslarni olish (ko'cha bo'yicha guruhlab)
                $invoiceSums = Invoice::join('customers', 'invoices.customer_id', '=', 'customers.id')
                    ->whereIn('customers.street_id', $streetIds)
                    ->where('customers.is_active', 1)
                    ->when(!$user->hasRole('admin') && $user->company_id, function ($q) use ($user) {
                        $q->where('customers.company_id', $user->company_id);
                    })
                    ->groupBy('customers.street_id')
                    ->selectRaw('customers.street_id, sum(invoices.amount_due) as total_due')
                    ->pluck('total_due', 'street_id'); // [street_id => sum]
                Log::info('Invoice Sums:', $invoiceSums->toArray());
                // Jami to'lovlarni olish (ko'cha bo'yicha guruhlab)
                $paymentSums = Payment::join('customers', 'payments.customer_id', '=', 'customers.id')
                    ->whereIn('customers.street_id', $streetIds)
                    ->where('customers.is_active', 1)
                    ->when(!$user->hasRole('admin') && $user->company_id, function ($q) use ($user) {
                        $q->where('customers.company_id', $user->company_id);
                    })
                    ->groupBy('customers.street_id')
                    ->selectRaw('customers.street_id, sum(payments.amount) as total_paid')
                    ->pluck('total_paid', 'street_id'); // [street_id => sum]
                Log::info('Payment Sums:', $paymentSums->toArray());
                // 4. Har bir ko'chaga hisoblangan balansni qo'shish
                $streets->each(function ($street) use ($paymentSums, $invoiceSums) {
                    $totalPaid = $paymentSums->get($street->id, 0); // get(key, default_value)
                    $totalInvoiced = $invoiceSums->get($street->id, 0);
                    // Yangi 'calculated_balance' atributini qo'shamiz
                    $street->calculated_balance = $totalPaid - $totalInvoiced;
                    Log::info('Street ID: '.$street->id.' | Paid: '.$totalPaid.' | Invoiced: '.$totalInvoiced.' | Balance: '.$street->calculated_balance);
                });
            } else {
                // Agar ko'chalar topilmasa, har biriga balans 0 qo'shamiz
                $streets->each(function ($street) {
                    $street->calculated_balance = 0;
                });
            }


            // 5. DataTables'ga COLLECTION sifatida javob qaytarish
            return DataTables::collection($streets) // Eloquent o'rniga collection
            ->addColumn('neighborhood', function (Street $street) {
                // Endi with('neighborhood') ishlagani uchun to'g'ridan-to'g'ri murojaat qilamiz
                if ($street->neighborhood) {
                    $url = route('neighborhoods.show', $street->neighborhood->id);
                    return '<a href="' . $url . '" class="badge badge-outline text-blue">' . e($street->neighborhood->name) . '</a>';
                }
                return '-';
            })
                ->editColumn('customer_count', function(Street $street) {
                    // withCount natijasi
                    return $street->customer_count ?? 0;
                })
                ->addColumn('total_debt', function (Street $street) {
                    // PHPda qo'shilgan 'calculated_balance' atributidan foydalanamiz
                    $balance = $street->calculated_balance ?? 0; // default 0
                    $debt = $balance < 0 ? abs($balance) : 0;
                    $colorClass = $debt > 0 ? 'total-debt-negative' : 'total-debt-zero';
                    return '<span class="' . $colorClass . '">' . number_format($debt, 0, '', ' ') . ' UZS</span>';
                })
                ->addColumn('actions', function (Street $street) {
                    // Amallar tugmalari (avvalgidek)
                    // ... (button code) ...
                    $showUrl = route('streets.show', $street->id);
                    $editUrl = route('streets.edit', $street->id);
                    $deleteUrl = route('streets.destroy', $street->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $currentUser = Auth::user();

                    $buttons = '<a href="'.$showUrl.'" class="btn btn-info btn-sm">Ko‘rish</a> ';
                    $buttons .= '<a href="'.$editUrl.'" class="btn btn-warning btn-sm">Tahrirlash</a> ';

                    if ($currentUser->hasRole('admin')) {
                        $buttons .= '<form action="'.$deleteUrl.'" method="POST" style="display:inline;" onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">';
                        $buttons .= $csrf . $method;
                        $buttons .= '<button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>';
                        $buttons .= '</form>';
                    }
                    return $buttons;
                })
                // YANGI: total_debt ni rawColumns ga qo'shamiz
                ->rawColumns(['neighborhood', 'actions', 'total_debt'])
                // orderColumn kerak emas, DataTables collectionni o'zi saralaydi
                // ->orderColumn('calculated_balance', ...)
                ->toJson();
        }

        // Oddiy GET so'rov uchun faqat view'ni qaytaramiz
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

    public function show(Street $street) // Request $request qo'shilishi mumkin
    {
        $user = auth()->user();

        // Mijozlarni olish uchun asosiy query (bu AJAX uchun ham ishlatiladi)
        $query = Customer::query() // query() dan boshlash yaxshiroq
        ->with([
            'company', // Kompaniya ma'lumotini olish
            // 'street.neighborhood.city.region', // Bular DataTables uchun shart emas, agar ishlatilmasa
            'waterMeter', // Hisoblagichni olish
            'waterMeter.readings' => function ($q) { // Oxirgi ko'rsatkichni olish uchun
                $q->select('water_meter_id', 'reading', 'reading_date') // Kerakli ustunlarni tanlash
                ->latest('reading_date')->latest('id'); // Eng so'nggisini olish
            }
        ])
            ->where('street_id', $street->id) // Faqat shu ko'chadagilar
            ->where('is_active', 1); // Faqat aktivlar

        // Admin bo'lmasa, o'z kompaniyasi bo'yicha filtr
        if (!$user->hasRole('admin') && $user->company_id) {
            $query->where('company_id', $user->company_id);
        }

        // --- DataTables uchun o'zgarishlar ---
        if (request()->ajax()) {
            return DataTables::eloquent($query)
                ->addColumn('company', function (Customer $customer) {
                    // Faqat admin uchun kompaniya nomini link qilish
                    if (auth()->user()->hasRole('admin') && $customer->company) {
                        // Marshrut nomini tekshiring ('companies.show')
                        $url = route('companies.show', $customer->company->id);
                        return '<a href="' . $url . '">' . e($customer->company->name) . '</a>';
                    }
                    // Admin bo'lmasa yoki kompaniya yo'q bo'lsa (JS da bu ustun bo'lmaydi yoki bo'sh keladi)
                    return $customer->company ? e($customer->company->name) : '-';
                })
                ->editColumn('name', function (Customer $customer) {
                    // Mijoz nomini link qilish
                    $url = route('customers.show', $customer->id);
                    // e() XSS himoyasi uchun
                    return '<a href="' . $url . '" class="badge badge-outline text-blue">' . e($customer->name) . '</a>';
                })
                ->addColumn('meter', function (Customer $customer) {
                    // Hisoblagich nomini link qilish yoki "Yo'q" deb chiqarish
                    if ($customer->waterMeter) {
                        // Marshrut nomini tekshiring ('water_meters.show')
                        $url = route('water_meters.show', $customer->waterMeter->id);
                        return '<a href="' . $url . '" class="badge badge-outline text-blue">' . e($customer->waterMeter->meter_number) . '</a>';
                    }
                    return '<span class="text-muted">Hisoblagich yo‘q</span>';
                })
                ->addColumn('balance', function (Customer $customer) {
                    // Balansni formatlash va rang berish
                    // 'balance' accessori Customer modelida bo'lishi kerak yoki shu yerda hisoblash kerak
                    // Masalan: $balance = $customer->total_due - $customer->total_paid;
                    $balance = $customer->balance ?? 0; // Agar accessor bo'lsa (yoki 0)
                    $colorClass = $balance < 0 ? 'balance-negative' : ($balance > 0 ? 'balance-positive' : 'balance-zero');
                    // number_format bilan formatlash
                    return '<span class="' . $colorClass . '">' . number_format($balance, 0, '', ' ') . ' UZS</span>';
                })
                ->addColumn('last_reading', function (Customer $customer) {
                    // Oxirgi ko'rsatkichni olish (yuklangan readings aloqasidan)
                    $lastReading = $customer->waterMeter?->readings?->first(); // Eng oxirgi (latest) yuklangan
                    if ($lastReading) {
                        // Sanani ham qo'shish mumkin: e($lastReading->reading) . ' (' . Carbon::parse($lastReading->reading_date)->format('d.m.Y') . ')'
                        return e($lastReading->reading);
                    }
                    return '—'; // Agar ko'rsatkich yo'q bo'lsa
                })
                // ID ni qayta ishlamaymiz, data: 'id' yetarli
                // editColumn('id', function(Customer $customer) { return $customer->id; })
                // Telefon raqamini qayta ishlamaymiz, data: 'phone' yetarli
                // editColumn('phone', function(Customer $customer) { return $customer->phone ?? '-'; })
                ->rawColumns(['company', 'name', 'meter', 'balance']) // HTML ishlatilgan ustunlar
                ->toJson();
        }

        // Oddiy GET so'rov uchun (sahifa birinchi ochilganda)
        // Umumiy sonni hisoblash (agar DataTables'dan oldin kerak bo'lsa)
        // $customersCount = (clone $query)->count(); // Bu query DataTables ishlatishdan oldin bo'lishi kerak

        // Hozirgi kodda $customersCount allaqachon hisoblangan, shuni ishlatsak ham bo'ladi.
        // Lekin DataTables'dan oldin hisoblash to'g'riroq.
        $customersCount = $query->count(); // DataTablesga berishdan oldin sonni olamiz

        // View'ga faqat ko'cha va mijozlar sonini uzatamiz
        return view('streets.show', compact('street', 'customersCount'));
        // $customers o'zgaruvchisi endi kerak emas
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
