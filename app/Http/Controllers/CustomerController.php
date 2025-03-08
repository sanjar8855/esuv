<?php
namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\Street;
use App\Models\WaterMeter;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Laravel\Facades\Telegram;

class CustomerController extends Controller
{
    /**
     * Mijozlar ro‘yxati.
     */
    public function index()
    {
        $user = auth()->user();
        $search = request('search');
        $streetId = request('street_id');
        $debtFilter = request('debt');

        // **📌 Admin bo‘lsa barcha ko‘chalarni oladi, aks holda faqat o‘z kompaniyasidagi mijozlar ko‘chalarini**
        if ($user->hasRole('admin')) {
            $streets = Street::all(); // ✅ Admin barcha ko‘chalarni ko‘radi
        } else {
            // ✅ Agar admin bo‘lmasa, faqat o‘z kompaniyasidagi mijozlar joylashgan ko‘chalar
            if ($user->company) {
                $streets = Street::whereHas('customers', function($query) use ($user) {
                    $query->where('company_id', $user->company->id);
                })->get();
            } else {
                // ❌ Kompaniyasi yo‘q foydalanuvchilar uchun bo‘sh ro‘yxat
                $streets = collect();
            }
        }

        // **📌 Asosiy query**
        $query = Customer::with([
            'company',
            'street.neighborhood.city.region',
            'waterMeter.readings' => function ($q) {
                $q->orderBy('reading_date', 'desc');
            }
        ])
            ->withSum('invoices as total_due', 'amount_due')
            ->withSum('payments as total_paid', 'amount');

        // **📌 Agar admin bo‘lmasa, faqat o‘z kompaniyasiga tegishli mijozlarni ko‘rsatamiz**
        if (!$user->hasRole('admin') && $user->company) {
            $query->where('company_id', $user->company_id);
        }

        // **📌 Faqat faol mijozlarni olish**
        $query->where('is_active', 1);

        // **📌 Qidiruv**
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('account_number', 'LIKE', "%{$search}%");
            });
        }

        // **📌 Ko‘cha bo‘yicha filtrlash**
        if ($streetId) {
            $query->where('street_id', $streetId);
        }

        // **📌 Qarzdor mijozlarni chiqarish**
        if ($debtFilter == 'has_debt') {
            $query->havingRaw('total_due > total_paid');
        }

        // **📌 Sahifalash va query stringni saqlash**
        $customers = $query->paginate(20)->withQueryString();

        return view('customers.index', compact('customers', 'streets'));
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

        $validated = $request->validate([
            'company_id' => $user->hasRole('admin') ? 'required|exists:companies,id' : '',
            'street_id' => 'required|exists:streets,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'required|string',
            'account_number' => 'required|unique:customers,account_number',
            'family_members' => 'nullable|integer|min:1',
            'pdf_file' => 'nullable|file|mimes:pdf|max:4096',

            'meter_number' => 'required|numeric|unique:water_meters,meter_number',
            'installation_date' => 'required|date',
            'validity_period' => 'required|integer|min:1',

            'initial_reading' => 'required|numeric|min:0',
            'reading_date' => 'required|date',
        ]);

        if ($request->hasFile('pdf_file')) {
            $validated['pdf_file'] = $request->file('pdf_file')->store('pdfs', 'public');
        }

        // Oddiy foydalanuvchilar uchun kompaniyani avtomatik qo‘shish
        if (!$user->hasRole('admin')) {
            $validated['company_id'] = $user->company->id;
        }

//        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_active'] = 1;
//        $validated['has_water_meter'] = $request->has('has_water_meter') ? 1 : 0;
        $validated['has_water_meter'] = 1;

        $customer = Customer::create($validated);

        $waterMeter = WaterMeter::create([
            'customer_id' => $customer->id,
            'meter_number' => $validated['meter_number'],
            'installation_date' => $validated['installation_date'],
            'validity_period' => $validated['validity_period'],
            'expiration_date' => now()->parse($validated['installation_date'])->addYears($validated['validity_period']), // Amal qilish muddati
        ]);

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
            'waterMeter.readings' => function ($query) {
                // Oxirgi (eng so‘nggi) o‘qish reading_date yoki id bo‘yicha tartib:
                $query->orderBy('reading_date', 'desc');
            },
            'telegramAccounts'
        ]);

        $invoices = $customer->invoices()->orderBy('id', 'desc')->paginate(5, ['*'], 'invoice_page');
        $payments = $customer->payments()->orderBy('id', 'desc')->paginate(5, ['*'], 'payment_page');

        return view('customers.show', compact('customer', 'invoices', 'payments'));
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
            'text' => "🚨 Sizning <b>{$accountNumber}</b> hisob raqamingiz botdan o‘chirildi.\n🔢 Yangi hisob raqamini kiritib, qayta bog‘lang.",
            'parse_mode' => 'HTML',
        ]);
    }
}
