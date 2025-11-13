<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramWebAppController extends Controller
{
    /**
     * Telegram WebApp entry point
     *
     * Bu route Telegram Mini App'dan ochiladi
     * Middleware avtomatik authentication qiladi
     */
    public function index(Request $request)
    {
        // Middleware allaqachon authentication qilgan
        // Foydalanuvchini dashboard'ga yo'naltirish

        if (!Auth::check()) {
            return view('telegram-webapp.login');
        }

        // Telegram WebApp flagini session'ga qo'shamiz
        session(['is_telegram_webapp' => true]);

        // Dashboard'ga redirect
        return redirect()->route('dashboard');
    }

    /**
     * Telegram WebApp authentication endpoint
     *
     * Telegram InitData bilan authentication
     */
    public function authenticate(Request $request)
    {
        // Middleware allaqachon validate va authenticate qilgan

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please contact administrator.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Authentication successful',
            'user' => [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'company' => Auth::user()->company?->name,
            ],
            'redirect_url' => route('dashboard')
        ]);
    }

    /**
     * Telegram WebApp'dan chiqish
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * User ma'lumotlarini olish
     */
    public function user(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'phone' => Auth::user()->phone,
                'company' => Auth::user()->company?->name,
                'company_id' => Auth::user()->company_id,
                'telegram_username' => Auth::user()->telegram_username,
            ]
        ]);
    }
}
