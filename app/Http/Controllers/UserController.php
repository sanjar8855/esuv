<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $usersQuery = User::with('company','roles')->orderBy('id', 'desc');

        if (!$user->hasRole('admin')) {
            $usersQuery->where('company_id', $user->company_id);
        }

        $usersCount = (clone $usersQuery)->count();

        $users = $usersQuery->paginate(10);

        return view('users.index', compact('users', 'usersCount'));
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
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:company_owner,employee',
            'rank' => 'nullable|string',
            'files' => 'nullable|file|max:4096',
            'work_start' => 'nullable|date',
        ]);

        $filePath = null;

        if ($request->hasFile('files')) {
            // store() metodi fayl manzilini (string) qaytaradi
            $filePath = $request->file('files')->store('user_files', 'public'); // Papka nomini o'zgartirdim (ixtiyoriy)
        }

        $user = User::create([
            'company_id' => $validatedData['company_id'],
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'rank' => $validatedData['rank'],
            'files' => $filePath,
            'work_start' => $validatedData['work_start'],
        ]);

        $user->assignRole($validatedData['role']);

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
        // 1. Validatsiya
        $validatedData = $request->validate([ // Validatsiyadan o'tgan ma'lumotlarni olish
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id, // Yangilash uchun to'g'ri unique qoidasi
            'password' => 'nullable|string|min:6', // Yangilashda parol majburiy emas (nullable)
            'role' => 'required|in:company_owner,employee',
            'rank' => 'nullable|string',
            'files' => 'nullable|file|max:4096', // Fayl ham majburiy emas va hajmi cheklangan
            'work_start' => 'nullable|date',
        ]);

        // 2. Fayl bilan ishlash
        $filePath = $user->files; // Joriy fayl manzilini saqlab turamiz

        if ($request->hasFile('files')) { // Agar YANGI fayl yuklangan bo'lsa
            // a) Eski faylni o'chirish (agar mavjud bo'lsa)
            if ($user->files) {
                // Storage::disk('public')->delete($user->pdf_file); // XATO: pdf_file emas, files bo'lishi kerak
                Storage::disk('public')->delete($user->files); // TO'G'RI: 'files' ustunidagi manzilni ishlatish
            }
            // b) Yangi faylni saqlash va manzilini olish
            // $user->files = $request->file('files')->store('files', 'public'); // Bu ham ishlaydi, lekin pastdagi yondashuv aniqroq
            $filePath = $request->file('files')->store('user_files', 'public'); // Yangi fayl manzilini $filePath ga yozamiz (store metodidagi papkaga)
        }

        // 3. Yangilash uchun ma'lumotlar massivini tayyorlash
        $updateData = [
            'company_id' => $validatedData['company_id'],
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            // Parolni faqat yangi parol kiritilgan bo'lsa yangilaymiz
            // 'password' => $request->password ? Hash::make($request->password) : $user->password, // Bu eski yondashuv
            'rank' => $validatedData['rank'],
            'files' => $filePath, // <--- FAYL MANZILINI (string yoki null) TO'G'RI UZATISH
            'work_start' => $validatedData['work_start'],
        ];

        // 4. Agar validatsiyadan o'tgan parol bo'sh bo'lmasa (yangi parol kiritilgan bo'lsa)
        if (!empty($validatedData['password'])) {
            $updateData['password'] = Hash::make($validatedData['password']);
        }
        // Agar yangi parol kiritilmagan bo'lsa, $updateData massivida 'password' kaliti bo'lmaydi
        // va update() metodi parolni o'zgartirmaydi.

        // 5. Foydalanuvchi ma'lumotlarini yangilash
        $user->update($updateData);

        // 6. Rolni yangilash (eskisini o'chirib, yangisini qo'shish uchun syncRoles yaxshiroq)
        // $user->assignRole($validatedData['role']); // assignRole faqat qo'shadi, eskini o'chirmaydi
        $user->syncRoles([$validatedData['role']]); // Eskilarini o'chiradi va faqat ko'rsatilganni qoldiradi

        return redirect()->route('users.index')->with('success', 'Foydalanuvchi yangilandi!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Foydalanuvchi o‘chirildi!');
    }
}
