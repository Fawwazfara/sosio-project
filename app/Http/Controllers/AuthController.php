<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function csrfToken(Request $request): JsonResponse
    {
        return response()->json([
            'csrf_token' => $request->session()->token(),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $identifier = strtolower(trim($request->email));
        $remember = (bool) $request->boolean('remember');

        // Izinkan login pakai Username/Email (Admin/Guru/Wali) atau NIS siswa (khusus akun Wali)
        $user = null;
        if (Auth::attempt(['email' => $identifier, 'password' => $request->password], $remember)) {
            $user = Auth::user();
        } else {
            $siswa = Siswa::with('waliUser')->where('nis', trim($request->email))->first();
            if ($siswa && $siswa->waliUser && Hash::check($request->password, $siswa->waliUser->password)) {
                Auth::login($siswa->waliUser, $remember);
                $user = $siswa->waliUser;
            }
        }

        if ($user) {
            $request->session()->regenerate();

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'message' => 'Login successful',
                'csrf_token' => $request->session()->token(),
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['Email/NIS atau kata sandi salah.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout successful',
            'csrf_token' => $request->session()->token(),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
