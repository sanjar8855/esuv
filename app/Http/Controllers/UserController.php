<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            // Admin barcha foydalanuvchilarni ko‘radi
            $users = User::with('company')->paginate(10);
        } else {
            // Oddiy foydalanuvchi faqat o‘z kompaniyasidagi userlarni ko‘radi
            $users = User::where('company_id', $user->company_id)->with('company')->paginate(10);
        }

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            // Admin barcha foydalanuvchilarni ko‘radi
            $companies = Company::paginate(10);
        } else {
            // Oddiy foydalanuvchi faqat o‘z kompaniyasidagi userlarni ko‘radi
            $companies = Company::where('id', $user->company->id)->paginate(10);
        }
        return view('users.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'company_id' => $request->company_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')->with('success', 'Foydalanuvchi qo‘shildi!');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $users = auth()->user();
        if ($users->hasRole('admin')) {
            // Admin barcha foydalanuvchilarni ko‘radi
            $companies = Company::all()->paginate(10);
        } else {
            // Oddiy foydalanuvchi faqat o‘z kompaniyasidagi userlarni ko‘radi
            $companies = Company::where('id', $user->company->id)->paginate(10);
        }
        return view('users.edit', compact('user', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'company_id' => $request->company_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        return redirect()->route('users.index')->with('success', 'Foydalanuvchi yangilandi!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Foydalanuvchi o‘chirildi!');
    }
}
