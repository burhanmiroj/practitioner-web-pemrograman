<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        $this->buatAdminJikaKosong();

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $this->buatAdminJikaKosong();

        $kredensial = [
            'username' => trim((string) $request->input('username', '')),
            'password' => (string) $request->input('password', ''),
        ];

        if (Auth::attempt($kredensial)) {
            $request->session()->regenerate(); // ganti ID sesi supaya tidak bisa dibajak

            return redirect('/');
        }

        return back()
            ->withInput($request->only('username'))
            ->withErrors(['login' => 'Username atau password salah.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Kalau belum ada akun sama sekali, buat akun admin dulu (admin / admin123)
    private function buatAdminJikaKosong(): void
    {
        if (User::count() === 0) {
            User::create(['username' => 'admin', 'password' => 'admin123']);
        }
    }
}
