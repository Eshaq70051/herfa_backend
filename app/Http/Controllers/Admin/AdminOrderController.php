<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Category;
use App\Models\Service;
use App\Models\RequestStatus;
use App\Models\RequestAssignment;
use App\Models\RequestStatusHistory;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceRequest::with(['customer', 'service.category', 'status', 'assignments.craftsman.craftsmanProfile']);

        if ($request->has('status') && $request->status !== 'all') {
            $statusName = $request->status;
            $query->whereHas('status', function ($q) use ($statusName) {
                $q->where('name', $statusName);
            });
        }

        if ($request->has('search') && !empty($request->search)) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('request_number', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($cq) use ($s) {
                      $cq->where('full_name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(15);
        $categories = Category::with('services')->where('is_active', true)->get();
        $craftsmen = User::where('role', 'craftsman')->with('craftsmanProfile')->get();
        $customers = User::whereIn('role', ['customer', 'client'])->where('account_status', 'active')->get();
        $statuses = RequestStatus::all();

        return view('admin.orders.index', compact('orders', 'categories', 'craftsmen', 'customers', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'description' => 'required|string',
            'address_details' => 'nullable|string',
            'preferred_date' => 'nullable|date',
            'craftsman_id' => 'nullable|exists:users,id',
        ]);

        // Validate craftsman availability status if assigned
        if (!empty($validated['craftsman_id'])) {
            $craftsman = User::with('craftsmanProfile')->findOrFail($validated['craftsman_id']);
            $isAvailable = $craftsman->account_status === 'active'
                && ($craftsman->craftsmanProfile->approval_status ?? '') === 'approved'
                && (($craftsman->craftsmanProfile->availability_status ?? false) || $craftsman->is_online);

            if (!$isAvailable) {
                return back()->withErrors([
                    'craftsman_id' => "لا يمكن إسناد الطلب للحرفي ({$craftsman->full_name}) لأن حالته مغلقة حالياً (غير متاح لاستقبال الطلبات) أو لم يتم اعتماده."
                ])->withInput();
            }
        }

        $defaultStatus = RequestStatus::where('name', !empty($validated['craftsman_id']) ? 'assigned' : 'pending')->first();
        if (!$defaultStatus) {
            $defaultStatus = RequestStatus::firstOrCreate(['name' => 'pending'], ['description' => 'بانتظار قبول الحرفي']);
        }

        $order = ServiceRequest::create([
            'request_number' => 'HRF-' . date('Ymd') . '-' . rand(100, 999),
            'customer_id' => $validated['customer_id'],
            'service_id' => $validated['service_id'],
            'description' => $validated['description'],
            'address_details' => $validated['address_details'] ?? 'الرياض - حي النرجس',
            'preferred_date' => $validated['preferred_date'] ?? now()->toDateString(),
            'status_id' => $defaultStatus->id,
        ]);

        if (!empty($validated['craftsman_id'])) {
            RequestAssignment::create([
                'request_id' => $order->id,
                'craftsman_id' => $validated['craftsman_id'],
                'assigned_by' => auth()->id(),
                'assignment_status' => 'accepted',
                'assigned_at' => now(),
            ]);
        }

        RequestStatusHistory::create([
            'request_id' => $order->id,
            'new_status_id' => $defaultStatus->id,
            'changed_by' => auth()->id(),
            'notes' => 'تم إنشاء الطلب يدوياً بواسطة الإدارة' . (!empty($validated['craftsman_id']) ? ' وإسناده للحرفي' : ''),
        ]);

        return back()->with('success', 'تم إنشاء الطلب بنجاح برقم: ' . $order->request_number);
    }

    public function show($id)
    {
        $order = ServiceRequest::with([
            'customer.addresses',
            'service.category',
            'status',
            'assignments.craftsman.craftsmanProfile',
            'history.changedBy',
            'attachments',
            'reviews',
            'transactions.paymentMethod',
        ])->findOrFail($id);

        $craftsmen = User::where('role', 'craftsman')->with('craftsmanProfile')->get();
        $statuses = RequestStatus::all();

        return view('admin.orders.show', compact('order', 'craftsmen', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $order = ServiceRequest::findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string',
            'address_details' => 'nullable|string',
            'preferred_date' => 'nullable|date',
            'preferred_time' => 'nullable',
        ]);

        $order->update($validated);

        return back()->with('success', 'تم تعديل بيانات الطلب بنجاح');
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:request_statuses,id',
            'craftsman_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $order = ServiceRequest::findOrFail($id);

        // Validate craftsman availability status if assigning/reassigning
        if (!empty($validated['craftsman_id'])) {
            $craftsman = User::with('craftsmanProfile')->findOrFail($validated['craftsman_id']);
            $isAvailable = $craftsman->account_status === 'active'
                && ($craftsman->craftsmanProfile->approval_status ?? '') === 'approved'
                && (($craftsman->craftsmanProfile->availability_status ?? false) || $craftsman->is_online);

            if (!$isAvailable) {
                return back()->withErrors([
                    'craftsman_id' => "لا يمكن إسناد الطلب للحرفي ({$craftsman->full_name}) لأن حالته مغلقة حالياً (غير متاح لاستقبال الطلبات) أو لم يتم توثيقه."
                ]);
            }
        }

        $oldStatusId = $order->status_id;
        $order->status_id = $validated['status_id'];
        $order->save();

        if (!empty($validated['craftsman_id'])) {
            RequestAssignment::updateOrCreate(
                ['request_id' => $order->id, 'craftsman_id' => $validated['craftsman_id']],
                [
                    'assigned_by' => auth()->id(),
                    'assignment_status' => 'accepted',
                    'assigned_at' => now(),
                ]
            );
        }

        RequestStatusHistory::create([
            'request_id' => $order->id,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $validated['status_id'],
            'changed_by' => auth()->id(),
            'notes' => $validated['notes'] ?? 'تحديث الحالة من لوحة الإدارة',
        ]);

        return back()->with('success', 'تم تحديث حالة الطلب وإسناده بنجاح');
    }

    public function destroy($id)
    {
        $order = ServiceRequest::findOrFail($id);
        $reqNum = $order->request_number;
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', "تم حذف الطلب ($reqNum) نهائياً من النظام");
    }

    /**
     * إسناد أو تغيير الحرفي المسند للطلب مباشرة من جدول الطلبات
     */
    public function assignCraftsman(Request $request, $id)
    {
        $validated = $request->validate([
            'craftsman_id' => 'required|exists:users,id',
        ]);

        $order = ServiceRequest::findOrFail($id);
        $craftsman = User::with('craftsmanProfile')->findOrFail($validated['craftsman_id']);

        // تحديث الحرفي في جدول الطلب الأساسي
        $order->craftsman_id = $craftsman->id;

        // إذا كان الطلب بانتظار فني يتم تحويله إلى حالة مسند/مقبول
        $assignedStatus = RequestStatus::where('name', 'assigned')->first();
        if ($assignedStatus && ($order->status_id == 1 || empty($order->status_id))) {
            $order->status_id = $assignedStatus->id;
        }
        $order->status = 'accepted';
        $order->save();

        // تسجيل أو تحديث الإسناد في جدول RequestAssignment
        RequestAssignment::updateOrCreate(
            ['request_id' => $order->id],
            [
                'craftsman_id' => $craftsman->id,
                'assigned_by' => auth()->id(),
                'assignment_status' => 'accepted',
                'assigned_at' => now(),
            ]
        );

        // تسجيل في سجل الحالات
        RequestStatusHistory::create([
            'request_id' => $order->id,
            'old_status_id' => $order->status_id,
            'new_status_id' => $assignedStatus?->id ?? $order->status_id,
            'changed_by' => auth()->id(),
            'notes' => 'تم إسناد الطلب للحرفي (' . $craftsman->full_name . ') مباشرة من لوحة الإدارة',
        ]);

        return back()->with('success', "تم إسناد الطلب ({$order->request_number}) للحرفي ({$craftsman->full_name}) بنجاح ✓");
    }
}
