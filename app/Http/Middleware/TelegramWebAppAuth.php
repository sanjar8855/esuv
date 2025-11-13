<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebAppAuth
{
    /**
     * Handle an incoming request.
     *
     * Telegram WebApp InitData validation va authentication
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Agar user allaqachon login bo'lsa va Telegram orqali kelmasa, o'tkazamiz
        if (Auth::check() && !$request->has('telegram_init_data')) {
            return $next($request);
        }

        // Telegram InitData olish
        $initData = $request->input('telegram_init_data') ?? $request->header('X-Telegram-Init-Data');

        if (!$initData) {
            // Agar InitData yo'q bo'lsa, oddiy login sahifasiga yo'naltirish
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Telegram orqali kirish kerak');
            }
            return $next($request);
        }

        // InitData validation
        $userData = $this->validateInitData($initData);

        if (!$userData) {
            Log::warning('Telegram WebApp: Invalid InitData', ['initData' => $initData]);
            return response()->json([
                'error' => 'Invalid Telegram authentication data'
            ], 403);
        }

        // User'ni topish yoki yaratish
        $user = $this->findOrCreateUser($userData);

        if (!$user) {
            return response()->json([
                'error' => 'User not found. Please contact administrator.'
            ], 403);
        }

        // User'ni login qilish
        Auth::login($user, true);

        // Session'ga Telegram ma'lumotlarini saqlash
        session([
            'telegram_user_id' => $userData['id'],
            'telegram_username' => $userData['username'] ?? null,
            'telegram_first_name' => $userData['first_name'] ?? null,
            'telegram_last_name' => $userData['last_name'] ?? null,
            'is_telegram_webapp' => true,
        ]);

        return $next($request);
    }

    /**
     * Telegram InitData'ni validate qilish
     *
     * @param string $initData
     * @return array|null
     */
    private function validateInitData(string $initData): ?array
    {
        $botToken = config('telegram.bots.webapp.token');

        if (!$botToken) {
            Log::error('Telegram WebApp Bot Token not configured');
            return null;
        }

        // InitData'ni parse qilish
        parse_str($initData, $data);

        if (!isset($data['hash'])) {
            return null;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        // Data string yaratish (sorted by key)
        ksort($data);
        $dataCheckString = [];
        foreach ($data as $key => $value) {
            $dataCheckString[] = $key . '=' . $value;
        }
        $dataCheckString = implode("\n", $dataCheckString);

        // Secret key yaratish
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);

        // Hash verification
        $computedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        if (!hash_equals($computedHash, $hash)) {
            Log::warning('Telegram WebApp: Hash mismatch', [
                'computed' => $computedHash,
                'received' => $hash
            ]);
            return null;
        }

        // User data'ni parse qilish
        if (isset($data['user'])) {
            $user = json_decode($data['user'], true);
            return $user;
        }

        return null;
    }

    /**
     * User'ni topish (faqat topish, yaratmaslik)
     *
     * Xodimlar tizimda allaqachon mavjud bo'lishi kerak
     * Telegram orqali yangi user yaratmaslik (security uchun)
     *
     * @param array $telegramUser
     * @return User|null
     */
    private function findOrCreateUser(array $telegramUser): ?User
    {
        // Telegram ID orqali topish (eng ishonchli usul)
        if (isset($telegramUser['id'])) {
            $user = User::where('telegram_user_id', $telegramUser['id'])->first();
            if ($user) {
                // Username o'zgargan bo'lishi mumkin, update qilamiz
                if (isset($telegramUser['username']) && $user->telegram_username !== $telegramUser['username']) {
                    $user->update(['telegram_username' => $telegramUser['username']]);
                }
                return $user;
            }
        }

        // Telegram username orqali topish (agar ID bilan topilmasa)
        if (isset($telegramUser['username'])) {
            $user = User::where('telegram_username', $telegramUser['username'])->first();
            if ($user) {
                // Telegram ID'ni saqlash (kelajakda tezroq topish uchun)
                if (isset($telegramUser['id']) && !$user->telegram_user_id) {
                    $user->update(['telegram_user_id' => $telegramUser['id']]);
                }
                return $user;
            }
        }

        // User topilmasa null qaytaramiz
        // Admin avval user yaratib, telegram_username yoki telegram_user_id ni o'rnatishi kerak
        return null;
    }
}
