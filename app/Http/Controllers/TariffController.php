<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tariff;
use App\Models\Company;

class TariffController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Admin barcha tariflarni ko‘radi
        if ($user->hasRole('admin')) {
            $tariffs = Tariff::with('company')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            // Xodim faqat o‘z kompaniyasining tariflarini ko‘radi
            $tariffs = Tariff::where('company_id', $user->company_id)
                ->with('company')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('tariffs.index', compact('tariffs'));
    }


    public function create()
    {
        $user = auth()->user();

        // Admin barcha tariflarni ko‘radi
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            // Xodim faqat o‘z kompaniyasining tariflarini ko‘radi
            $companies = Company::where('id', $user->company_id)->get();
        }

        return view('tariffs.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'nullable|string|max:255',
            'price_per_m3' => 'required|numeric|min:0',
            'for_one_person' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        // Eski tarifni topish (eng oxirgi tarif)
        $oldTariff = Tariff::where('company_id', $request->company_id)
            ->where('is_active', true)
            ->orderBy('valid_from', 'desc')
            ->first();

        // Agar eski tarif mavjud bo‘lsa, uning `valid_to` sanasini yangi tarifning `valid_from` sanasiga o‘zgartiramiz
        if ($oldTariff) {
            $oldTariff->update([
                'valid_to' => $request->valid_from,
                'is_active' => false, // Eski tarifni nofaol qilamiz
            ]);
        }

        Tariff::create($validated);

        return redirect()->route('tariffs.index')->with('success', 'Tarif muvaffaqiyatli qo‘shildi!');
    }

    public function show(Tariff $tariff)
    {
        return view('tariffs.show', compact('tariff'));
    }

    public function edit(Tariff $tariff)
    {
        $user = auth()->user();

        // Admin barcha tariflarni ko‘radi
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            // Xodim faqat o‘z kompaniyasining tariflarini ko‘radi
            $companies = Company::where('id', $user->company_id)->get();

            // Agar tarif xodimning kompaniyasiga tegishli bo‘lmasa, 403 error qaytariladi
            if ($tariff->company_id !== $user->company_id) {
                abort(403, 'Siz ushbu tarifni tahrirlashga ruxsatingiz yo‘q.');
            }
        }

        return view('tariffs.edit', compact('tariff', 'companies'));
    }


    public function update(Request $request, Tariff $tariff)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'price_per_m3' => 'required|numeric|min:0',
            'for_one_person' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        // Agar tarifning valid_from sanasi o‘zgarsa, eski tariflarni tekshirish
        if ($tariff->valid_from != $request->valid_from) {
            // O‘sha kompaniyaga tegishli eski aktiv tarifni topish
            $oldTariff = Tariff::where('company_id', $request->company_id)
                ->where('id', '!=', $tariff->id) // O‘zi bilan solishtirmaslik uchun
                ->where('is_active', true)
                ->orderBy('valid_from', 'desc')
                ->first();

            // Agar eski tarif mavjud bo‘lsa va yangi tarifning boshlanish sanasi undan keyin bo‘lsa
            if ($oldTariff && $request->valid_from > $oldTariff->valid_from) {
                $oldTariff->update([
                    'valid_to' => $request->valid_from,
                    'is_active' => false, // Eski tarifni nofaol qilish
                ]);
            }
        }

        // Tarifni yangilash
        $tariff->update($validated);

        return redirect()->route('tariffs.index')->with('success', 'Tarif muvaffaqiyatli yangilandi!');
    }

    public function destroy(Tariff $tariff)
    {
        $tariff->delete();

        return redirect()->route('tariffs.index')->with('success', 'Tarif o‘chirildi.');
    }
}
