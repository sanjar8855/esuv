<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables; // DataTables Facade'ni import qiling
use Illuminate\Support\Facades\Auth; // Auth facade'ni import qiling

class UserController extends Controller
{
    public function index()
    {
        $loggedInUser = Auth::user(); // O'zgaruvchi nomini o'zgartirdim tushunarli bo'lishi uchun

        // Asosiy query builder (boshlang'ich holat)
        $usersQuery = User::with('company', 'roles')->select('users.*'); // Agar kerak bo'lsa select() ni qoldiring

        // Admin bo'lmaganlar uchun kompaniya bo'yicha filtr
        if (!$loggedInUser->hasRole('admin')) {
            $usersQuery->where('company_id', $loggedInUser->company_id);
        }

        // Agar so'rov AJAX orqali DataTables'dan kelsa
        if (request()->ajax()) {
            return DataTables::eloquent($usersQuery)
                ->addColumn('roles', function (User $user) {
                    // Rollarni formatlash (avvalgi kodingizdan) - Badge'larni shu yerda qo'shish qulayroq
                    return $user->roles->map(function ($role) {
                        switch ($role->name) {
                            case 'admin': return '<span class="badge badge-outline text-red">Admin</span>';
                            case 'company_owner': return '<span class="badge badge-outline text-blue">Direktor</span>';
                            case 'employee': return '<span class="badge badge-outline text-green">Xodim</span>';
                            default: return '<span class="badge badge-outline text-muted">' . e($role->name) . '</span>'; // e() - XSS himoyasi uchun
                        }
                    })->implode(' '); // Bir nechta rol bo'lsa, orasiga probel qo'yadi
                })
                ->addColumn('company_name', function (User $user) use ($loggedInUser) {
                    if ($loggedInUser->hasRole('admin')) {
                        if ($user->company) {
                            // Kompaniyaning 'show' sahifasi uchun URL generatsiya qilamiz
                            // 'companies.show' - sizning marshrutingiz nomi bo'lishi kerak
                            // Agar marshrut boshqacha bo'lsa, mos ravishda o'zgartiring
                            $url = route('companies.show', $user->company->id);
                            // <a> tegi yordamida havolani yaratamiz
                            return '<a href="' . $url . '">' . e($user->company->name) . '</a>';
                        } else {
                            return '-'; // Kompaniya yo'q bo'lsa
                        }
                    }
                    return '-'; // Admin bo'lmasa
                })
                ->addColumn('actions', function (User $user) {
                    // Amallar tugmalarini generatsiya qilish
                    $showUrl = route('users.show', $user->id);
                    $editUrl = route('users.edit', $user->id);
                    $deleteUrl = route('users.destroy', $user->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    // O'chirish uchun formani ham qo'shamiz
                    return <<<HTML
                        <a href="{$showUrl}" class="btn btn-info btn-sm">Batafsil</a>
                        <a href="{$editUrl}" class="btn btn-warning btn-sm">Tahrirlash</a>
                        <form action="{$deleteUrl}" method="POST" style="display:inline;" onsubmit="return confirm('Haqiqatan ham o‘chirmoqchimisiz?');">
                            {$csrf}
                            {$method}
                            <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                        </form>
                    HTML;
                    // Yoki alohida blade fayl (\`users.actions\`) ishlatishingiz mumkin:
                    // return view('users.actions', compact('user'))->render();
                })
                ->editColumn('work_start', function(User $user) {
                    // Sanani formatlash (agar kerak bo'lsa)
                    return $user->work_start ? \Carbon\Carbon::parse($user->work_start)->format('Y-m-d') : '-';
                })
                ->rawColumns(['roles', 'actions', 'company_name']) // Bu ustunlarda HTML borligini DataTables'ga aytamiz
                ->orderColumn('id', '-id $1') // Agar ID bo'yicha default sort kerak bo'lsa
                ->toJson();
        }

        // Oddiy GET so'rov uchun (sahifa birinchi marta ochilganda)
        // Faqat umumiy sonni (agar sarlavhada kerak bo'lsa) va view'ni qaytaramiz
        $usersCount = (clone $usersQuery)->count(); // Umumiy son (filtr hisobga olingan)

        return view('users.index', compact('usersCount')); // Endi $users'ni o'tkazish shart emas
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            // Admin barcha foydalanuvchilarni ko‘radi
            $companies = Company::all();
        } else {
            // Oddiy foydalanuvchi faqat o‘z kompaniyasidagi userlarni ko‘radi
            $companies = Company::where('id', $user->company->id)->get();
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
            $companies = Company::all();
        } else {
            // Oddiy foydalanuvchi faqat o‘z kompaniyasidagi userlarni ko‘radi
            $companies = Company::where('id', $user->company->id)->get();
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
