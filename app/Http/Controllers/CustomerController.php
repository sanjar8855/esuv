<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\Street;
use App\Models\WaterMeter;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon; // Buni qo'shing


class CustomerController extends Controller
{
    /**
     * Mijozlar ro‘yxati.
     */
    public function index()
    {
        $user = Auth::user();

        // Ko'chalar ro'yxatini olish (bu filtr uchun kerak)
        if ($user->hasRole('admin')) {
            $streets = Street::with('neighborhood.city.region')->get(); // <-- Optimallashtirish uchun eager load
        } else {
            $streets = $user->company
                ? Street::whereHas('customers', function ($query) use ($user) {
                    $query->where('company_id', $user->company->id);
                })->with('neighborhood.city.region')->get() // <-- Optimallashtirish uchun eager load
                : collect();
        }

        // ----- AJAX so'rovini tekshirish -----
        if (request()->ajax()) {
            // Asosiy query (AJAX uchun)
            $query = Customer::with([
                'company', // Admin uchun kerak
                'street',  // Ko'cha nomi uchun kerak
                'waterMeter' // Hisoblagich va ko'rsatkich uchun kerak
            ])->select('customers.*') // DT bilan ishlaganda select() kerak bo'lishi mumkin
            ->where('is_active', 1);

            // **📌 Admin bo‘lmasa, faqat o‘z kompaniyasidagi mijozlarni olish**
            if (!$user->hasRole('admin') && $user->company) {
                $query->where('company_id', $user->company_id);
            }

            // ----- Tashqi filtrlarni qo'llash -----
            $searchText = request('search_text'); // JS dan keladigan nom
            $streetId = request('street_id');
            $debtFilter = request('debt');

            if ($searchText) {
                $query->where(function ($q) use ($searchText) {
                    $q->where('name', 'LIKE', "%{$searchText}%")
                        ->orWhere('phone', 'LIKE', "%{$searchText}%")
                        ->orWhere('account_number', 'LIKE', "%{$searchText}%");
                });
            }

            if ($streetId) {
                $query->where('street_id', $streetId);
            }

            if ($debtFilter == 'has_debt') {
                // Qarzdorlikni hisoblash uchun Invoice va Payment'lar kerak bo'ladi
                // Bu qismni optimallashtirish kerak bo'lishi mumkin, masalan, balansni DBda saqlash
                // Hozircha Customer modelidagi getBalanceAttribute ishlatilishiga tayanib ko'ramiz
                // Lekin bu har bir qator uchun alohida query chaqirishi mumkin!
                // Yaxshiroq yechim: balansni jadvalda saqlash yoki HAVING bilan ishlash
                // $query->where('balance', '<', 0); // Agar 'balance' ustuni DBda bo'lsa
                $query->withSum('invoices as total_due', 'amount_due')
                    ->withSum('payments as total_paid', 'amount')
                    ->havingRaw('IFNULL(total_due, 0) > IFNULL(total_paid, 0)'); // HAVING bilan ishlash ancha yaxshi
            }

            // ----- DataTables ga uzatish -----
            return DataTables::eloquent($query)
                ->addIndexColumn() // "N" ustuni uchun (DT_RowIndex)
                ->addColumn('company_name', function(Customer $customer) { // Admin uchun kompaniya
                    return $customer->company ? '<a href="'.route('companies.show', $customer->company->id).'" class="badge badge-outline text-blue">'.$customer->company->name.'</a>' : '-';
                })
                ->addColumn('street_name', function(Customer $customer) { // Ko'cha nomi
                    return $customer->street ? '<a href="'.route('streets.show', $customer->street->id).'" class="badge badge-outline text-blue">'.$customer->street->name.'</a>' : '-';
                })
                ->addColumn('name_status', function(Customer $customer){ // Ism va status
                    $statusBadge = $customer->is_active
                        ? '<span class="badge bg-cyan text-cyan-fg ms-1">Faol</span>'
                        : '<span class="badge bg-red text-red-fg ms-1">Nofaol</span>';
                    return e($customer->name) . $statusBadge; // e() - XSS himoyasi
                })
                ->addColumn('meter_link', function(Customer $customer) { // Hisoblagich linki
                    if ($customer->waterMeter) {
                        return '<a href="'.route('water_meters.show', $customer->waterMeter->id).'" class="badge badge-outline text-blue">'.e($customer->waterMeter->meter_number).'</a>';
                    }
                    return '<span class="text-muted">Hisoblagich yo‘q</span>';
                })
                ->addColumn('balance_formatted', function(Customer $customer){ // Balansni formatlash
                    // Modelda hisoblangan balansni olamiz (lekin bu sekin bo'lishi mumkin)
                    $balance = $customer->balance; // Model getBalanceAttribute ishlaydi
                    $balanceClass = $balance < 0 ? 'text-red' : ($balance > 0 ? 'text-green' : 'text-info');
                    return '<span class="badge '.$balanceClass.'">' . ($balance >= 0 ? '+' : '') . number_format($balance) . ' UZS</span>';
                })
                ->addColumn('last_reading', function(Customer $customer){ // Oxirgi ko'rsatkich
                    // Eager loading bilan olingan ma'lumotdan foydalanishga harakat qilamiz
                    return $customer->waterMeter?->readings?->first()?->reading ?? '—';
                     // Bu queryni optimallashtirish kerak bo'lishi mumkin
                })
                ->addColumn('actions', function(Customer $customer) { // Amallar tugmalari
                    $showUrl = route('customers.show', $customer->id);
                    $editUrl = route('customers.edit', $customer->id);
                    $deleteUrl = route('customers.destroy', $customer->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    return <<<HTML
                        <a href="{$showUrl}" class="btn btn-info btn-sm">Batafsil</a>
                        <a href="{$editUrl}" class="btn btn-warning btn-sm">Tahrirlash</a>
                        <form action="{$deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Haqiqatan ham o‘chirmoqchimisiz?')">
                            {$csrf}
                            {$method}
                            <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                        </form>
                    HTML;
                })
                ->filterColumn('company_name', function($query, $keyword) { // Kompaniya nomini qidirish (agar admin bo'lsa)
                    $query->whereHas('company', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('street_name', function($query, $keyword) { // Ko'cha nomini qidirish
                    $query->whereHas('street', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['company_name', 'street_name', 'name_status', 'meter_link', 'balance_formatted', 'actions']) // HTML ustunlar
                ->make(true); // JSON javobni qaytarish
        }

        // ----- Oddiy GET so'rov uchun (sahifa birinchi ochilganda) -----
        // Jami mijozlar sonini olish (boshlang'ich holat uchun)
        $customersQueryForCount = Customer::query()->where('is_active', 1);
        if (!$user->hasRole('admin') && $user->company) {
            $customersQueryForCount->where('company_id', $user->company_id);
        }
        $customersCount = $customersQueryForCount->count();

        // Faqat kerakli ma'lumotlarni view'ga uzatamiz
        return view('customers.index', compact('streets', 'customersCount'));
    }

    /**
     * Yangi mijoz qo‘shish formasi.
     */
    public function create()
    {
        $user = auth()->user();

        // Agar admin bo‘lsa, barcha kompaniyalarning mahallalarini oladi
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            // Oddiy foydalanuvchilar faqat o‘z kompaniyasining ma’lumotlarini ko‘radi
            $company = $user->company;
            if (!$company) {
                return redirect()->route('customers.index')->withErrors('Sizga hech qanday kompaniya bog‘lanmagan.');
            }
            $companies = Company::where('id', $company->id)->get(); // Faqat bitta kompaniya qaytadi
        }
        $streets = Street::all();

        return view('customers.create', compact('companies', 'streets'));
    }

    /**
     * Mijozni saqlash.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'company_id' => $user->hasRole('admin') ? 'required|exists:companies,id' : '',
            'street_id' => 'required|exists:streets,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'account_meter_number' => [
                'required',
                'string', // Yoki 'numeric' agar faqat son bo'lsa
                Rule::unique('customers', 'account_number'), // customers.account_number ga unique
                Rule::unique('water_meters', 'meter_number'), // water_meters.meter_number ga unique
            ],
            'family_members' => 'nullable|integer|min:1',
            'pdf_file' => 'nullable|file|mimes:pdf|max:4096',

            'installation_date' => 'nullable|date',
            'validity_period' => 'nullable|integer|min:1',

            'initial_reading' => 'required|numeric|min:0',
            'reading_date' => 'nullable|date',
        ];

        if (!$user->hasRole('admin')) {
            unset($rules['company_id']); // Admin bo'lmasa, validatsiyadan olib tashlaymiz
        }

        $validated = $request->validate($rules);

        // Oddiy foydalanuvchilar uchun kompaniyani avtomatik qo‘shish
        if (!$user->hasRole('admin')) {
            $validated['company_id'] = $user->company_id; // company_id ni qo'lda qo'shamiz
            if (!$validated['company_id']) {
                return redirect()->back()->withErrors(['msg' => 'Sizga kompaniya biriktirilmagan!'])->withInput();
            }
        }

        $accountMeterNumber = $validated['account_meter_number'];

        $customerData = [
            'company_id' => $validated['company_id'],
            'street_id' => $validated['street_id'],
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'account_number' => $accountMeterNumber, // Yagona qiymatni ishlatamiz
            'family_members' => $validated['family_members'],
            'is_active' => 1, // Har doim aktiv
            'has_water_meter' => 1, // Har doim hisoblagichli deb hisoblaymiz
            // Balans avtomatik hisoblanishi kerak (yoki 0 qilib boshlash mumkin)
            'balance' => 0,
        ];

        if ($request->hasFile('pdf_file')) {
            $validated['pdf_file'] = $request->file('pdf_file')->store('pdfs', 'public');
        }

        $customer = Customer::create($customerData);

        $installationDate = Carbon::now(); // Bugungi sana
        $validityPeriod = 8; // 8 yil
        $expirationDate = $installationDate->copy()->addYears($validityPeriod);

        $waterMeterData = [
            'customer_id' => $customer->id,
            'meter_number' => $accountMeterNumber, // Yagona qiymatni ishlatamiz
            'installation_date' => $installationDate->toDateString(), // Avto-to'ldirilgan sana
            'validity_period' => $validityPeriod, // Avto-to'ldirilgan muddat
            'expiration_date' => $expirationDate->toDateString(), // Hisoblangan sana
            // last_reading_date kerak emas, chunki MeterReading da bor
        ];

        $waterMeter = WaterMeter::create($waterMeterData);

        MeterReading::create([
            'water_meter_id' => $waterMeter->id,
            'reading' => $validated['initial_reading'],
            'reading_date' => $validated['reading_date'],
            'confirmed' => 1, // Birinchi o'qish avtomatik tasdiqlanadi
        ]);

        return redirect()->route('customers.index')->with('success', 'Mijoz muvaffaqiyatli qo‘shildi!');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'company',
            'street.neighborhood.city.region',
            'telegramAccounts'
        ]);

        $readings = $customer->waterMeter
            ? $customer->waterMeter->readings()->latest()->paginate(5, ['*'], 'reading_page')
            : new LengthAwarePaginator([], 0, 5, 1, ['path' => request()->url(), 'pageName' => 'reading_page']);
        $invoices = $customer->invoices()->latest()->paginate(5, ['*'], 'invoice_page');
        $payments = $customer->payments()->latest()->paginate(5, ['*'], 'payment_page');

//        // Agar PJAX orqali faqat readings so‘ralgan bo‘lsa
//        if (request()->has('reading_page')) {
//            return view('customers.partials.readings', compact('readings', 'customer'));
//        }
//
//        // Agar PJAX orqali faqat invoices so‘ralgan bo‘lsa
//        if (request()->has('invoice_page')) {
//            return view('customers.partials.invoices', compact('invoices', 'customer'));
//        }
//
//        // Agar PJAX orqali faqat payments so‘ralgan bo‘lsa
//        if (request()->has('payment_page')) {
//            return view('customers.partials.payments', compact('payments', 'customer'));
//        }

        return view('customers.show', compact('customer', 'readings', 'invoices', 'payments'));
    }

    /**
     * Mijozni tahrirlash.
     */
    public function edit(Customer $customer)
    {
        $user = auth()->user();

        // Agar admin bo‘lsa, barcha kompaniyalarning mahallalarini oladi
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            // Oddiy foydalanuvchilar faqat o‘z kompaniyasining ma’lumotlarini ko‘radi
            $company = $user->company;

            if (!$company) {
                return redirect()->route('customers.index')->withErrors('Sizga hech qanday kompaniya bog‘lanmagan.');
            }

            $companies = Company::where('id', $company->id)->get(); // Faqat bitta kompaniya qaytadi
        }
        $streets = Street::all();

        return view('customers.edit', compact('customer', 'companies', 'streets'));
    }

    /**
     * Mijoz ma’lumotlarini yangilash.
     */
    public function update(Request $request, Customer $customer)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'company_id' => $user->hasRole('admin') ? 'required|exists:companies,id' : '',
            'street_id' => 'required|exists:streets,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'required|string',
            'account_number' => 'required|unique:customers,account_number,' . $customer->id,
            'family_members' => 'nullable|integer|min:1',
            'pdf_file' => 'nullable|mimes:pdf|max:2048',
        ]);

        if ($request->hasFile('pdf_file')) {
            if ($customer->pdf_file) {
                Storage::disk('public')->delete($customer->pdf_file); // Eski PDFni o‘chirish
            }
            $customer->pdf_file = $request->file('pdf_file')->store('pdfs', 'public');
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['has_water_meter'] = $request->has('has_water_meter') ? 1 : 0;
        $validated['pdf_file'] = $customer->pdf_file;


        // Hisoblagich bo‘lsa, family_members null qilamiz
//        if ($request->has('has_water_meter')) {
//            $validated['family_members'] = null;
//        }

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Mijoz muvaffaqiyatli yangilandi!');
    }

    /**
     * Mijozni o‘chirish.
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        if ($customer->pdf_file) {
            Storage::disk('public')->delete($customer->pdf_file); // PDF faylni o‘chirish
        }
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Mijoz muvaffaqiyatli o‘chirildi!');
    }

    public function detachTelegramAccount(Request $request, $customerId, $telegramAccountId)
    {
        $customer = Customer::findOrFail($customerId);

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->back()->withErrors('Sizda bu amalni bajarish uchun ruxsat yo‘q.');
        }

        $telegramAccount = $customer->telegramAccounts()->where('telegram_accounts.id', $telegramAccountId)->first();

        if (!$telegramAccount) {
            return redirect()->back()->withErrors('Telegram akkaunt topilmadi.');
        }

        // 🔴 Telegram akkauntni ajratish
        $customer->telegramAccounts()->detach($telegramAccountId);
        cache()->forget("active_customer_id_{$telegramAccount->telegram_chat_id}");

        // 📩 Telegram orqali bildirish yuborish
        $this->notifyTelegramAccountDeleted($telegramAccount->telegram_chat_id, $customer->account_number);

        return redirect()->back()->with('success', 'Telegram akkaunt muvaffaqiyatli uzildi.');
    }

    /**
     * 📩 Telegram akkauntga hisob o‘chirilgani haqida bildirish
     */
    private function notifyTelegramAccountDeleted($telegramChatId, $accountNumber)
    {
        Telegram::sendMessage([
            'chat_id' => $telegramChatId,
            'text' => "🚨 Sizning <b>{$accountNumber}</b> hisob raqamingiz botdan o‘chirildi.\n🔢 Yangi hisob raqamini kiriting.",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['remove_keyboard' => true]), // ❌ Menyuni olib tashlash
        ]);
    }

}
