<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Region;
use App\Models\Customer;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CityController extends Controller
{

    public function index(Request $request) // Requestni inject qilamiz
    {
        // Bu sahifaga faqat admin kiradi deb hisoblaymiz
        // $user = auth()->user();

        if ($request->ajax()) {
            $query = City::query()
                ->with([
                    'region', // Viloyat ma'lumotini yuklash
                    'company' // Shaharning kompaniyasini yuklash
                ])
                ->select('cities.*'); // Asosiy jadval ustunlarini aniq tanlaymiz

            // 1. Mahallalar sonini hisoblash (har bir shahar uchun, filtrsiz)
            $query->withCount('neighborhoods as neighborhood_count_val'); // 'neighborhoods_count' blade'da ishlatiladi, alias beramiz

            // 2. Mijozlar sonini hisoblash (Subquery bilan)
            // Shu shaharga va shaharning kompaniyasiga tegishli aktiv mijozlar soni
            $query->addSelect(['customer_count_val' => Customer::select(DB::raw('count(*)'))
                ->where('customers.is_active', true)
                ->whereHas('street.neighborhood', function (Builder $neighborhoodQuery) {
                    // Mijozning mahallasi shu shaharga tegishli ekanligini tekshirish
                    $neighborhoodQuery->whereColumn('neighborhoods.city_id', 'cities.id');
                })
                // Mijozning kompaniyasi shaharning kompaniyasiga mos kelishini tekshirish
                // Agar cities.company_id NULL bo'lsa, customers.company_id ham NULL bo'lganlar sanaladi
                ->whereColumn('customers.company_id', 'cities.company_id')
            ]);


            return DataTables::eloquent($query)
                ->addIndexColumn() // "N" ustuni uchun
                ->addColumn('region_name', function (City $city) {
                    return $city->region ? $city->region->name : '-';
                })
                ->addColumn('company_name_display', function (City $city) {
                    return $city->company ? $city->company->name : '<span class="text-muted">Belgilanmagan</span>';
                })
                ->editColumn('name', function(City $city) { // Shahar nomi
                    return $city->name;
                })
                ->editColumn('neighborhood_count', function(City $city) { // Mahalla soni
                    return $city->neighborhood_count_val ?? 0; // withCount dan kelgan qiymat
                })
                ->editColumn('customer_count', function(City $city) { // Mijozlar soni
                    return $city->customer_count_val ?? 0; // addSelect dan kelgan qiymat
                })
                ->addColumn('actions', function (City $city) {
                    $showUrl = route('cities.show', $city->id);
                    $editUrl = route('cities.edit', $city->id);
                    $deleteUrl = route('cities.destroy', $city->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    // Faqat admin ko'rishi va amallarni bajarishi mumkin
                    $buttons = '<a href="'.$showUrl.'" class="btn btn-info btn-sm">Ko‘rish</a> ';
                    $buttons .= '<a href="'.$editUrl.'" class="btn btn-warning btn-sm">Tahrirlash</a> ';
                    $buttons .= '<form action="'.$deleteUrl.'" method="POST" style="display:inline;" onsubmit="return confirm(\'Haqiqatan ham o‘chirmoqchimisiz?\');">'.$csrf.$method.'<button type="submit" class="btn btn-danger btn-sm">O‘chirish</button></form>';
                    return $buttons;
                })
                ->rawColumns(['region_name', 'company_name_display', 'actions'])
                ->make(true);
        }

        // AJAX bo'lmagan so'rov uchun (sahifa birinchi ochilganda sarlavha uchun)
        // Jami shaharlar sonini yuborishimiz mumkin, lekin DataTables o'zi hisoblaydi.
        // Agar sarlavhada "Jami X ta shahar" deb chiqarish kerak bo'lsa:
        // $citiesCount = City::count(); // Yoki agar admin bo'lmaganlar uchun ham bo'lsa, filtr bilan
        // return view('cities.index', compact('citiesCount'));
        return view('cities.index'); // Endi ma'lumot uzatish shart emas
    }

    public function create()
    {
        // Bu amalni faqat admin bajarishi mumkin (agar kerak bo'lsa Policy yoki middleware orqali tekshiring)
        // misol uchun: $this->authorize('create', City::class);

        $regions = Region::orderBy('name', 'asc')->get();
        $companies = Company::orderBy('name', 'asc')->get(); // Barcha kompaniyalarni olish

        return view('cities.create', compact('regions', 'companies'));
    }

    public function store(Request $request)
    {
        // Bu amalni faqat admin bajarishi mumkin
        // $this->authorize('create', City::class);

        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'company_id' => 'nullable|exists:companies,id', // company_id endi ixtiyoriy va mavjudligini tekshiradi
            'name' => [
                'required',
                'string',
                'max:255',
                // Unikallik (name + region_id + company_id) kombinatsiyasi bo'yicha tekshiriladi
                // company_id NULL bo'lsa, (name + region_id + NULL company_id) bo'yicha tekshiriladi
                Rule::unique('cities')->where(function ($query) use ($request) {
                    return $query->where('region_id', $request->region_id)
                        ->where('company_id', $request->company_id); // $request->company_id NULL bo'lishi mumkin
                }),
            ],
        ]);

        try {
            // City modelida $fillable ga 'company_id' qo'shilgan bo'lishi kerak
            City::create([
                'name' => $validated['name'],
                'region_id' => $validated['region_id'],
                'company_id' => $validated['company_id'], // Bu NULL bo'lishi mumkin
            ]);
            return redirect()->route('cities.index')->with('success', 'Shahar/Tuman muvaffaqiyatli qo‘shildi!');
        } catch (\Exception $e) {
            Log::error('Error storing city: ' . $e->getMessage()); // Xatolikni logga yozish
            return back()->withInput()->with('error', 'Shahar/Tuman qo‘shishda xatolik yuz berdi.');
        }
    }

    public function show(City $city)
    {
        $neighborhoods = $city->neighborhoods()
            ->withCount(['streets as street_count'])
            ->get();

        foreach ($neighborhoods as $neighborhood) {
            $neighborhood->customer_count = Customer::where('is_active', true)
                ->whereHas('street', function ($q) use ($neighborhood) {
                    $q->where('neighborhood_id', $neighborhood->id);
                })
                ->count();
        }

        $page    = request()->get('page', 1);
        $perPage = 15;
        $paginated = new LengthAwarePaginator(
            $neighborhoods->forPage($page, $perPage),
            $neighborhoods->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('cities.show', [
            'city'         => $city,
            'neighborhoods'=> $paginated,
        ]);
    }

    public function edit(City $city) // Route model binding orqali City olinadi
    {
        // Bu amalni faqat admin bajarishi mumkin
        // $this->authorize('update', $city); // Policy orqali tekshirish yaxshiroq

        $regions = Region::orderBy('name', 'asc')->get();
        $companies = Company::orderBy('name', 'asc')->get();

        return view('cities.edit', compact('city', 'regions', 'companies'));
    }

    public function update(Request $request, City $city)
    {
        // Bu amalni faqat admin bajarishi mumkin
        // $this->authorize('update', $city);

        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'company_id' => 'nullable|exists:companies,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities')->where(function ($query) use ($request) {
                    return $query->where('region_id', $request->region_id)
                        ->where('company_id', $request->company_id);
                })->ignore($city->id), // Joriy shaharni unikallik tekshiruvidan chiqarib tashlash
            ],
        ]);

        try {
            $city->update([
                'name' => $validated['name'],
                'region_id' => $validated['region_id'],
                'company_id' => $validated['company_id'], // Bu NULL bo'lishi mumkin
            ]);
            return redirect()->route('cities.index')->with('success', 'Shahar/Tuman muvaffaqiyatli yangilandi!');
        } catch (\Exception $e) {
            Log::error('Error updating city ID ' . $city->id . ': ' . $e->getMessage());
            return back()->withInput()->with('error', 'Shahar/Tuman yangilashda xatolik yuz berdi.');
        }
    }

    public function destroy(City $city)
    {
        // Bu amalni faqat admin bajarishi mumkin
        // $this->authorize('delete', $city);

        try {
            // Qo'shimcha tekshiruv: Agar shaharda mahallalar bo'lsa, o'chirishga ruxsat bermaslik
            if ($city->neighborhoods()->count() > 0) {
                return redirect()->route('cities.index')->with('error', 'Bu shaharda mahallalar mavjudligi sababli uni o\'chirib bo\'lmaydi.');
            }
            // Yoki cascade delete ishlatilsa, bu shart kerak emas, lekin foydalanuvchini ogohlantirish yaxshi

            $cityName = $city->name; // Xabar uchun saqlab qolamiz
            $city->delete();
            return redirect()->route('cities.index')->with('success', "'{$cityName}' shahri/tumani muvaffaqiyatli o‘chirildi!");
        } catch (\Exception $e) {
            Log::error('Error deleting city ID ' . $city->id . ': ' . $e->getMessage());
            return redirect()->route('cities.index')->with('error', 'Shaharni o‘chirishda xatolik yuz berdi.');
        }
    }
}
