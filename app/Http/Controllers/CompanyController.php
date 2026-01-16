<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Tariff;
use App\Models\Neighborhood;
use App\Models\Street;
use App\Models\Plan;
use App\Models\SaasPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Kompaniyalar ro‘yxatini ko‘rsatish.
     */
    public function index()
    {
        $companies = Company::withCount(['users', 'customers'])->get();
        return view('companies.index', compact('companies'));
    }

    /**
     * Yangi kompaniya yaratish formasi.
     */
    public function create()
    {
        $plans = Plan::where('is_active', true)->get();

        return view('companies.create', compact('plans'));
    }

    /**
     * Yangi kompaniyani saqlash.
     */
    public function store(Request $request)
    {
        // 1. Validatsiya
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:companies,email', // email ustuni companies jadvalida unique bo'lishi kerak
            'phone' => 'required|string|max:20',
            'plan' => 'nullable|in:basic,premium', // Reja majburiy bo'lmasligi mumkin
            'plan_id' => 'nullable|exists:plans,id',
            'address' => 'nullable|string|max:255',
            'logo' => 'nullable|file|mimes:jpg,png,svg,webp|max:4096', // Logo validatsiyasi
            'schet' => 'nullable|string|digits:20',
            'inn' => 'nullable|string|digits:9',
            'description' => 'nullable|string|max:255',
        ]);

        // 2. Faylni saqlash (agar yuklangan bo'lsa)
        $logoPath = null; // Boshlang'ich qiymat null
        if ($request->hasFile('logo')) {
            // Faylni 'public' diskidagi 'company_logos' papkasiga saqlash
            // store() metodi saqlangan faylning nisbiy manzilini qaytaradi
            $logoPath = $request->file('logo')->store('company_logos', 'public');
        }

        // 3. Model uchun ma'lumotlarni tayyorlash
        $companyData = $validatedData; // Validatsiyadan o'tgan ma'lumotlarni olish
        $companyData['logo'] = $logoPath; // Saqlangan fayl manzilini (yoki null) 'logo' kalitiga o'rnatish
        $companyData['is_active'] = $request->has('is_active'); // Checkbox qiymatini boolean (true/false) sifatida olish

        // 4. Kompaniyani bazaga yozish
        Company::create($companyData);

        // 5. Redirect qilish
        return redirect()->route('companies.index')->with('success', 'Kompaniya muvaffaqiyatli qo‘shildi!');
    }

    /**
     * Bitta kompaniya ma’lumotlarini ko‘rsatish.
     */
    public function show(Company $company)
    {
        $company->load([
            'users' => function($query) {
                $query->orderBy('id', 'asc');
            },
            'tariffs' => function($query) {
                $query->orderBy('id', 'desc');
            }
        ]);

        $tariff = $company->tariffs()->where('is_active', true)->latest()->first()
            ?? new Tariff(['price_per_m3' => 0, 'for_one_person' => 0]);

        $neighborhoods = $company->neighborhoods()
            ->with('city')
            ->latest()
            ->paginate(10, ['*'], 'neighborhoods_page');

        $streets = $company->streets()
            ->with('neighborhood')
            ->latest()
            ->paginate(10, ['*'], 'streets_page');

        $operatorMap = [
            '90' => 'Beeline', '91' => 'Beeline',
            '93' => 'Ucell',    '94' => 'Ucell',
            '97' => 'Mobiuz',
            '95' => 'Uzmobile', '99' => 'Uzmobile',
            '33' => 'Humans',   '77' => 'Humans', '88' => 'Humans',
            '98' => 'Perfectum',
        ];

        // Ma'lumotlar bazasidan telefon raqamlari kodlarini ajratib olib, sanash
        $statsFromDb = DB::table('customers')
            ->select(
                DB::raw("SUBSTRING(phone, 2, 2) as operator_code"), // Qavs ichidagi 2 ta raqamni ajratib oladi. Masalan, '(91)' dan '91' ni.
                DB::raw("COUNT(*) as total")
            )
            ->where('company_id', $company->id)
            ->whereNotNull('phone')
            ->where('phone', 'LIKE', '(%)%') // Faqat (XX) formatidagilarni olish
            ->groupBy('operator_code')
            ->get();

        // Natijalarni birlashtirish (masalan, 90 va 91 kodlari Beeline ga tegishli)
        $finalOperatorStats = collect(); // Bo'sh kolleksiya
        foreach($statsFromDb as $stat) {
            // Operator nomini xaritadan topamiz
            $name = $operatorMap[$stat->operator_code] ?? 'Boshqa (' . $stat->operator_code . ')';
            // Agar bu operator nomi oldin ham uchragan bo'lsa, sonini qo'shamiz
            $currentCount = $finalOperatorStats->get($name, 0);
            $finalOperatorStats->put($name, $currentCount + $stat->total);
        }
        // Natijani kamayish tartibida saralash
        $finalOperatorStats = $finalOperatorStats->sortDesc();

        $saasPayments = $company->saasPayments() // Company modelida yaratgan bog'liqlik
        ->with('createdBy') // To'lovni qo'shgan adminni ham yuklaymiz
        ->latest('payment_date') // To'lov sanasi bo'yicha saralaymiz (eng yangisi birinchi)
        ->paginate(10, ['*'], 'saas_payments_page');

        return view('companies.show', compact('company', 'tariff', 'neighborhoods', 'streets', 'finalOperatorStats', 'saasPayments'));
    }

    /**
     * Kompaniyani tahrirlash formasi.
     */
    public function edit(Company $company)
    {
        $plans = Plan::where('is_active', true)->get(); // Barcha aktiv tarif rejalarini olish

        return view('companies.edit', compact('company', 'plans'));
    }

    /**
     * Kompaniyani yangilash.
     */
    public function update(Request $request, $id)
    {
        // 1. Yangilanayotgan kompaniyani topish
        $company = Company::findOrFail($id);

        // 2. Validatsiya
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            // Emailni yangilashda joriy kompaniyaning emailini unique tekshiruvidan chiqarib tashlash
            'email' => 'nullable|email|unique:companies,email,' . $id,
            'phone' => 'required|string|max:20',
            'plan' => 'nullable|in:basic,premium', // Reja majburiy bo'lmasligi mumkin
            'plan_id' => 'nullable|exists:plans,id',
            'address' => 'nullable|string|max:255',
            'logo' => 'nullable|file|mimes:jpg,png,svg,webp|max:4096', // Yangi logo uchun validatsiya
            'schet' => 'nullable|string|digits:20',
            'inn' => 'nullable|string|digits:9',
            'description' => 'nullable|string|max:255',
        ]);

        // 3. Yangi faylni saqlash va eskisini o'chirish (agar yangi fayl yuklangan bo'lsa)
        $logoPath = $company->logo; // Boshida eski logotip manzilini olib turamiz

        if ($request->hasFile('logo')) {
            // a) Eski logotip faylini storage'dan o'chirish (agar mavjud bo'lsa)
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }

            // b) Yangi logotipni 'public' diskidagi 'company_logos' papkasiga saqlash
            $logoPath = $request->file('logo')->store('company_logos', 'public');
        }

        // 4. Model uchun yangilanadigan ma'lumotlarni tayyorlash
        $updateData = $validatedData; // Validatsiyadan o'tgan ma'lumotlarni olish
        $updateData['logo'] = $logoPath; // Yangi (yoki eski) fayl manzilini 'logo' kalitiga o'rnatish
        $updateData['is_active'] = $request->has('is_active'); // Checkbox qiymatini boolean (true/false) sifatida olish

        // 5. Kompaniya ma'lumotlarini bazada yangilash
        $company->update($updateData);

        // 6. Redirect qilish
        return redirect()->route('companies.index')->with('success', 'Kompaniya muvaffaqiyatli yangilandi!');
    }

    /**
     * Kompaniyani o'chirish.
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Kompaniya muvaffaqiyatli o\'chirildi!');
    }

    /**
     * Kompaniya mijozlarini tozalash (barcha mijozlar va ularga bog'liq ma'lumotlar)
     */
    public function clearCustomers(Company $company)
    {
        // ✅ 1. Ruxsat tekshiruvi - faqat admin va company_owner
        $user = auth()->user();
        if (!$user->hasRole('admin') && !($user->hasRole('company_owner') && $user->company_id === $company->id)) {
            return redirect()->back()->with('error', 'Sizda bu amalni bajarish uchun ruxsat yo\'q!');
        }

        try {
            // ✅ 2. Transaction ichida o'chirish
            DB::beginTransaction();

            // Kompaniyaga tegishli mijozlarni topish
            $customers = $company->customers()->pluck('id');

            if ($customers->isEmpty()) {
                DB::rollBack();
                return redirect()->back()->with('info', 'Bu kompaniyada mijozlar mavjud emas.');
            }

            // ✅ 3. Mijozlarga bog'liq ma'lumotlarni o'chirish

            // To'lovlarni o'chirish (payments jadvalida customer_id bo'yicha)
            DB::table('payments')->whereIn('customer_id', $customers)->delete();

            // Invoyslarni o'chirish (invoices jadvalida customer_id bo'yicha)
            DB::table('invoices')->whereIn('customer_id', $customers)->delete();

            // Hisoblagich ko'rsatkichlarini o'chirish (meter_readings jadvalida water_meter_id orqali)
            $waterMeterIds = DB::table('water_meters')->whereIn('customer_id', $customers)->pluck('id');
            if ($waterMeterIds->isNotEmpty()) {
                DB::table('meter_readings')->whereIn('water_meter_id', $waterMeterIds)->delete();
            }

            // Hisoblagichlarni o'chirish (water_meters jadvalida customer_id bo'yicha)
            DB::table('water_meters')->whereIn('customer_id', $customers)->delete();

            // Telegram akkauntlar bog'lanishini o'chirish (customer_telegram_account pivot jadvali)
            DB::table('customer_telegram_account')->whereIn('customer_id', $customers)->delete();

            // Mijozlarni o'chirish
            DB::table('customers')->whereIn('id', $customers)->delete();

            DB::commit();

            return redirect()->back()->with('success', count($customers) . ' ta mijoz va ularga tegishli barcha ma\'lumotlar muvaffaqiyatli tozalandi!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Mijozlarni tozalashda xatolik: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Xatolik yuz berdi: ' . $e->getMessage());
        }
    }
}
