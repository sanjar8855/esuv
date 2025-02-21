<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * Kompaniyalar ro‘yxatini ko‘rsatish.
     */
    public function index()
    {
        $companies = Company::all();
        return view('companies.index', compact('companies'));
    }

    /**
     * Yangi kompaniya yaratish formasi.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Yangi kompaniyani saqlash.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:companies',
            'phone' => 'required|string|max:20',
            'plan' => 'in:basic,premium',
            'address' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        Company::create($validated);

        return redirect()->route('companies.index')->with('success', 'Kompaniya muvaffaqiyatli qo‘shildi!');
    }

    /**
     * Bitta kompaniya ma’lumotlarini ko‘rsatish.
     */
    public function show($id)
    {
        $company = Company::findOrFail($id);
        return view('companies.show', compact('company'));
    }

    /**
     * Kompaniyani tahrirlash formasi.
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('companies.edit', compact('company'));
    }

    /**
     * Kompaniyani yangilash.
     */
    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:companies,email,' . $id,
            'phone' => 'required|string|max:20',
            'plan' => 'in:basic,premium',
            'address' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $company->update($validated);

        return redirect()->route('companies.index')->with('success', 'Kompaniya muvaffaqiyatli yangilandi!');
    }

    /**
     * Kompaniyani o‘chirish.
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Kompaniya muvaffaqiyatli o‘chirildi!');
    }
}
