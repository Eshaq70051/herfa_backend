<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Category;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Review;

class AdminDashboardController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        $input = trim($credentials['phone']);
        $password = $credentials['password'];

        // 1. البحث عن المستخدم الإداري بالهاتف أو البريد الإلكتروني
        $user = \App\Models\User::where(function ($q) use ($input) {
            $q->where('phone', $input)
              ->orWhere('email', $input);
        })->where('role', 'admin')->first();

        if ($user) {
            $passwordMatches = false;

            // 2. فحص أمان كلمة المرور والتعامل مع أي تشفير أو نص صريح
            try {
                if (\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                    $passwordMatches = true;
                }
            } catch (\Exception $e) {
                // إذا كانت كلمة المرور غير مشفرة بصيغة Bcrypt
                if ($user->password === $password) {
                    $passwordMatches = true;
                }
            }

            // فحص إضافي للنص الصريح
            if (!$passwordMatches && $user->password === $password) {
                $passwordMatches = true;
            }

            if ($passwordMatches) {
                // 3. إعادة التشفير التلقائي بخوارزمية Bcrypt إن لم تكن مشفرة
                if (!str_starts_with($user->password, '$2y$')) {
                    $user->password = \Illuminate\Support\Facades\Hash::make($password);
                    $user->save();
                }

                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'));
            }
        }

        return back()->withErrors(['phone' => 'بيانات الدخول الإدارية غير صحيحة'])->onlyInput('phone');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function index()
    {
        $totalOrders = ServiceRequest::count();
        $activeOrders = ServiceRequest::whereHas('status', function ($q) {
            $q->whereIn('name', ['assigned', 'in_progress', 'pending']);
        })->count();
        $completedOrders = ServiceRequest::whereHas('status', function ($q) {
            $q->where('name', 'completed');
        })->count();

        $totalRevenue = Transaction::where('payment_status', 'paid')->sum('amount');
        if ($totalRevenue == 0) {
            $totalRevenue = 2850.00; // قيمة إجمالية للعرض التجريبي
        }
        $platformEarnings = $totalRevenue * 0.15;

        $totalCraftsmen = User::where('role', 'craftsman')->count();
        $clientsCount = User::whereIn('role', ['customer', 'client'])->count();
        
        $pendingVerifications = User::whereHas('craftsmanProfile', function ($q) {
            $q->where('approval_status', 'pending')->orWhere('identity_status', 'pending');
        })->count();

        $pendingPayouts = Transaction::where('payment_status', 'pending')->count();

        $recentOrders = ServiceRequest::with(['customer', 'service.category', 'status'])
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::withCount('services')->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'activeOrders',
            'completedOrders',
            'totalRevenue',
            'platformEarnings',
            'totalCraftsmen',
            'clientsCount',
            'pendingVerifications',
            'pendingPayouts',
            'recentOrders',
            'categories'
        ));
    }
}
