<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BasicExcelImport; // Import klassini chaqirish
use Illuminate\Support\Facades\DB; // Tranzaksiya uchun
use Illuminate\Support\Facades\Validator; // Validatsiya uchun
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Imports\CustomersImport;
use App\Exports\CustomersExport;
use App\Models\MeterReading;
use App\Models\Street;
use App\Models\Tariff;
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
use Carbon\Carbon;

class CustomerController extends Controller
{
    /**
     * Mijozlar ro‘yxati.
     */
    public function index()
    {
        $user = Auth::user();

        // Bu ma'lumotlar sahifa birinchi marta ochilganda tashqi filtrlar uchun kerak bo'ladi
        $companies = collect();
        if ($user->hasRole('admin')) {
            $companies = Company::orderBy('name')->get();
        }

        $streets = collect();
        if ($user->hasRole('admin')) {
            // Admin uchun barcha kompaniyalarga tegishli ko'chalarni chiqarish
            $streets = Street::with('neighborhood.city.region', 'company')->orderBy('name')->get();
        } else {
            // Admin bo'lmagan foydalanuvchi uchun faqat o'z kompaniyasining ko'chalari
            $streets = $user->company ? Street::where('company_id', $user->company->id)->with('neighborhood.city.region')->get() : collect();
        }

        // ----- AJAX so'rovini tekshirish -----
        if (request()->ajax()) {
            // Asosiy query (AJAX uchun)
            $query = Customer::with([
                'company', // Admin uchun kerak
                'street',  // Ko'cha nomi uchun kerak
                'waterMeter' // Hisoblagich va ko'rsatkich uchun kerak
            ])->select('customers.*') // DT bilan ishlaganda select() kerak bo'lishi mumkin
            ->where('customers.is_active', 1);

            // **📌 Admin bo‘lmasa, faqat o‘z kompaniyasidagi mijozlarni olish**
            if (!$user->hasRole('admin') && $user->company_id) {
                $query->where('company_id', $user->company_id);
            }

            // ----- Tashqi filtrlarni qo'llash -----
            $searchText = request('search_text');
            $streetId = request('street_id');
            $debtFilter = request('debt');
            $companyId = request('company_id'); // Kompaniya filtrini requestdan olish

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
                $query->withSum('invoices as total_due', 'amount_due')
                    ->withSum('payments as total_paid', 'amount')
                    ->havingRaw('IFNULL(total_due, 0) > IFNULL(total_paid, 0)');
            }

            // Admin tanlagan kompaniya bo'yicha filtrlash
            if ($user->hasRole('admin') && !empty($companyId)) {
                $query->where('company_id', $companyId);
            }

            // ----- DataTables ga uzatish -----
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('company_name', function (Customer $customer) {
                    return $customer->company ? '<a href="' . route('companies.show', $customer->company->id) . '" class="badge badge-outline text-blue">' . e($customer->company->name) . '</a>' : '-';
                })
                ->addColumn('street_name', function (Customer $customer) {
                    return $customer->street ? '<a href="' . route('streets.show', $customer->street->id) . '" class="badge badge-outline text-blue">' . e($customer->street->name) . '</a>' : '-';
                })
                ->addColumn('name_status', function (Customer $customer) {
                    $nameLink = '<a href="' . route('customers.show', $customer->id) . '" class="badge badge-outline text-blue">'. e($customer->name) . '</a>';
                    $statusBadge = $customer->is_active
                        ? '<span class="badge bg-cyan text-cyan-fg ms-1">Faol</span>'
                        : '<span class="badge bg-red text-red-fg ms-1">Nofaol</span>';
                    return $nameLink . $statusBadge;
                })
                ->addColumn('balance_formatted', function (Customer $customer) {
                    $balance = $customer->balance;
                    $balanceClass = $balance < 0 ? 'text-red' : ($balance > 0 ? 'text-green' : 'text-info');
                    return '<span class="badge ' . $balanceClass . '">' . ($balance >= 0 ? '+' : '') . number_format($balance) . ' UZS</span>';
                })
                ->addColumn('last_reading', function (Customer $customer) {
                    if (!$customer->waterMeter) {
                        return '—';
                    }
                    $lastConfirmedReading = $customer->waterMeter->readings()
                        ->where('confirmed', true)
                        ->orderBy('reading_date', 'desc') // reading_date bo'yicha saralash yaxshiroq
                        ->orderBy('id', 'desc')
                        ->first();
                    return $lastConfirmedReading ? $lastConfirmedReading->reading : '—';
                })
                ->addColumn('actions', function (Customer $customer) {
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
                ->filterColumn('company_name', function ($query, $keyword) {
                    $query->whereHas('company', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('street_name', function ($query, $keyword) {
                    $query->whereHas('street', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['company_name', 'street_name', 'name_status', 'balance_formatted', 'actions'])
                ->make(true);
        }

        // ----- Oddiy GET so'rov uchun (sahifa birinchi ochilganda) -----
        $customersQueryForCount = Customer::query()->where('customers.is_active', 1);
        if (!$user->hasRole('admin') && $user->company) {
            $customersQueryForCount->where('company_id', $user->company_id);
        }
        $customersCount = $customersQueryForCount->count();

        // Faqat kerakli ma'lumotlarni view'ga uzatamiz
        return view('customers.index', compact('streets', 'customersCount', 'companies'));
    }

    /**
     * Yangi mijoz qo‘shish formasi.
     */
    public function create()
    {
        $user = auth()->user();
        $companies = collect(); // Bo'sh kolleksiya bilan initsializatsiya qilamiz
        $streets = collect();   // Bo'sh kolleksiya bilan initsializatsiya qilamiz

        // Agar admin bo‘lsa, barcha kompaniyalarning mahallalarini oladi
        if ($user->hasRole('admin')) {
            $companies = Company::all();
            $streets = Street::all();
        } else {
            if (!$user->company_id) {
                // Agar foydalanuvchiga kompaniya biriktirilmagan bo'lsa, xatolik yoki bosh sahifaga yo'naltirish
                return redirect()->route('dashboard') // Yoki customers.index
                ->with('error', 'Sizga kompaniya biriktirilmagan. Mijoz qo\'sha olmaysiz.');
            }
            // Faqat o'z kompaniyasini olamiz (formada ko'rsatish yoki yashirincha ishlatish uchun)
            $companies = Company::where('id', $user->company_id)->get();
            // Faqat o'z kompaniyasiga tegishli ko'chalarni olamiz
            $streets = Street::where('company_id', $user->company_id)
                ->with('neighborhood.city.region')
                ->orderBy('name', 'asc')
                ->get();
        }

        return view('customers.create', compact('companies', 'streets'));
    }

    /**
     * Mijozni saqlash.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $hasWaterMeter = $request->boolean('has_water_meter');

        if ($request->has('account_meter_number')) {
            $cleanedAccountMeterNumber = str_replace(' ', '', $request->input('account_meter_number'));
            $request->merge(['account_meter_number' => $cleanedAccountMeterNumber]);
        }

        $rules = [
            'company_id' => $user->hasRole('admin') ? 'required|exists:companies,id' : '',
            'street_id' => 'required|exists:streets,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'account_meter_number' => [
                'required',
                'string',
                'max:50', // Bazadagi ustun turiga moslang
                Rule::unique('customers', 'account_number'), // customers jadvalida unikal bo'lishi shart
            ],
            'family_members' => $hasWaterMeter ? 'nullable|integer|min:1' : 'required|integer|min:1', // Shartli majburiy
            'has_water_meter' => 'nullable|boolean',
            'initial_reading' => ['nullable', Rule::requiredIf($hasWaterMeter), 'numeric', 'min:0'],
            'reading_date' => ['nullable', Rule::requiredIf($hasWaterMeter), 'date'],
        ];

        if ($hasWaterMeter) {
            $rules['account_meter_number'][] = Rule::unique('water_meters', 'meter_number');
        }

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

        // 1. Mijoz qo'shilayotgan kompaniyani topamiz
        $company = Company::with('plan')->find($validated['company_id']);

        if (!$company) {
            // Bu holat deyarli yuz bermaydi, chunki validatsiyada 'exists:companies' bor, lekin ehtiyot shart
            return back()->with('error', 'Kompaniya topilmadi.')->withInput();
        }

        // 2. Kompaniyaning tarif rejasida limit borligini tekshiramiz
        // customer_limit > 0 bo'lsa, bu cheklangan tarif degani (0 yoki null - cheklanmagan)
        if ($company->plan && $company->plan->customer_limit > 0) {

            // 3. Kompaniyaning hozirgi mijozlari sonini sanaymiz
            $currentCustomerCount = $company->customers()->count();

            // 4. Joriy sonni limit bilan solishtiramiz
            if ($currentCustomerCount >= $company->plan->customer_limit) {
                // Agar limitga yetgan bo'lsa, xatolik bilan ortga qaytaramiz
                return redirect()->back()
                    ->withErrors(['limit_error' => 'Siz o\'z tarif rejangizdagi mijozlar limitiga yetdingiz. Cheklov: ' . $company->plan->customer_limit . ' ta. Tarifni yangilash uchun administratorga murojaat qiling.'])
                    ->withInput();
            }
        }

        $accountMeterNumber = $validated['account_meter_number'];

        $customerData = [
            'company_id' => $validated['company_id'] ?? $user->company_id,
            'street_id' => $validated['street_id'],
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'account_number' => $accountMeterNumber,
            'family_members' => $validated['family_members'],
            'is_active' => 1,
            'has_water_meter' => $hasWaterMeter,
            'balance' => 0,
        ];

        $customer = Customer::create($customerData);

        if ($hasWaterMeter) {
            $installationDate = Carbon::now();
            $validityPeriod = 8;
            $expirationDate = $installationDate->copy()->addYears($validityPeriod);

            $waterMeterData = [
                'customer_id' => $customer->id,
                'meter_number' => $accountMeterNumber,
                'installation_date' => $installationDate->toDateString(),
                'validity_period' => $validityPeriod,
                'expiration_date' => $expirationDate->toDateString(),
            ];

            $waterMeter = WaterMeter::create($waterMeterData);

            MeterReading::create([
                'water_meter_id' => $waterMeter->id,
                'reading' => $validated['initial_reading'],
                'reading_date' => $validated['reading_date'],
                'confirmed' => 1,
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Mijoz muvaffaqiyatli qo‘shildi!');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'company',
            'street.neighborhood.city.region',
            'telegramAccounts',
            'createdBy',
            'updatedBy',
        ]);

        $readings = $customer->waterMeter
            ? $customer->waterMeter->readings()
                ->latest()
                ->orderBy('id', 'desc')
                ->paginate(5, ['*'], 'reading_page')
            : new LengthAwarePaginator([], 0, 5, 1, ['path' => request()->url(), 'pageName' => 'reading_page']);
        $invoices = $customer->invoices()->latest()->paginate(5, ['*'], 'invoice_page');
        $payments = $customer->payments()->latest()->paginate(5, ['*'], 'payment_page');

        $activeTariffs = collect(); // Bo'sh kolleksiya bilan boshlaymiz
        if ($customer->company_id) { // Agar mijoz kompaniyaga biriktirilgan bo'lsa
            $activeTariffs = Tariff::where('company_id', $customer->company_id)
                ->where('is_active', true)
                ->orderBy('name') // Yoki valid_from bo'yicha saralash
                ->get();
        }

        return view('customers.show', compact(
            'customer',
            'readings',
            'invoices',
            'payments',
            'activeTariffs'
        ));
    }

    /**
     * Mijozni tahrirlash.
     */
    public function edit(Customer $customer)
    {
        $user = auth()->user();

        $customer->loadMissing(['company', 'street']);
        $companies = collect();
        $streets = collect();

        // Agar admin bo‘lsa, barcha kompaniyalarning mahallalarini oladi
        if ($user->hasRole('admin')) {
            $companies = Company::all();
            $streets = Street::all();
        } else {
            // Oddiy foydalanuvchilar faqat o‘z kompaniyasining ma’lumotlarini ko‘radi
            if ($customer->company_id != $user->company_id) {
                return redirect()->route('customers.index')->with('error', 'Siz faqat o\'z kompaniyangiz mijozlarini tahrirlay olasiz.');
            }

            // Kompaniyasi o'zgarmaydi, faqat o'zining kompaniyasi
            $companies = Company::where('id', $user->company_id)->get();
            // Faqat o'z kompaniyasiga tegishli ko'chalar
            $streets = Street::where('company_id', $user->company_id)
                ->with('neighborhood.city.region')
                ->orderBy('name')->get();
        }

        return view('customers.edit', compact('customer', 'companies', 'streets'));
    }

    /**
     * Mijoz ma’lumotlarini yangilash.
     */
    public function update(Request $request, Customer $customer)
    {
        $user = auth()->user();

        if ($request->has('account_number')) {
            $cleanedAccountNumber = str_replace(' ', '', $request->input('account_number'));
            $request->merge(['account_number' => $cleanedAccountNumber]);
        }

        $validated = $request->validate([
            'company_id' => $user->hasRole('admin') ? 'required|exists:companies,id' : '',
            'street_id' => 'required|exists:streets,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'required|string',
            'account_number' => 'required|string|max:20|unique:customers,account_number,' . $customer->id,
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

    public function showImportForm()
    {
        // Bu yerda adminlar uchun ekanligini tekshirish (Policy yoki middleware)
        return view('customers.import'); // Yangi view fayli
    }

    public function handleImportNoMeter(Request $request)
    {
        $request->validate(['excel_file' => 'required|mimes:xlsx,xls,csv|max:10240']);
        $file = $request->file('excel_file');
        $importErrors = [];
        DB::beginTransaction();
        try {
            $rows = Excel::toCollection(new BasicExcelImport, $file)->first();
            if ($rows->isEmpty()) { throw new \Exception("Excel fayl bo'sh."); }

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $rowData = $row->toArray();

                $preparedData = [
                    'kompaniya_id' => $rowData['kompaniya_id'] ?? null,
                    'kocha_id' => $rowData['kocha_id'] ?? null,
                    'fio' => $rowData['fio'] ?? null,
                    'telefon_raqami' => $rowData['telefon_raqami'] ?? null,
                    'uy_raqami' => $rowData['uy_raqami'] ?? null,
                    'hisob_raqam' => $rowData['hisob_raqam'] ?? null,
                    'oila_azolari' => $rowData['oila_azolari'] ?? null,
                ];

                $validator = Validator::make($preparedData, [
                    'kompaniya_id' => ['required', 'integer', 'exists:companies,id'],
                    'kocha_id' => ['required', 'integer', 'exists:streets,id'],
                    'fio' => ['required', 'string', 'max:255'],
                    'hisob_raqam' => ['required', 'integer', Rule::unique('customers', 'account_number')],
                    'oila_azolari' => ['required', 'integer', 'min:1'],
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $error) { $importErrors[] = "{$rowNumber}-qatorda xatolik: " . $error; }
                    continue;
                }

                $validatedData = $validator->validated();
                Customer::create([
                    'company_id'       => $validatedData['kompaniya_id'],
                    'street_id'        => $validatedData['kocha_id'],
                    'name'             => $validatedData['fio'],
                    'phone'            => $preparedData['telefon_raqami'],
                    'address'          => $preparedData['uy_raqami'],
                    'account_number'   => $validatedData['hisob_raqam'],
                    'has_water_meter'  => false,
                    'family_members'   => $validatedData['oila_azolari'],
                    'is_active'        => true,
                    'balance'          => 0,
                ]);
            }

            if (!empty($importErrors)) {
                // --- MANA SHU BLOK TUZATILDI ---
                $finalValidator = Validator::make([], []); // Bo'sh validator yaratamiz
                foreach ($importErrors as $errorMsg) {
                    $finalValidator->errors()->add('excel_error', $errorMsg); // Unga xatolarni qo'shamiz
                }
                throw new \Illuminate\Validation\ValidationException($finalValidator); // Keyin exception chaqiramiz
                // --- TUZATISH TUGADI ---
            }

            DB::commit();
            return redirect()->route('customers.import.form')->with('success', 'Hisoblagichsiz mijozlar muvaffaqiyatli import qilindi!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel import error (No Meter): ' . $e->getMessage());
            return redirect()->back()->withErrors(['umumiy_xato' => 'Import qilishda xatolik yuz berdi: ' . $e->getMessage()])->withInput();
        }
    }

    public function handleImportWithMeter(Request $request)
    {
        $request->validate(['excel_file' => 'required|mimes:xlsx,xls,csv|max:10240']);
        $file = $request->file('excel_file');
        $importErrors = [];
        DB::beginTransaction();
        try {
            $rows = Excel::toCollection(new BasicExcelImport, $file)->first();
            if ($rows->isEmpty()) { throw new \Exception("Excel fayl bo'sh."); }

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $rowData = $row->toArray();

                $preparedData = [
                    'kompaniya_id' => $rowData['kompaniya_id'] ?? null,
                    'kocha_id' => $rowData['kocha_id'] ?? null,
                    'fio' => $rowData['fio'] ?? null,
                    'telefon_raqami' => isset($rowData['telefon_raqami']) ? (string)$rowData['telefon_raqami'] : null,
                    'uy_raqami'      => isset($rowData['uy_raqami']) ? (string)$rowData['uy_raqami'] : null,
                    'hisob_raqam' => $rowData['hisob_raqam'] ?? null,
                    'hisoblagich_ornatilgan_sana' => $rowData['hisoblagich_ornatilgan_sana'] ?? null,
                    'amal_qilish_muddati' => $rowData['amal_qilish_muddati'] ?? null,
                    'boshlangich_korsatkich' => $rowData['boshlangich_korsatkich'] ?? null,
                    'korsatkich_sanasi' => $rowData['korsatkich_sanasi'] ?? null,
                    'oila_azolari' => $rowData['oila_azolari'] ?? null,
                ];

                if (!empty($preparedData['hisoblagich_ornatilgan_sana'])) {
                    $dateValue = $preparedData['hisoblagich_ornatilgan_sana'];
                    if (is_numeric($dateValue) && strlen((string)$dateValue) === 4) { // Agar faqat yil kiritilgan bo'lsa
                        $preparedData['hisoblagich_ornatilgan_sana'] = Carbon::createFromDate((int)$dateValue, 1, 1)->format('Y-m-d');
                    } else { // Agar to'liq sana (Excel formati yoki matn) bo'lsa
                        try {
                            $preparedData['hisoblagich_ornatilgan_sana'] = Carbon::instance(ExcelDate::excelToDateTimeObject($dateValue))->format('Y-m-d');
                        } catch (\Throwable $th) {
                            $preparedData['hisoblagich_ornatilgan_sana'] = null; // Format noto'g'ri bo'lsa
                        }
                    }
                }

                if (!empty($preparedData['korsatkich_sanasi'])) {
                    try {
                        $preparedData['korsatkich_sanasi'] = Carbon::instance(ExcelDate::excelToDateTimeObject($preparedData['korsatkich_sanasi']))->format('Y-m-d');
                    } catch (\Throwable $th) { $preparedData['korsatkich_sanasi'] = null; }
                } else {
                    $preparedData['korsatkich_sanasi'] = now()->format('Y-m-d');
                }

                $validator = Validator::make($preparedData, [
                    'kompaniya_id' => ['required', 'integer', 'exists:companies,id'],
                    'kocha_id' => ['required', 'integer', 'exists:streets,id'],
                    'fio' => ['required', 'string', 'max:255'],
                    'hisob_raqam' => ['required','integer', Rule::unique('customers', 'account_number'), Rule::unique('water_meters', 'meter_number')],
                    'boshlangich_korsatkich' => ['required', 'numeric', 'min:0'],
                    'amal_qilish_muddati' => ['nullable', 'integer', 'min:0'],
                    'hisoblagich_ornatilgan_sana' => ['nullable', 'date'],
                    'telefon_raqami' => ['nullable', 'string', 'max:30'],
                    'uy_raqami' => ['nullable', 'string', 'max:255'],
                    'oila_azolari' => ['nullable', 'integer', 'min:0'],
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $error) { $importErrors[] = "{$rowNumber}-qatorda xatolik: " . $error; }
                    continue;
                }

                $validatedData = $validator->validated();

                $customer = Customer::create([
                    'company_id' => $validatedData['kompaniya_id'],
                    'street_id' => $validatedData['kocha_id'],
                    'name' => $validatedData['fio'],
                    'account_number' => $validatedData['hisob_raqam'],

                    'phone' => $validatedData['telefon_raqami'],
                    'address' => $validatedData['uy_raqami'],
                    'family_members'   => $validatedData['oila_azolari'],

                    'has_water_meter' => true,
                    'is_active' => true,
                    'balance' => 0,
                ]);

                $installationDate = isset($validatedData['hisoblagich_ornatilgan_sana']) ? Carbon::parse($validatedData['hisoblagich_ornatilgan_sana']) : Carbon::now();

                $validityPeriod = (int)($validatedData['amal_qilish_muddati'] ?? 8);

                $waterMeter = $customer->waterMeter()->create([
                    'meter_number'      => $validatedData['hisob_raqam'],
                    'installation_date' => $installationDate->toDateString(),
                    'validity_period'   => $validityPeriod,

                    // 1. expiration_date ni hisoblaymiz: installationDate + validityPeriod (8 yil)
                    'expiration_date'   => $installationDate->copy()->addYears($validityPeriod)->toDateString(),
                ]);

                MeterReading::create([
                    'water_meter_id' => $waterMeter->id,
                    'reading'        => $validatedData['boshlangich_korsatkich'],
                    'reading_date'   => now()->toDateString(),
                    'confirmed'      => true,
                ]);
            }

            if (!empty($importErrors)) {
                // --- MANA SHU BLOK TUZATILDI ---
                $finalValidator = Validator::make([], []);
                foreach ($importErrors as $errorMsg) {
                    $finalValidator->errors()->add('excel_error', $errorMsg);
                }
                throw new \Illuminate\Validation\ValidationException($finalValidator);
                // --- TUZATISH TUGADI ---
            }

            DB::commit();
            return redirect()->route('customers.import.form')->with('success', 'Hisoblagichli mijozlar muvaffaqiyatli import qilindi!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel import error (With Meter): ' . $e->getMessage());
            return redirect()->back()->withErrors(['umumiy_xato' => 'Import qilishda xatolik yuz berdi: ' . $e->getMessage()])->withInput();
        }
    }

    public function export(Request $request)
    {
        $companyId = $request->query('company_id');

        // Foydalanuvchi huquqini tekshirish (ixtiyoriy, lekin tavsiya etiladi)
        if (!auth()->user()->hasRole('admin') && auth()->user()->company_id != $companyId) {
            abort(403, 'Sizda bu kompaniya ma\'lumotlarini yuklab olishga ruxsat yo\'q.');
        }

        $fileName = 'mijozlar_royxati_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new CustomersExport($companyId), $fileName);
    }
}
