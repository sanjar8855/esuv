<?php
namespace App\Http\Controllers;

use App\Models\Street;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Company;

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
            'telegram_chat_id' => 'nullable|string|max:255',
            'address' => 'required|string',
            'account_number' => 'required|unique:customers,account_number',
            'family_members' => 'nullable|integer|min:1',
        ]);

        // Oddiy foydalanuvchilar uchun kompaniyani avtomatik qo‘shish
        if (!$user->hasRole('admin')) {
            $validated['company_id'] = $user->company->id;
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['has_water_meter'] = $request->has('has_water_meter') ? 1 : 0;

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Mijoz muvaffaqiyatli qo‘shildi!');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'company',
            'street.neighborhood.city.region',
            'invoices' => function ($query) {
                $query->orderBy('billing_period', 'desc');
            },
            'payments' => function ($query) {
                $query->orderBy('payment_date', 'desc');
            },
            'waterMeter.readings' => function ($query) {
                // Oxirgi (eng so‘nggi) o‘qish reading_date yoki id bo‘yicha tartib:
                $query->orderBy('reading_date', 'desc');
            },
        ]);

        return view('customers.show', compact('customer'));
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
            'telegram_chat_id' => 'nullable|string|max:255',
            'address' => 'required|string',
            'account_number' => 'required|unique:customers,account_number,' . $customer->id,
            'family_members' => 'nullable|integer|min:1',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['has_water_meter'] = $request->has('has_water_meter') ? 1 : 0;

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
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Mijoz muvaffaqiyatli o‘chirildi!');
    }
}
