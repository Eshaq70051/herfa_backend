<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CraftsmanDocument;
use App\Models\CraftsmanProfile;
use App\Models\CraftsmanSpecialty;
use App\Models\User;
use Illuminate\Http\Request;

class AdminCraftsmanController extends Controller
{
    public function index(Request $request)
    {
        $query = User::craftsmen()->with(['craftsmanProfile', 'roles', 'craftsmanServices']);

        if ($request->has('verification') && $request->verification !== 'all') {
            $status = $request->verification;
            $query->whereHas('craftsmanProfile', function ($q) use ($status) {
                $q->where('approval_status', $status)->orWhere('identity_status', $status);
            });
        }

        if ($request->has('search') && ! empty($request->search)) {
            $s = $request->search;
            $sHash = hash('sha256', $s);
            $query->where(function ($q) use ($s, $sHash) {
                $q->where('full_name', 'like', "%{$s}%")
                    ->orWhere('phone_encrypted', 'like', "%{$s}%")
                    ->orWhere('phone_hash', $sHash)
                    ->orWhere('email_encrypted', 'like', "%{$s}%");
            });
        }

        $craftsmen = $query->latest()->paginate(15);
        $categories = Category::all();

        return view('admin.craftsmen.index', compact('craftsmen', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'password' => 'required|min:6',
            'category_id' => 'nullable|exists:categories,id',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|image|max:5120',
            'identity_image' => 'nullable|image|max:5120',
            'national_id_back' => 'nullable|image|max:5120',
            'license_image' => 'nullable|image|max:5120',
        ]);

        $phoneHash = hash('sha256', $validated['phone']);
        if (User::where('phone_hash', $phoneHash)->exists()) {
            return back()->withErrors(['phone' => 'رقم الهاتف مستخدم مسبقاً'])->withInput();
        }

        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $profileImagePath = '/storage/'.$path;
        }

