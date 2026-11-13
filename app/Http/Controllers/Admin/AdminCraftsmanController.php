<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CraftsmanProfile;
use App\Models\Category;

class AdminCraftsmanController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'craftsman')->with(['craftsmanProfile']);

        if ($request->has('verification') && $request->verification !== 'all') {
            $status = $request->verification;
            $query->whereHas('craftsmanProfile', function ($q) use ($status) {
                $q->where('approval_status', $status)->orWhere('identity_status', $status);
            });
        }

        if ($request->has('search') && !empty($request->search)) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhereHas('craftsmanProfile', function ($pq) use ($s) {
                      $pq->where('profession', 'like', "%{$s}%")->orWhere('city', 'like', "%{$s}%");
                  });
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
            'phone' => 'required|string|unique:users,phone|max:20',
            'email' => 'nullable|email|max:150',
            'password' => 'required|min:6',
            'profession' => 'required|string|max:100',
            'experience_years' => 'required|integer|min:0',
            'city' => 'nullable|string|max:100',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'profile_image' => 'nullable|image|max:5120',
            'identity_image' => 'nullable|image|max:5120',
        ]);

        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $profileImagePath = '/storage/' . $path;
        }

        $identityImagePath = null;
        if ($request->hasFile('identity_image')) {
            $path = $request->file('identity_image')->store('identities', 'public');
            $identityImagePath = '/storage/' . $path;
        }

        $user = User::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'craftsman',
            'account_status' => 'active',
            'current_mode' => 'craftsman',
            'profile_image' => $profileImagePath,
            'phone_verified_at' => now(),
        ]);

        CraftsmanProfile::create([
            'user_id' => $user->id,
            'profession' => $validated['profession'],
            'experience_years' => $validated['experience_years'],
            'city' => $validated['city'] ?? 'الرياض',
            'identity_image' => $identityImagePath,
            'approval_status' => 'approved',
            'identity_status' => 'verified',
            'skill_status' => 'approved',
            'availability_status' => true,
            'commission_rate' => $validated['commission_rate'] ?? 15.00,
        ]);

        return back()->with('success', 'تمت إضافة الحرفي (' . $user->full_name . ') واعتماده بنجاح');
    }

    public function show($id)
    {
        $craftsman = User::where('role', 'craftsman')
            ->with(['craftsmanProfile', 'portfolio', 'certificates', 'reviewsReceived.customer', 'assignedRequests.request.service'])
            ->findOrFail($id);

        return view('admin.craftsmen.show', compact('craftsman'));
    }

    public function update(Request $request, $id)
    {
        $craftsman = User::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'required|string|max:20|unique:users,phone,' . $craftsman->id,
            'email' => 'nullable|email|max:150',
            'profession' => 'required|string|max:100',
            'experience_years' => 'required|integer|min:0',
            'city' => 'nullable|string|max:100',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'wallet_balance' => 'nullable|numeric',
            'profile_image' => 'nullable|image|max:5120',
            'identity_image' => 'nullable|image|max:5120',
        ]);

        $userData = [
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'wallet_balance' => $validated['wallet_balance'] ?? $craftsman->wallet_balance,
        ];

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $userData['profile_image'] = '/storage/' . $path;
        }

        $craftsman->update($userData);

        if ($craftsman->craftsmanProfile) {
            $profileData = [
                'profession' => $validated['profession'],
                'experience_years' => $validated['experience_years'],
                'city' => $validated['city'] ?? $craftsman->craftsmanProfile->city,
                'commission_rate' => $validated['commission_rate'] ?? $craftsman->craftsmanProfile->commission_rate,
            ];

            if ($request->hasFile('identity_image')) {
                $path = $request->file('identity_image')->store('identities', 'public');
                $profileData['identity_image'] = '/storage/' . $path;
            }

            $craftsman->craftsmanProfile->update($profileData);
        }

        return back()->with('success', 'تم تعديل بيانات الحرفي بنجاح');
    }

    public function updateVerification(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'rejection_reason' => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'availability_status' => 'nullable|boolean',
        ]);

        $craftsman = User::findOrFail($id);
        if ($craftsman->craftsmanProfile) {
            $craftsman->craftsmanProfile->update([
                'approval_status' => $validated['status'],
                'identity_status' => ($validated['status'] === 'approved') ? 'verified' : (($validated['status'] === 'rejected') ? 'rejected' : 'pending'),
                'skill_status' => $validated['status'],
                'rejection_reason' => $validated['rejection_reason'] ?? null,
                'commission_rate' => $validated['commission_rate'] ?? $craftsman->craftsmanProfile->commission_rate,
                'availability_status' => $request->has('availability_status') ? true : $craftsman->craftsmanProfile->availability_status,
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
