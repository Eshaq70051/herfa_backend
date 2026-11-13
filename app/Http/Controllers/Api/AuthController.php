<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\CraftsmanProfile;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|unique:users,phone|max:20',
            'email' => 'nullable|email|unique:users,email|max:191',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:client,craftsman',
            'city' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|max:50',
            'years_of_experience' => 'nullable|integer',
            'bio' => 'nullable|string',
            'categories' => 'nullable|array',
        ]);

        $role = $validated['role'] ?? 'client';

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'current_mode' => $role,
            'account_status' => 'active',
            'city' => $validated['city'] ?? 'صنعاء',
            'wallet_balance' => 0.00,
            'phone_verified_at' => now(),
        ]);

        if ($role === 'craftsman') {
            CraftsmanProfile::create([
                'user_id' => $user->id,
                'profession' => 'فني صيانة عامة',
                'national_id_number' => $validated['national_id'] ?? null,
                'experience_years' => $validated['years_of_experience'] ?? 1,
                'bio' => $validated['bio'] ?? null,
                'approval_status' => 'pending',
                'identity_status' => 'pending',
                'skill_status' => 'pending',
                'availability_status' => false,
                'commission_rate' => 15.00,
            ]);
        }

        $token = $user->createToken('herfa_mobile_app')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء الحساب بنجاح',
            'data' => [
                'token' => $token,
                'user' => $user->load(['craftsmanProfile', 'addresses']),
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'رقم الجوال أو كلمة المرور غير صحيحة',
            ], 401);
        }

        if ($user->account_status === 'suspended') {
            return response()->json([
                'status' => false,
                'message' => 'تم إيقاف أو حظر حسابك، يرجى التواصل مع إدارة حِرفة',
            ], 403);
        }

        $token = $user->createToken('herfa_mobile_app')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'token' => $token,
                'user' => $user->load(['craftsmanProfile', 'addresses']),
            ]
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['craftsmanProfile', 'addresses']);
        return response()->json([
            'status' => true,
            'data' => $user,
        ]);
    }

    public function switchMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:client,craftsman',
        ]);

        $user = $request->user();

        // If switching to craftsman, ensure profile exists
        if ($request->mode === 'craftsman' && !$user->craftsmanProfile) {
            CraftsmanProfile::create([
                'user_id' => $user->id,
                'profession' => 'فني صيانة',
                'experience_years' => 1,
                'approval_status' => 'pending',
                'identity_status' => 'pending',
                'availability_status' => false,
                'commission_rate' => 15.00,
            ]);
            $user->role = 'craftsman';
        }

        $user->current_mode = $request->mode;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'تم التحويل إلى نمط ' . ($request->mode === 'client' ? 'العميل' : 'الحرفي') . ' بنجاح',
            'data' => $user->load(['craftsmanProfile']),
        ]);
    }

    public function toggleOnline(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'craftsman' || $user->current_mode === 'craftsman') {
            $profile = $user->craftsmanProfile;
            if (!$profile || $profile->approval_status !== 'approved') {
                return response()->json([
                    'status' => false,
                    'message' => 'عذراً، لا يمكنك فتح الحالة واستقبال الطلبات حتى يتم اعتماد وتوثيق حسابك رسمياً من قبل الإدارة.',
                ], 403);
            }
        }

        $user->is_online = !$user->is_online;
        $user->save();

        if ($user->craftsmanProfile) {
            $user->craftsmanProfile->availability_status = $user->is_online;
            $user->craftsmanProfile->save();
        }

        return response()->json([
            'status' => true,
            'message' => $user->is_online ? 'أنت الآن متصل ومتاح لاستقبال الطلبات' : 'أنت الآن مغلق الحالة وغير متاح لاستقبال الطلبات',
            'data' => [
                'is_online' => (bool)$user->is_online,
                'availability_status' => (bool)$user->is_online,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'bio' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'iban' => 'nullable|string',
            'profession' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $user->profile_image = '/storage/' . $path;
        }

        if (!empty($validated['name'])) {
            $user->full_name = $validated['name'];
        }
        if (!empty($validated['city'])) {
            $user->city = $validated['city'];
        }
        if (isset($validated['latitude'])) {
            $user->latitude = $validated['latitude'];
        }
        if (isset($validated['longitude'])) {
            $user->longitude = $validated['longitude'];
        }
        $user->save();

        if ($user->craftsmanProfile) {
            if (!empty($validated['bio'])) {
                $user->craftsmanProfile->bio = $validated['bio'];
            }
            if (!empty($validated['profession'])) {
                $user->craftsmanProfile->profession = $validated['profession'];
            }
            $user->craftsmanProfile->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث البيانات بنجاح',
            'data' => $user->load('craftsmanProfile'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
    public function updateKyc(Request $request)
    {
        $validated = $request->validate([
            'national_id' => 'required|string|max:50',
            'years_of_experience' => 'required|integer|min:0',
            'bank_account' => 'required|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'specialties' => 'nullable|array',
            'id_document' => 'nullable|string',
            'cert_document' => 'nullable|string',
        ]);

        $user = $request->user();
        $profile = CraftsmanProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'profession' => 'فني صيانة عامة',
                'approval_status' => 'pending',
                'identity_status' => 'pending',
                'skill_status' => 'pending',
                'availability_status' => false,
                'commission_rate' => 15.00,
            ]
        );

        $profile->experience_years = $validated['years_of_experience'];
        $profile->bio = $validated['bio'] ?? $profile->bio;
        if (!empty($validated['id_document'])) {
            $profile->identity_image = $validated['id_document'];
            $profile->identity_status = 'submitted';
        }
        $profile->approval_status = 'pending';
        $profile->save();

        return response()->json([
            'status' => true,
            'message' => 'تم استلام وتحديث وثائق الاعتماد المهني بنجاح! جاري مراجعتها وتفعيل حسابك من قبل الإدارة ✓',
            'data' => $profile,
        ]);
    }
}