        $user = User::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'],
            'role' => 'craftsman',
            'account_status' => 'active',
            'profile_image' => $profileImagePath,
            'phone_verified_at' => now(),
        ]);

        $profile = CraftsmanProfile::create([
            'user_id' => $user->id,
            'bio' => $validated['bio'] ?? 'حرفي معتمد في منصة إنجز لي',
            'identity_status' => 'verified',
            'approval_status' => 'approved',
            'availability_status' => true,
        ]);

        // رفع وربط وثائق ومستندات الهوية للحرفي
        if ($request->hasFile('identity_image')) {
            $path = $request->file('identity_image')->store('identities', 'public');
            CraftsmanDocument::create([
                'craftsman_profile_id' => $profile->id,
                'document_type' => 'NATIONAL_ID_FRONT',
                'title' => 'الهوية الوطنية - الوجه الأمامي',
                'file_path' => '/storage/'.$path,
                'review_status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        }

        if ($request->hasFile('national_id_back')) {
            $path = $request->file('national_id_back')->store('identities', 'public');
            CraftsmanDocument::create([
                'craftsman_profile_id' => $profile->id,
                'document_type' => 'NATIONAL_ID_BACK',
                'title' => 'الهوية الوطنية - الوجه الخلفي',
                'file_path' => '/storage/'.$path,
                'review_status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        }

        if ($request->hasFile('license_image')) {
            $path = $request->file('license_image')->store('identities', 'public');
            CraftsmanDocument::create([
                'craftsman_profile_id' => $profile->id,
                'document_type' => 'PROFESSIONAL_CERTIFICATE',
                'title' => 'الشهادة المهنية أو رخصة العمل',
                'file_path' => '/storage/'.$path,
                'review_status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        }

        // ربط الحرفي بالقسم والمهنة التخصصية
        if (! empty($validated['category_id'])) {
            CraftsmanSpecialty::firstOrCreate([
                'craftsman_id' => $user->id,
                'category_id' => $validated['category_id'],
            ]);
        }

        return back()->with('success', 'تمت إضافة الحرفي ('.$user->full_name.') واعتماده بنجاح');
    }

    public function show($id)
    {
        $craftsman = User::craftsmen()
            ->with([
                'craftsmanProfile.documents',
                'portfolio',
                'certificates',
                'reviewsReceived.customer',
                'assignedRequests' => function ($q) {
                    $q->with(['request.service.category', 'request.status', 'request.customer', 'assignedBy'])->latest();
                },
                'craftsmanServices.category',
                'transactions' => function ($q) {
                    $q->latest();
                },
            ])
            ->findOrFail($id);

        return view('admin.craftsmen.show', compact('craftsman'));
    }

    public function update(Request $request, $id)
    {
        $craftsman = User::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'category_id' => 'nullable|exists:categories,id',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|image|max:5120',
            'identity_image' => 'nullable|image|max:5120',
            'national_id_back' => 'nullable|image|max:5120',
            'license_image' => 'nullable|image|max:5120',
        ]);

        $phoneHash = hash('sha256', $validated['phone']);
        if (User::where('phone_hash', $phoneHash)->where('id', '!=', $craftsman->id)->exists()) {
            return back()->withErrors(['phone' => 'رقم الهاتف مستخدم مسبقاً'])->withInput();
        }

        $userData = [
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
        ];

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $userData['profile_image'] = '/storage/'.$path;
        }

        $craftsman->update($userData);

        $profile = $craftsman->craftsmanProfile;
        if (! $profile) {
            $profile = CraftsmanProfile::create([
                'user_id' => $craftsman->id,
                'bio' => $validated['bio'] ?? '',
                'approval_status' => 'approved',
                'identity_status' => 'verified',
                'availability_status' => true,
            ]);
        } else {
            $profile->update([
                'bio' => $validated['bio'] ?? $profile->bio,
            ]);
        }

        if ($request->hasFile('identity_image')) {
            $path = $request->file('identity_image')->store('identities', 'public');
            CraftsmanDocument::updateOrCreate(
                ['craftsman_profile_id' => $profile->id, 'document_type' => 'NATIONAL_ID_FRONT'],
                ['file_path' => '/storage/'.$path, 'review_status' => 'approved', 'title' => 'الهوية - الوجه الأمامي']
            );
        }

        if ($request->hasFile('national_id_back')) {
            $path = $request->file('national_id_back')->store('identities', 'public');
            CraftsmanDocument::updateOrCreate(
                ['craftsman_profile_id' => $profile->id, 'document_type' => 'NATIONAL_ID_BACK'],
                ['file_path' => '/storage/'.$path, 'review_status' => 'approved', 'title' => 'الهوية - الوجه الخلفي']
            );
        }

        if ($request->hasFile('license_image')) {
            $path = $request->file('license_image')->store('identities', 'public');
            CraftsmanDocument::updateOrCreate(
                ['craftsman_profile_id' => $profile->id, 'document_type' => 'PROFESSIONAL_CERTIFICATE'],
                ['file_path' => '/storage/'.$path, 'review_status' => 'approved', 'title' => 'الشهادة المهنية']
            );
        }

        if (! empty($validated['category_id'])) {
            CraftsmanSpecialty::firstOrCreate([
                'craftsman_id' => $craftsman->id,
                'category_id' => $validated['category_id'],
            ]);
        }

        return back()->with('success', 'تم تعديل بيانات الحرفي بنجاح');
    }

    public function updateVerification(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'rejection_reason' => 'nullable|string',
            'availability_status' => 'nullable|boolean',
        ]);

        $craftsman = User::findOrFail($id);
        $profile = $craftsman->craftsmanProfile;
        if ($profile) {
            $profile->update([
                'approval_status' => $validated['status'],
                'identity_status' => ($validated['status'] === 'approved') ? 'verified' : (($validated['status'] === 'rejected') ? 'rejected' : 'pending'),
                'approval_rejection_reason' => $validated['rejection_reason'] ?? null,
                'availability_status' => $request->has('availability_status') ? true : $profile->availability_status,
            ]);
        }

        return back()->with('success', 'تم تحديث حالة اعتماد وتوثيق الحرفي بنجاح');
    }

    public function toggleBlock($id)
    {
        $user = User::findOrFail($id);
        $user->account_status = ($user->account_status === 'active') ? 'suspended' : 'active';
        $user->save();

        $msg = ($user->account_status === 'active') ? 'تم تفعيل حساب الحرفي بنجاح' : 'تم تعطيل/حظر حساب الحرفي';

        return back()->with('success', $msg);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $name = $user->full_name;
        $user->delete();

        return redirect()->route('admin.craftsmen.index')->with('success', "تم حذف الحرفي ($name) نهائياً من المنظومة");
    }
}
