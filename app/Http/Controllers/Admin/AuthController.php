<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'يرجى إدخال البريد الإلكتروني أو رقم الهاتف',
            'password.required' => 'يرجى إدخال كلمة المرور',
        ]);

        $input = trim($request->input('login'));
        $hash = User::hashIdentifier($input);

        // البحث عن المستخدم عبر البريد أو رقم الهاتف المشفر
        $user = User::where('email_hash', $hash)
            ->orWhere('phone_hash', $hash)
            ->orWhere('email_encrypted', $input)
            ->orWhere('phone_encrypted', $input)
            ->first();

        if (! $user) {
            return back()->withErrors(['login' => 'بيانات الدخول غير صحيحة'])->withInput();
        }

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['login' => 'بيانات الدخول غير صحيحة'])->withInput();
        }

        if ($user->account_status !== 'active') {
            return back()->withErrors(['login' => 'هذا الحساب غير نشط أو معلق من قبل الإدارة']);
        }

        Auth::login($user, $request->boolean('remember'));

        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route('admin.dashboard'))->with('success', 'تم تسجيل الدخول بنجاح! مرحباً بك في لوحة تحكم إنجز لي');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'تم تسجيل الخروج بنجاح');
    }
}
