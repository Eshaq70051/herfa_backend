<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CraftsmanDocument;
use App\Models\CraftsmanProfile;
use App\Models\District;
use App\Models\Service;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:client,craftsman,customer',
            'bio' => 'nullable|string',
        ]);

        $role = $validated['role'] ?? 'client';
        $phoneHash = hash('sha256', $validated['phone']);

        if (User::where('phone_hash', $phoneHash)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'رقم الجوال مسجل مسبقاً، يرجى تسجيل الدخول مباشرة',
            ], 422);
        }

        $user = User::create([
            'full_name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'],
            'role' => $role,
            'account_status' => 'active',
            'phone_verified_at' => now(),
        ]);

        if ($role === 'craftsman') {
            CraftsmanProfile::create([
                'user_id' => $user->id,
                'bio' => $validated['bio'] ?? 'فني مسجل في منصة إنجز لي',
                'approval_status' => 'pending',
                'identity_status' => 'pending',
                'availability_status' => false,
            ]);
        }

        $token = $user->createToken('herfa_mobile_app')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء الحساب بنجاح',
            'data' => [
                'token' => $token,
                'user' => $user->load(['craftsmanProfile', 'addresses']),
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $phone = trim($request->phone);
        $phoneHash = hash('sha256', $phone);

        $user = User::where(function ($q) use ($phone, $phoneHash) {
            $q->where('phone_hash', $phoneHash)
                ->orWhere('phone_encrypted', $phone);
        })->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'رقم الجوال أو كلمة المرور غير صحيحة',
            ], 401);
        }

        if ($user->account_status === 'suspended') {
            return response()->json([
                'status' => false,
                'message' => 'تم إيقاف أو حظر حسابك، يرجى التواصل مع إدارة المنصة',
            ], 403);
        }

        $user->last_login_at = now();
        $user->save();

        $token = $user->createToken('herfa_mobile_app')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'token' => $token,
                'user' => $user->load(['craftsmanProfile', 'addresses']),
            ],
        ]);
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'purpose' => 'nullable|in:LOGIN,REGISTER,PHONE_VERIFICATION,PASSWORD_RESET',
        ]);

        $phone = $validated['phone'];
        $purpose = $validated['purpose'] ?? 'LOGIN';
        $otpCode = app()->isLocal() ? '1234' : (string) rand(1000, 9999);

        Cache::put("otp_{$phone}_{$purpose}", $otpCode, now()->addMinutes(10));

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال رمز التحقق بنجاح إلى هاتفك عبر رسالة نصية',
            'data' => [
                'phone' => $phone,
                'expires_in_minutes' => 10,
                'otp_debug' => app()->isLocal() ? $otpCode : null,
            ],
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'otp_code' => 'required|string|min:4|max:10',
            'purpose' => 'nullable|in:LOGIN,REGISTER,PHONE_VERIFICATION,PASSWORD_RESET',
            'role' => 'nullable|in:client,craftsman',
        ]);

        $phone = $validated['phone'];
        $purpose = $validated['purpose'] ?? 'LOGIN';
        $code = $validated['otp_code'];

        $cachedOtp = Cache::get("otp_{$phone}_{$purpose}");
        $isValid = ($code === $cachedOtp) || (app()->isLocal() && $code === '1234');

        if (! $isValid) {
            return response()->json([
                'status' => false,
                'message' => 'رمز التحقق غير صحيح أو انتهت صلاحيته',
            ], 400);
        }

        Cache::forget("otp_{$phone}_{$purpose}");

        $phoneHash = hash('sha256', $phone);
        $user = User::where(function ($q) use ($phone, $phoneHash) {
            $q->where('phone_hash', $phoneHash)
                ->orWhere('phone_encrypted', $phone);
        })->first();

        if (! $user) {
            $role = $validated['role'] ?? 'client';
            $user = User::create([
                'full_name' => 'مستخدم جديد',
                'phone' => $phone,
                'password' => str()->random(16),
                'role' => $role,
                'account_status' => 'active',
                'phone_verified_at' => now(),
            ]);

            if ($role === 'craftsman') {
                CraftsmanProfile::create([
                    'user_id' => $user->id,
                    'bio' => 'فني مسجل في منصة إنجز لي',
                    'approval_status' => 'pending',
                    'identity_status' => 'pending',
                    'availability_status' => false,
                ]);
            }
        } else {
            $user->phone_verified_at = now();
            $user->last_login_at = now();
            $user->save();
        }

        $token = $user->createToken('herfa_mobile_app')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'تم التحقق بنجاح وتسجيل الدخول',
            'data' => [
                'token' => $token,
                'user' => $user->load(['craftsmanProfile', 'addresses']),
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['craftsmanProfile', 'addresses', 'roles']);

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

        if ($request->mode === 'craftsman' && ! $user->craftsmanProfile) {
            CraftsmanProfile::create([
                'user_id' => $user->id,
                'bio' => 'فني مسجل في منصة إنجز لي',
                'approval_status' => 'pending',
                'identity_status' => 'pending',
                'availability_status' => false,
            ]);
            $user->role = 'craftsman';
        }

        $user->current_mode = $request->mode;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'تم التحويل إلى نمط '.($request->mode === 'client' ? 'العميل' : 'الحرفي').' بنجاح',
            'data' => $user->fresh()->load(['craftsmanProfile']),
        ]);
    }

    public function toggleOnline(Request $request)
    {
        $user = $request->user();

        $profile = $user->craftsmanProfile;
        if (! $profile || $profile->approval_status !== 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'عذراً، لا يمكنك فتح الحالة واستقبال الطلبات حتى يتم اعتماد وتوثيق حسابك رسمياً من قبل الإدارة.',
            ], 403);
        }

        $newStatus = ! $profile->availability_status;
        $profile->availability_status = $newStatus;
        $profile->save();

        return response()->json([
            'status' => true,
            'message' => $newStatus ? 'أنت الآن متصل ومتاح لاستقبال الطلبات' : 'أنت الآن مغلق الحالة وغير متاح لاستقبال الطلبات',
            'data' => [
                'is_online' => $newStatus,
                'availability_status' => $newStatus,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'nullable|string|max:191',
            'bio' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $user->profile_image = '/storage/'.$path;
        }

        if (! empty($validated['name'])) {
            $user->full_name = $validated['name'];
        }
        $user->save();

        if ($user->craftsmanProfile && ! empty($validated['bio'])) {
            $user->craftsmanProfile->bio = $validated['bio'];
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
            'full_name' => 'nullable|string|max:150',
            'name' => 'nullable|string|max:150',
            'national_id' => 'nullable|string|max:50',
            'national_id_number' => 'nullable|string|max:50',
            'years_of_experience' => 'nullable|integer|min:0',
            'age' => 'nullable|integer|min:15|max:100',
            'alternative_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'profession' => 'nullable|string|max:150',
            'custom_specialty' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'specialties' => 'nullable',
            'id_document' => 'nullable',
            'identity_image' => 'nullable',
            'id_front' => 'nullable',
            'id_back' => 'nullable',
            'national_id_back' => 'nullable',
            'cv_document' => 'nullable',
            'cv_file' => 'nullable',
            'cert_document' => 'nullable',
        ]);

        $user = $request->user();

        // 1. تحديث الاسم الكامل للحرفي
        $fullName = $validated['full_name'] ?? $validated['name'] ?? null;
        if (! empty($fullName)) {
            $user->full_name = $fullName;
            $user->save();
        }

        $profile = CraftsmanProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'approval_status' => 'pending',
                'identity_status' => 'pending',
                'availability_status' => false,
            ]
        );

        if (! empty($validated['bio'])) {
            $profile->bio = $validated['bio'];
        }
        $nationalId = $validated['national_id'] ?? $validated['national_id_number'] ?? null;
        if (! empty($nationalId)) {
            $profile->national_id_number = $nationalId;
        }
        if (isset($validated['years_of_experience'])) {
            $profile->experience_years = (int) $validated['years_of_experience'];
        }
        if (isset($validated['age'])) {
            $profile->age = (int) $validated['age'];
        }
        if (! empty($validated['alternative_phone'])) {
            $profile->alternative_phone = $validated['alternative_phone'];
        }
        if (! empty($validated['address'])) {
            $profile->address = $validated['address'];
        }
        if (isset($validated['latitude'])) {
            $profile->latitude = $validated['latitude'];
        }
        if (isset($validated['longitude'])) {
            $profile->longitude = $validated['longitude'];
        }
        if (! empty($validated['profession'])) {
            $profile->profession = $validated['profession'];
        }
        if (! empty($validated['custom_specialty'])) {
            $profile->custom_specialty = $validated['custom_specialty'];
            if (empty($profile->profession)) {
                $profile->profession = $validated['custom_specialty'];
            }
        }
        if (! empty($validated['bank_name'])) {
            $profile->bank_name = $validated['bank_name'];
        }
        if (! empty($validated['bank_account'])) {
            $profile->iban = $validated['bank_account'];
        }

        // 2. معالجة وحفظ صورة البطاقة الأمامية
        $idFrontPath = null;
        if ($request->hasFile('identity_image')) {
            $file = $request->file('identity_image');
            $stored = $file->store('identities', 'public');
            $idFrontPath = '/storage/'.$stored;
        } elseif ($request->hasFile('id_front')) {
            $file = $request->file('id_front');
            $stored = $file->store('identities', 'public');
            $idFrontPath = '/storage/'.$stored;
        } elseif (! empty($validated['identity_image']) && is_string($validated['identity_image'])) {
            $idFrontPath = $validated['identity_image'];
        } elseif (! empty($validated['id_document']) && is_string($validated['id_document'])) {
            $idFrontPath = $validated['id_document'];
        }

        if ($idFrontPath) {
            $profile->identity_image = $idFrontPath;
            CraftsmanDocument::updateOrCreate(
                ['craftsman_profile_id' => $profile->id, 'document_type' => 'NATIONAL_ID_FRONT'],
                [
                    'title' => 'بطاقة الهوية الوطنية - الوجه الأمامي',
                    'file_path' => $idFrontPath,
                    'review_status' => 'PENDING',
                ]
            );
        }

        // 3. معالجة وحفظ صورة البطاقة الخلفية
        $idBackPath = null;
        if ($request->hasFile('national_id_back')) {
            $file = $request->file('national_id_back');
            $stored = $file->store('identities', 'public');
            $idBackPath = '/storage/'.$stored;
        } elseif ($request->hasFile('id_back')) {
            $file = $request->file('id_back');
            $stored = $file->store('identities', 'public');
            $idBackPath = '/storage/'.$stored;
        } elseif (! empty($validated['national_id_back']) && is_string($validated['national_id_back'])) {
            $idBackPath = $validated['national_id_back'];
        }

        if ($idBackPath) {
            $profile->national_id_back = $idBackPath;
            CraftsmanDocument::updateOrCreate(
                ['craftsman_profile_id' => $profile->id, 'document_type' => 'NATIONAL_ID_BACK'],
                [
                    'title' => 'بطاقة الهوية الوطنية - الوجه الخلفي',
                    'file_path' => $idBackPath,
                    'review_status' => 'PENDING',
                ]
            );
        }

        // 4. معالجة وحفظ ملف السيرة الذاتية (CV PDF)
        $cvPath = null;
        if ($request->hasFile('cv_document')) {
            $file = $request->file('cv_document');
            $stored = $file->store('cvs', 'public');
            $cvPath = '/storage/'.$stored;
        } elseif ($request->hasFile('cv_file')) {
            $file = $request->file('cv_file');
            $stored = $file->store('cvs', 'public');
            $cvPath = '/storage/'.$stored;
        } elseif (! empty($validated['cv_document']) && is_string($validated['cv_document'])) {
            $cvPath = $validated['cv_document'];
        }

        if ($cvPath) {
            $profile->cv_document = $cvPath;
            CraftsmanDocument::updateOrCreate(
                ['craftsman_profile_id' => $profile->id, 'document_type' => 'CV'],
                [
                    'title' => 'السيرة الذاتية (CV)',
                    'file_path' => $cvPath,
                    'review_status' => 'PENDING',
                ]
            );
        }

        // 5. شهادة الكفاءة والخبرة الإضافية
        if (! empty($validated['cert_document'])) {
            CraftsmanDocument::updateOrCreate(
                ['craftsman_profile_id' => $profile->id, 'document_type' => 'certificate'],
                [
                    'title' => 'شهادة الخبرة والاعتماد المهني',
                    'file_path' => $validated['cert_document'],
                    'review_status' => 'PENDING',
                ]
            );
        }

        // 6. ربط التخصصات والخدمات المختارة
        $specialtiesList = $validated['specialties'] ?? [];
        if (is_string($specialtiesList)) {
            $specialtiesList = json_decode($specialtiesList, true) ?? [$specialtiesList];
        }

        if (! empty($profile->custom_specialty) && ! in_array($profile->custom_specialty, $specialtiesList, true)) {
            $specialtiesList[] = $profile->custom_specialty;
        }

        if (! empty($specialtiesList)) {
            $years = (int) ($validated['years_of_experience'] ?? $profile->experience_years ?? 3);
            $serviceIds = [];
            foreach ($specialtiesList as $spec) {
                if (is_numeric($spec)) {
                    $serviceIds[] = (int) $spec;
                } else {
                    $s = Service::where('name', 'like', "%{$spec}%")->first();
                    if ($s) {
                        $serviceIds[] = $s->id;
                    }
                }
            }
            if (! empty($serviceIds)) {
                $attachData = [];
                foreach ($serviceIds as $sid) {
                    $attachData[$sid] = [
                        'experience_years' => $years,
                        'approval_status' => 'pending',
                        'is_active' => true,
                    ];
                }
                $user->craftsmanServices()->syncWithoutDetaching($attachData);
            }
        }

        // حفظ الموقع والعنوان للمستخدم إذا وجد
        if (! empty($profile->latitude) && ! empty($profile->longitude)) {
            $defaultDistrictId = District::first()?->id ?? 1;
            UserAddress::updateOrCreate(
                ['user_id' => $user->id, 'label' => 'مقر العمل المهني'],
                [
                    'district_id' => $defaultDistrictId,
                    'address_details' => $profile->address ?? 'مقر العمل المحدد على الخريطة',
                    'latitude' => $profile->latitude,
                    'longitude' => $profile->longitude,
                    'is_default' => true,
                ]
            );
        }

        $profile->approval_status = 'pending';
        $profile->identity_status = 'pending';
        $profile->save();

        $user->current_mode = 'client';
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'تم استلام وتحديث وثائق وبيانات الاعتماد المهني بنجاح! جاري مراجعتها وتفعيل حسابك من قبل الإدارة ✓',
            'data' => [
                'user' => $user->fresh()->load(['craftsmanProfile.documents', 'roles']),
                'profile' => $profile->fresh()->load('documents'),
            ],
        ]);
    }
}
