<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Login sahifasini ko‘rsatish
    public function showLogin()
    {
        return view('auth.login');
    }

    // Login jarayoni
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        $remember = $request->has('remember');
        $loginField = $request->input('login');

        // Login yoki phone orqali kirish
        $credentials = [];

        // Agar faqat raqamlardan iborat bo'lsa - telefon raqami
        if (preg_match('/^[0-9]+$/', $loginField)) {
            $credentials = [
                'phone' => $loginField,
                'password' => $request->password,
            ];
        } else {
            // Aks holda - login
            $credentials = [
                'login' => $loginField,
                'password' => $request->password,
            ];
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // ✅ Activity Log - Login
            ActivityLog::log('auth', 'Tizimga kirdi', auth()->user());

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['login' => 'Login/Telefon yoki parol noto\'g\'ri'])->withInput();
    }

    // Ro‘yxatdan o‘tish sahifasi
    public function showRegister()
    {
        return view('auth.register');
    }

    // Ro‘yxatdan o‘tish jarayoni
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        return redirect()->route('dashboard');
    }

    // Logout funksiyasi
    public function logout(Request $request)
    {
        // ✅ Activity Log - Logout (logout qilishdan oldin)
        if (auth()->check()) {
            ActivityLog::log('auth', 'Tizimdan chiqdi', auth()->user());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
