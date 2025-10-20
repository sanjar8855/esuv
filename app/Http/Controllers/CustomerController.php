<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
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

        $companies = $user->hasRole('admin')
            ? Company::orderBy('name')->get()
            : Company::where('id', $user->company_id)->get();

        // ✅ 1. Streets ni to'g'ri qo'shamiz
        if ($user->hasRole('admin')) {
            $streets = Street::with('neighborhood.city.region', 'company')
                ->orderBy('name')
                ->get();
        } else {
            $streets = Street::where('company_id', $user->company_id)
                ->with('neighborhood.city.region')
                ->orderBy('name')
                ->get();
        }

        // ----- AJAX so'rovini tekshirish -----
        if (request()->ajax()) {
            // ✅ 2. Eager Loading - readings ham yuklash
            $query = Customer::with([
                'company',
                'street',
                'waterMeter.readings' => function($q) {
                    $q->where('confirmed', true)
                        ->orderBy('reading_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->limit(1);
                }
            ])->select('customers.*')
                ->where('customers.is_active', 1);

            // ----- Tashqi filtrlarni qo'llash -----
            $searchText = request('search_text');
            $streetId = request('street_id');
            $debtFilter = request('debt');
            $companyId = request('company_id');

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
                // ✅ 3. Last reading tuzatildi - qo'shimcha query yo'q
                ->addColumn('last_reading', function (Customer $customer) {
                    if (!$customer->waterMeter || $customer->waterMeter->readings->isEmpty()) {
                        return '—';
                    }
                    $lastConfirmedReading = $customer->waterMeter->readings->first();
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
                <form action="{$deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Haqiqatan ham o'chirmoqchimisiz?')">
                    {$csrf}
                    {$method}
                    <button type="submit" class="btn btn-danger btn-sm">O'chirish</button>
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

        return view('customers.index', compact('streets', 'customersCount', 'companies'));
    }

    /**
     * Yangi mijoz qo‘shish formasi.
     */
    public function create()
    {
        $user = auth()->user();

        if (!$user->company_id && !$user->hasRole('admin')) {
            return redirect()->route('dashboard')
                ->with('error', 'Sizga kompaniya biriktirilmagan. Mijoz qo\'sha olmaysiz.');
        }

        // ✅ Eager Loading - barcha relation lar
        if ($user->hasRole('admin')) {
            $companies = Company::orderBy('name')->get();

            // ✅ Admin uchun HAM eager loading qo'shamiz!
            $streets = Street::with([
                'neighborhood.city.region',
                'company'  // ✅ Company ham kerak
            ])->orderBy('name')->get();

        } else {
            $companies = Company::where('id', $user->company_id)->get();

            $streets = Street::where('company_id', $user->company_id)
                ->with('neighborhood.city.region')
                ->orderBy('name')
                ->get();
        }

        return view('customers.create', compact('companies', 'streets'));
    }

    /**
     * Mijozni saqlash.
     */
    public function store(StoreCustomerRequest $request)
    {
        // ✅ Validatsiya avtomatik bajarilgan!
        // ✅ account_meter_number tozalangan!
        $validated = $request->validated();
        $user = auth()->user();
        $hasWaterMeter = $request->boolean('has_water_meter');

        // ✅ Admin bo'lmasa - company_id avtomatik
        if (!$user->hasRole('admin')) {
            $validated['company_id'] = $user->company_id;
        }

        // ✅ Tarif rejasi limitini tekshirish
        $company = Company::with('plan')->find($validated['company_id']);

        if ($company->plan && $company->plan->customer_limit > 0) {
            $currentCount = $company->customers()->count();

            if ($currentCount >= $company->plan->customer_limit) {
                return back()->withErrors([
                    'limit_error' => "Tarif rejasidagi limit tugadi ({$company->plan->customer_limit} ta). Administratorga murojaat qiling."
                ])->withInput();
            }
        }

        // ✅ Mijoz yaratish
        $customer = Customer::create([
            'company_id' => $validated['company_id'],
            'street_id' => $validated['street_id'],
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'account_number' => $validated['account_meter_number'],
            'family_members' => $validated['family_members'] ?? null,
            'is_active' => true,
            'has_water_meter' => $hasWaterMeter,
            'balance' => 0,
        ]);

        // ✅ Hisoblagich yaratish (agar bor bo'lsa)
        if ($hasWaterMeter) {
            $installationDate = Carbon::now();
            $validityPeriod = config('water_meter.default_validity_period', 8);

            $waterMeter = WaterMeter::create([
                'customer_id' => $customer->id,
                'meter_number' => $validated['account_meter_number'],
                'installation_date' => $installationDate->toDateString(),
                'validity_period' => $validityPeriod,
                'expiration_date' => $installationDate->copy()->addYears($validityPeriod)->toDateString(),
            ]);

            MeterReading::create([
                'water_meter_id' => $waterMeter->id,
                'reading' => $validated['initial_reading'],
                'reading_date' => $validated['reading_date'],
                'confirmed' => true,
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Mijoz muvaffaqiyatli qo\'shildi!');
    }

    public function show(Customer $customer)
    {
        // ✅ EAGER LOADING - N+1 muammosini oldini olish
        $customer->load([
            'company',
            'street.neighborhood.city.region',
            'waterMeter.readings' => function($query) {
                $query->latest()->limit(1);  // Faqat eng oxirgi ko'rsatkich
            },
            'telegramAccounts',
            'createdBy',
            'updatedBy'
        ]);

        // ✅ ROL TEKSHIRUVI
        $user = auth()->user();
        $isCompanyOwner = $user->hasRole('company_owner') || $user->hasRole('admin');

        // ✅ HISOBLAGICH KO'RSATGICHLARI (Pagination bilan)
        $readings = $customer->waterMeter
            ? $customer->waterMeter->readings()
                ->with(['createdBy', 'updatedBy'])  // ✅ Kim yaratgan/yangilagan
                ->orderBy('reading_date', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(5, ['*'], 'reading_page')
            : new LengthAwarePaginator([], 0, 5, 1, [
                'path' => request()->url(),
                'pageName' => 'reading_page'
            ]);

        // ✅ INVOICELAR (Pagination bilan)
        $invoices = $customer->invoices()
            ->with(['tariff', 'createdBy'])  // ✅ Tariff va kim yaratgan
            ->orderBy('billing_period', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(5, ['*'], 'invoice_page');

        // ✅ TO'LOVLAR (Pagination bilan + Conditional Loading)
        $paymentsQuery = $customer->payments()
            ->with('invoice');  // Invoice har doim yuklanadi

        // ✅ Faqat ustun mavjud bo'lsa yuklash (Migration bajarilmagan bo'lsa xatolik bermasin)
        if (\Schema::hasColumn('payments', 'created_by')) {
            $paymentsQuery->with('createdBy');
        }

        if (\Schema::hasColumn('payments', 'updated_by')) {
            $paymentsQuery->with('updatedBy');
        }

        if (\Schema::hasColumn('payments', 'confirmed_by')) {
            $paymentsQuery->with('confirmedBy');
        }

        $payments = $paymentsQuery
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15, ['*'], 'payment_page');

        // ✅ AKTIV TARIFLAR (To'lov formasi uchun)
        $activeTariffs = collect(); // Bo'sh kolleksiya
        if ($customer->company_id) {
            $activeTariffs = Tariff::where('company_id', $customer->company_id)
                ->where('is_active', true)
                ->orderBy('valid_from', 'desc')
                ->orderBy('name')
                ->get();
        }

        return view('customers.show', compact(
            'customer',
            'readings',
            'invoices',
            'payments',
            'activeTariffs',
            'isCompanyOwner'
        ));
    }

    /**
     * Mijozni tahrirlash.
     */
    public function edit(Customer $customer)
    {
        $user = auth()->user();

        $customer->loadMissing(['company', 'street']);

        if ($user->hasRole('admin')) {
            $companies = Company::orderBy('name')->get();
            $streets = Street::with([
                'neighborhood.city.region',
                'company'
            ])->orderBy('name')->get();
        } else {
            $companies = Company::where('id', $user->company_id)->get();
            $streets = Street::where('company_id', $user->company_id)
                ->with('neighborhood.city.region')
                ->orderBy('name')
                ->get();
        }

        return view('customers.edit', compact('customer', 'companies', 'streets'));
    }

    /**
     * Mijoz ma’lumotlarini yangilash.
     */
    // ✅ YANGI
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $validated = $request->validated();

        // ✅ 1. PDF fayl bilan ishlash (agar yangi fayl yuklangan bo'lsa)
        if ($request->hasFile('pdf_file')) {
            // Eski faylni o'chirish
            if ($customer->pdf_file) {
                Storage::disk('public')->delete($customer->pdf_file);
            }
            // ✅ Yangi faylni $validated ga qo'shish
            $validated['pdf_file'] = $request->file('pdf_file')->store('pdfs', 'public');
        }

        // ✅ 2. Boolean qiymatlar
        $validated['is_active'] = $request->boolean('is_active');
        $validated['has_water_meter'] = $request->boolean('has_water_meter');

        // ✅ 3. Yangilash
        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Mijoz muvaffaqiyatli yangilandi!');
    }

    /**
     * Mijozni o‘chirish.
     */
    public function destroy(Customer $customer)
    {
        // ✅ 1. Ruxsat tekshiruvi (Policy orqali)
        $this->authorize('delete', $customer);

        // ✅ 2. O'chirish (cascade yoki model event ishlaydi)
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Mijoz va unga tegishli barcha ma\'lumotlar muvaffaqiyatli o\'chirildi!');
    }

    public function detachTelegramAccount(Request $request, $customerId, $telegramAccountId)
    {
        $customer = Customer::findOrFail($customerId);

        // ✅ 1. Ruxsat tekshiruvi
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->back()
                ->with('error', 'Sizda bu amalni bajarish uchun ruxsat yo\'q.');
        }

        // ✅ 2. Telegram akkauntni topish
        $telegramAccount = $customer->telegramAccounts()
            ->where('telegram_accounts.id', $telegramAccountId)
            ->first();

        if (!$telegramAccount) {
            return redirect()->back()
                ->with('error', 'Telegram akkaunt topilmadi.');
        }

        // ✅ 3. Akkauntni ajratish
        $customer->telegramAccounts()->detach($telegramAccountId);

        // ✅ 4. Cache dan o'chirish
        cache()->forget("active_customer_id_{$telegramAccount->telegram_chat_id}");

        // ✅ 5. Telegram orqali xabar yuborish (xatosiz)
        $this->notifyTelegramAccountDeleted(
            $telegramAccount->telegram_chat_id,
            $customer->account_number
        );

        return redirect()->back()
            ->with('success', 'Telegram akkaunt muvaffaqiyatli uzildi.');
    }

    /**
     * 📩 Telegram akkauntga hisob o‘chirilgani haqida bildirish
     */
    private function notifyTelegramAccountDeleted($telegramChatId, $accountNumber)
    {
        try {
            Telegram::sendMessage([
                'chat_id' => $telegramChatId,
                'text' => "🚨 Sizning <b>{$accountNumber}</b> hisob raqamingiz botdan o'chirildi.\n🔢 Yangi hisob raqamini kiriting.",
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode(['remove_keyboard' => true]),
            ]);
        } catch (\Exception $e) {
            // ✅ Xatoni log ga yozish (lekin metodning davom etishiga xalaqit bermaydi)
            \Log::warning('Telegram notification failed', [
                'chat_id' => $telegramChatId,
                'account_number' => $accountNumber,
                'error' => $e->getMessage()
            ]);

            // ✅ Davom etish (akkaunt ajratiladi, faqat notification yuborilmaydi)
        }
    }

    public function showImportForm()
    {
        // Faqat admin va company_owner kirishi mumkin
        if (!auth()->user()->hasAnyRole(['admin', 'company_owner'])) {
            return redirect()->route('customers.index')
                ->with('error', 'Sizda import sahifasiga kirish uchun ruxsat yo\'q.');
        }

        return view('customers.import');
    }

    public function handleImportNoMeter(Request $request)
    {
        return $this->processImport($request, false);
    }

    /**
     * ✅ Hisoblagichli mijozlar import (public metod)
     */
    public function handleImportWithMeter(Request $request)
    {
        return $this->processImport($request, true);
    }

    /**
     * ✅ Umumiy import logikasi (YAXSHILANDI: Partial success)
     */
    private function processImport(Request $request, bool $hasWaterMeter)
    {
        // Validatsiya
        $maxFileSize = config('water_meter.import_max_file_size', 10) * 1024; // KB ga o'tkazish
        $request->validate([
            'excel_file' => "required|file|mimes:xlsx,xls,csv|max:{$maxFileSize}"
        ]);

        $file = $request->file('excel_file');
        $importErrors = [];
        $successCount = 0;
        $failedRows = [];

        try {
            $rows = Excel::toCollection(new BasicExcelImport, $file)->first();

            if ($rows->isEmpty()) {
                throw new \Exception("Excel fayl bo'sh yoki noto'g'ri formatda.");
            }

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;  // Excel da 1-qator sarlavha, 2-qatordan boshlanadi

                try {
                    // ✅ Har bir qator uchun alohida transaction
                    DB::beginTransaction();

                    if ($hasWaterMeter) {
                        $this->importCustomerWithMeter($row->toArray(), $rowNumber);
                    } else {
                        $this->importCustomerNoMeter($row->toArray(), $rowNumber);
                    }

                    DB::commit();
                    $successCount++;

                } catch (\Illuminate\Validation\ValidationException $e) {
                    DB::rollBack();
                    $errors = [];
                    foreach ($e->errors() as $field => $fieldErrors) {
                        foreach ($fieldErrors as $error) {
                            $errors[] = $error;
                        }
                    }
                    $failedRows[] = [
                        'row' => $rowNumber,
                        'errors' => implode(', ', $errors)
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $failedRows[] = [
                        'row' => $rowNumber,
                        'errors' => $e->getMessage()
                    ];
                }
            }

            // ✅ Natijani qaytarish
            $type = $hasWaterMeter ? 'hisoblagichli' : 'hisoblagichsiz';

            if ($successCount > 0 && empty($failedRows)) {
                // Hammasi muvaffaqiyatli
                return redirect()->route('customers.import.form')
                    ->with('success', "{$successCount} ta {$type} mijoz muvaffaqiyatli import qilindi!");
            } elseif ($successCount > 0 && !empty($failedRows)) {
                // Qisman muvaffaqiyatli
                $errorMessages = [];
                foreach ($failedRows as $failed) {
                    $errorMessages[] = "Qator {$failed['row']}: {$failed['errors']}";
                }

                return redirect()->route('customers.import.form')
                    ->with('warning', "{$successCount} ta {$type} mijoz import qilindi, " . count($failedRows) . " ta qatorda xatolik bor.")
                    ->withErrors(['import_errors' => $errorMessages]);
            } else {
                // Hech narsa import qilinmadi
                $errorMessages = [];
                foreach ($failedRows as $failed) {
                    $errorMessages[] = "Qator {$failed['row']}: {$failed['errors']}";
                }

                $finalValidator = Validator::make([], []);
                foreach ($errorMessages as $error) {
                    $finalValidator->errors()->add('import_errors', $error);
                }

                throw new \Illuminate\Validation\ValidationException($finalValidator);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Excel import error: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['file_error' => 'Import qilishda xatolik: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * ✅ Hisoblagichsiz mijoz import qilish
     */
    private function importCustomerNoMeter(array $rowData, int $rowNumber)
    {
        $preparedData = [
            'kompaniya_id' => $rowData['kompaniya_id'] ?? null,
            'kocha_id' => $rowData['kocha_id'] ?? null,
            'fio' => $rowData['fio'] ?? null,
            'telefon_raqami' => $rowData['telefon_raqami'] ?? null,
            'uy_raqami' => $rowData['uy_raqami'] ?? null,
            'hisob_raqam' => $rowData['hisob_raqam'] ?? null,
            'oila_azolari' => $rowData['oila_azolari'] ?? null,
        ];

        // ✅ Validatsiya
        $validator = Validator::make($preparedData, [
            'kompaniya_id' => 'required|integer|exists:companies,id',
            'kocha_id' => 'required|integer|exists:streets,id',
            'fio' => 'required|string|max:255',
            'hisob_raqam' => [
                'required',
                'integer',
                Rule::unique('customers', 'account_number')
            ],
            'oila_azolari' => 'required|integer|min:1|max:50',
            'telefon_raqami' => 'nullable|string|max:30',
            'uy_raqami' => 'nullable|string|max:255',
        ], [
            'kompaniya_id.required' => 'Kompaniya ID majburiy',
            'kompaniya_id.exists' => 'Kompaniya topilmadi',
            'kocha_id.required' => 'Ko\'cha ID majburiy',
            'kocha_id.exists' => 'Ko\'cha topilmadi',
            'fio.required' => 'FIO majburiy',
            'hisob_raqam.required' => 'Hisob raqam majburiy',
            'hisob_raqam.unique' => 'Bu hisob raqam allaqachon mavjud',
            'oila_azolari.required' => 'Oila a\'zolari soni majburiy',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $validated = $validator->validated();

        // ✅ Mijoz yaratish
        Customer::create([
            'company_id' => $validated['kompaniya_id'],
            'street_id' => $validated['kocha_id'],
            'name' => $validated['fio'],
            'phone' => $validated['telefon_raqami'] ?? null,
            'address' => $validated['uy_raqami'] ?? null,
            'account_number' => $validated['hisob_raqam'],
            'has_water_meter' => false,
            'family_members' => $validated['oila_azolari'],
            'is_active' => true,
            'balance' => 0,
        ]);
    }

    /**
     * ✅ Hisoblagichli mijoz import qilish
     */
    private function importCustomerWithMeter(array $rowData, int $rowNumber)
    {
        $preparedData = [
            'kompaniya_id' => $rowData['kompaniya_id'] ?? null,
            'kocha_id' => $rowData['kocha_id'] ?? null,
            'fio' => $rowData['fio'] ?? null,
            'telefon_raqami' => isset($rowData['telefon_raqami']) ? (string)$rowData['telefon_raqami'] : null,
            'uy_raqami' => isset($rowData['uy_raqami']) ? (string)$rowData['uy_raqami'] : null,
            'hisob_raqam' => $rowData['hisob_raqam'] ?? null,
            'hisoblagich_ornatilgan_sana' => $this->parseExcelDate($rowData['hisoblagich_ornatilgan_sana'] ?? null),
            'amal_qilish_muddati' => $rowData['amal_qilish_muddati'] ?? 8,
            'boshlangich_korsatkich' => $rowData['boshlangich_korsatkich'] ?? null,
            'korsatkich_sanasi' => $this->parseExcelDate($rowData['korsatkich_sanasi'] ?? null) ?? now()->format('Y-m-d'),
            'oila_azolari' => $rowData['oila_azolari'] ?? null,
        ];

        // ✅ Validatsiya
        $validator = Validator::make($preparedData, [
            'kompaniya_id' => 'required|integer|exists:companies,id',
            'kocha_id' => 'required|integer|exists:streets,id',
            'fio' => 'required|string|max:255',
            'hisob_raqam' => [
                'required',
                'integer',
                Rule::unique('customers', 'account_number'),
                Rule::unique('water_meters', 'meter_number')
            ],
            'boshlangich_korsatkich' => 'required|numeric|min:0',
            'amal_qilish_muddati' => 'nullable|integer|min:1|max:20',
            'hisoblagich_ornatilgan_sana' => 'nullable|date|before_or_equal:today',
            'korsatkich_sanasi' => 'required|date|before_or_equal:today',
            'telefon_raqami' => 'nullable|string|max:30',
            'uy_raqami' => 'nullable|string|max:255',
            'oila_azolari' => 'nullable|integer|min:0|max:50',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $validated = $validator->validated();

        // ✅ Mijoz yaratish
        $customer = Customer::create([
            'company_id' => $validated['kompaniya_id'],
            'street_id' => $validated['kocha_id'],
            'name' => $validated['fio'],
            'phone' => $validated['telefon_raqami'] ?? null,
            'address' => $validated['uy_raqami'] ?? null,
            'account_number' => $validated['hisob_raqam'],
            'family_members' => $validated['oila_azolari'] ?? null,
            'has_water_meter' => true,
            'is_active' => true,
            'balance' => 0,
        ]);

        // ✅ WaterMeter yaratish
        $installationDate = $validated['hisoblagich_ornatilgan_sana']
            ? Carbon::parse($validated['hisoblagich_ornatilgan_sana'])
            : Carbon::now();

        $validityPeriod = (int)$validated['amal_qilish_muddati'];

        $waterMeter = $customer->waterMeter()->create([
            'meter_number' => $validated['hisob_raqam'],
            'installation_date' => $installationDate->toDateString(),
            'validity_period' => $validityPeriod,
            'expiration_date' => $installationDate->copy()->addYears($validityPeriod)->toDateString(),
        ]);

        // ✅ Boshlang'ich reading yaratish
        MeterReading::create([
            'water_meter_id' => $waterMeter->id,
            'reading' => $validated['boshlangich_korsatkich'],
            'reading_date' => $validated['korsatkich_sanasi'],
            'confirmed' => true,
        ]);
    }

    /**
     * ✅ Excel sanasini parse qilish
     */
    private function parseExcelDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        // Agar faqat yil kiritilgan bo'lsa (masalan: 2023)
        if (is_numeric($dateValue) && strlen((string)$dateValue) === 4) {
            return Carbon::createFromDate((int)$dateValue, 1, 1)->format('Y-m-d');
        }

        // Excel serial date format
        if (is_numeric($dateValue)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue))
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Oddiy sana string
        try {
            return Carbon::parse($dateValue)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function export(Request $request)
    {
        $user = auth()->user();

        // ✅ 1. Validatsiya
        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id'
        ]);

        $companyId = $validated['company_id'];

        // ✅ 2. Ruxsat tekshiruvi
        if (!$user->hasRole('admin') && $user->company_id != $companyId) {
            abort(403, 'Sizda bu kompaniya ma\'lumotlarini yuklab olishga ruxsat yo\'q.');
        }

        // ✅ 3. Fayl nomini kompaniya bilan
        $company = Company::find($companyId);
        $companyName = $company ? \Str::slug($company->name) : 'company';
        $fileName = "mijozlar_{$companyName}_" . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new CustomersExport($companyId), $fileName);
    }
}
