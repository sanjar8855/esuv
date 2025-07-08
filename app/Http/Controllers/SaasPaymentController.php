<?php

namespace App\Http\Controllers;

use App\Models\SaasPayment;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SaasPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) // Requestni metodga qabul qilamiz
    {
        // 1. So'rovdan kelayotgan 'period' ni validatsiyadan o'tkazib olamiz
        // Agar 'period' kelmasa yoki formati noto'g'ri bo'lsa, joriy oyni olamiz
        $validated = $request->validate([
            'period' => 'nullable|date_format:Y-m' // Formatni Yil-Oy (2025-06) deb tekshiramiz
        ]);

        $selectedPeriod = $validated['period'] ?? now()->format('Y-m');

        // 2. Barcha kompaniyalarni olamiz va har birining TANLANGAN OY uchun to'lovi bor-yo'qligini tekshiramiz
        $companies = Company::with(['plan', 'saasPayments' => function ($query) use ($selectedPeriod) {
            $query->where('payment_period', $selectedPeriod); // Endi dinamik $selectedPeriod bilan ishlaymiz
        }])->orderBy('name')->get();

        // 3. Har bir kompaniya uchun to'lov holatini aniqlaymiz
        $companies->each(function ($company) {
            $company->is_paid_for_selected_month = $company->saasPayments->isNotEmpty();
        });

        // 4. O'zgaruvchilarni view'ga uzatamiz
        return view('saas_payments.index', [
            'companies' => $companies,
            'selectedPeriod' => $selectedPeriod // Tanlangan oyni ham view'ga yuboramiz
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Admin panelda kompaniyani tanlash uchun barcha kompaniyalar ro'yxati
        $companies = Company::with('plan')->orderBy('name')->get();

        // Agar `index` sahifasidagi "To'lov qo'shish" tugmasi bosilgan bo'lsa,
        // o'sha kompaniyani avtomatik tanlab qo'yish uchun
        $selectedCompanyId = $request->query('company_id');

        return view('saas_payments.create', compact('companies', 'selectedCompanyId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_period' => 'required|date_format:Y-m', // Format YYYY-MM bo'lishini tekshiradi
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            SaasPayment::create($validatedData); // RecordUserStamps traiti created_by_user_id ni avtomatik qo'shadi

            return redirect()->route('saas.payments.index')
                ->with('success', 'To‘lov muvaffaqiyatli saqlandi!');

        } catch (\Exception $e) {
            Log::error('Error storing SaaS payment: ' . $e->getMessage());
            return back()->withInput()->with('error', 'To‘lovni saqlashda xatolik yuz berdi.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SaasPayment $saasPayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SaasPayment $saasPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SaasPayment $saasPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SaasPayment $saasPayment)
    {
        //
    }

    public function history(Request $request)
    {
        // DataTables uchun AJAX logikasi olib tashlandi.

        // To'lovlarni olamiz, eng yangisi birinchi bo'lib
        $saasPayments = SaasPayment::query()
            ->with(['company', 'createdBy']) // N+1 muammosini oldini olish uchun
            ->latest('payment_date') // To'lov sanasi bo'yicha saralaymiz
            ->latest('id')           // Bir xil sanadagilarni ID bo'yicha
            ->paginate(25); // Masalan, har sahifada 25 tadan

        // Ma'lumotlarni view'ga uzatamiz
        return view('saas_payments.history', compact('saasPayments'));
    }
}
