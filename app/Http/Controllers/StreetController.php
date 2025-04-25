<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Street;
use App\Models\Neighborhood;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StreetController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // OZGARISH: Admin uchun barcha ko'chalarni ko'rsatish
        $query = Street::with('neighborhood');

        // Mijozlar sonini qo'shamiz
        $query->withCount([
            'customers as customer_count' => function ($q) use ($user) {
                $q->where('is_active', 1);
                // OZGARISH: Admin emas bo'lsa, filter qo'llaymiz
                if (!$user->hasRole('admin') && $user->company_id) {
                    $q->where('company_id', $user->company_id);
                }
            }
        ]);

        // OZGARISH: Admin emas bo'lsa, filter qo'llaymiz, admin uchun barcha ko'chalarni ko'rsatish
        if (!$user->hasRole('admin')) {
            $query->whereHas('customers', function ($q) use ($user) {
                $q->where('is_active', 1);
                if ($user->company_id) {
                    $q->where('company_id', $user->company_id);
                }
            });
        }

        $streets = $query->paginate(15);

        return view('streets.index', compact('streets'));
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

    public function show(Street $street)
    {
        $user = auth()->user();

        $query = Customer::with([
            'company',
            'street.neighborhood.city.region',
            'waterMeter.readings' => function ($q) {
                $q->orderBy('reading_date', 'desc');
                $q->orderBy('id', 'desc');
            }
        ])
            ->withSum('invoices as total_due', 'amount_due')
            ->withSum('payments as total_paid', 'amount')
            ->where('is_active', 1)
            ->where('street_id', $street->id); // 🔴 Shu ko‘chadagi mijozlar

        if (!$user->hasRole('admin') && $user->company) {
            $query->where('company_id', $user->company_id); // 🔒 Faqat o‘z kompaniyasidagi
        }

        $customersCount = (clone $query)->count();

        $customers = $query->paginate(20)->withQueryString();

        return view('streets.show', compact('street', 'customers', 'customersCount'));
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
