<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CraftsmanProfile;
use App\Models\Currency;
use App\Models\RequestAssignment;
use App\Models\RequestFee;
use App\Models\RequestStatus;
use App\Models\RequestStatusHistory;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceRequest::with(['user', 'service', 'requestStatus']);

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('request_number', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhereHas('user', function ($uq) use ($s) {
                        $uq->where('full_name', 'like', "%{$s}%");
                    });
            });
        }

        $requests = $query->latest('id')->paginate(15);
        $statuses = RequestStatus::where('is_active', 1)->orderBy('sort_order')->get();
        $services = Service::where('is_active', 1)->get();

        return view('admin.requests.index', compact('requests', 'statuses', 'services'));
    }

    public function show($id)
    {
        $request = ServiceRequest::with([
            'user',
            'service.category',
            'userAddress.district.governorate',
            'userAddress.neighborhood',
            'requestStatus',
            'requestStatusHistories.requestStatus',
            'requestStatusHistories.user',
            'requestAssignments.craftsmanProfile.user',
            'requestFees.currency',
            'serviceRequestMaterials',
            'serviceRequestQuotes',
            'reviews',
            'transactions',
            'cancellationReason',
        ])->findOrFail($id);

        $statuses = RequestStatus::where('is_active', 1)->orderBy('sort_order')->get();
        $availableCraftsmen = CraftsmanProfile::with('user')
            ->where('approval_status', 'approved')
            ->get();
        $currencies = Currency::where('is_active', 1)->get();

        return view('admin.requests.show', compact('request', 'statuses', 'availableCraftsmen', 'currencies'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'new_status_id' => 'required|exists:request_statuses,id',
            'notes' => 'nullable|string',
        ]);

        $item = ServiceRequest::findOrFail($id);
        $oldStatusId = $item->status_id;

        $item->status_id = $request->new_status_id;
        $item->save();

        // تسجيل وتوثيق الحركة في سجل تاريخ حالات الطلب
        RequestStatusHistory::create([
            'request_id' => $item->id,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $request->new_status_id,
            'changed_by' => Auth::id(),
            'notes' => $request->notes ?? 'تغيير الحالة بواسطة مدير النظام',
        ]);

        return back()->with('success', 'تم تحديث حالة الطلب بنجاح وتوثيق الحركة في السجل.');
    }

    public function assignCraftsman(Request $request, $id)
    {
        $request->validate([
            'craftsman_id' => 'required|exists:profiles_craftsman,id',
            'response_notes' => 'nullable|string',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($id);

        RequestAssignment::create([
            'request_id' => $serviceRequest->id,
            'craftsman_id' => $request->craftsman_id,
            'assigned_by' => Auth::id(),
            'assignment_status' => 'pending',
            'assigned_at' => now(),
            'response_notes' => $request->response_notes,
        ]);

        // تحديث حالة الطلب إلى مسند في حال كان معلقاً
        $assignedStatus = RequestStatus::where('code', 'assigned')->first();
        if ($assignedStatus) {
            $oldStatus = $serviceRequest->status_id;
            $serviceRequest->status_id = $assignedStatus->id;
            $serviceRequest->save();

            RequestStatusHistory::create([
                'request_id' => $serviceRequest->id,
                'old_status_id' => $oldStatus,
                'new_status_id' => $assignedStatus->id,
                'changed_by' => Auth::id(),
                'notes' => 'تم إسناد الطلب إلى الحرفي بنجاح',
            ]);
        }

        return back()->with('success', 'تم إسناد الطلب إلى الحرفي بنجاح.');
    }

    public function addFee(Request $request, $id)
    {
        $request->validate([
            'fee_amount' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'fee_status' => 'required|in:PENDING,PAID,CANCELLED,REFUNDED',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($id);

        RequestFee::create([
            'service_request_id' => $serviceRequest->id,
            'fee_amount' => $request->fee_amount,
            'currency_id' => $request->currency_id,
            'fee_status' => $request->fee_status,
        ]);

        return back()->with('success', 'تمت إضافة الرسوم بنجاح.');
    }

    public function destroy($id)
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        $serviceRequest->delete();

        return redirect()->route('admin.requests.index')->with('success', 'تم حذف الطلب بنجاح.');
    }
}
