<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string', // email yoki phone
            'password' => 'required',
        ]);

        // Email yoki phone orqali login
        $user = User::where('email', $credentials['login'])
            ->orWhere('phone', $credentials['login'])
            ->orWhere('login', $credentials['login'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Noto\'g\'ri login yoki parol'
            ], 401);
        }

        // Token yaratish
        $token = $user->createToken('mobile_app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'company_id' => $user->company_id,
                'company_name' => $user->company?->name,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Chiqib ketildi']);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'company_id' => $user->company_id,
            'company_name' => $user->company?->name,
            'telegram_username' => $user->telegram_username,
        ]);
    }
}
