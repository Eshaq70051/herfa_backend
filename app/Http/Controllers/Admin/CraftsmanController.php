<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CraftsmanProfile;
use Illuminate\Http\Request;

class CraftsmanController extends Controller
{
    public function index(Request $request)
    {
        $query = CraftsmanProfile::with(['user', 'craftsmanSpecialties']);

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('identity_status')) {
            $query->where('identity_status', $request->identity_status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('user', function ($uq) use ($s) {
                $uq->where('full_name', 'like', "%{$s}%")
                    ->orWhere('phone_encrypted', 'like', "%{$s}%");
            });
        }

        $craftsmen = $query->latest('id')->paginate(15);

        return view('admin.craftsmen.index', compact('craftsmen'));
    }

    public function show($id)
    {
        $craftsman = CraftsmanProfile::with([
            'user',
            'craftsmanCertificates',
            'craftsmanDocuments',
            'craftsmanPortfolios.craftsmanPortfolioImages',
            'craftsmanServices.service',
            'craftsmanServiceAreas.district.governorate',
            'reviews.customer',
            'requestAssignments.serviceRequest',
        ])->findOrFail($id);

        return view('admin.craftsmen.show', compact('craftsman'));
    }

    public function updateApproval(Request $request, $id)
    {
        $request->validate([
            'approval_status' => 'required|in:pending,approved,rejected',
            'approval_rejection_reason' => 'nullable|string',
        ]);

        $craftsman = CraftsmanProfile::findOrFail($id);
        $craftsman->approval_status = $request->approval_status;
        $craftsman->approval_rejection_reason = $request->approval_rejection_reason;
        $craftsman->save();

        return back()->with('success', 'تم تحديث حالة اعتماد الحرفي بنجاح.');
    }

    public function updateIdentity(Request $request, $id)
    {
        $request->validate([
            'identity_status' => 'required|in:pending,verified,rejected',
            'identity_rejection_reason' => 'nullable|string',
        ]);

        $craftsman = CraftsmanProfile::findOrFail($id);
        $craftsman->identity_status = $request->identity_status;
        $craftsman->identity_rejection_reason = $request->identity_rejection_reason;
        $craftsman->save();

        return back()->with('success', 'تم تحديث حالة التحقق من الهوية بنجاح.');
    }

    public function toggleAvailability($id)
    {
        $craftsman = CraftsmanProfile::findOrFail($id);
        $craftsman->availability_status = ! $craftsman->availability_status;
        $craftsman->save();

        return back()->with('success', 'تم تبديل حالة توفر الحرفي بنجاح.');
    }

    public function destroy($id)
    {
        $craftsman = CraftsmanProfile::findOrFail($id);
        $craftsman->delete();

        return redirect()->route('admin.craftsmen.index')->with('success', 'تم حذف ملف الحرفي بنجاح.');
    }
}
