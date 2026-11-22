<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\RequestAssignment;
use App\Models\RequestAttachment;
use App\Models\RequestStatus;
use App\Models\RequestStatusHistory;
use App\Models\ServiceRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceRequest::with([
            'customer',
            'service.category',
            'status',
            'assignments.craftsman.craftsmanProfile',
        ]);

        if ($request->has('status') && $request->status !== 'all') {
            $statusVal = $request->status;
            $query->whereHas('status', function ($q) use ($statusVal) {
                $q->where('code', $statusVal)->orWhere('name', $statusVal);
            });
        }

        if ($request->has('search') && ! empty($request->search)) {
            $s = trim(str_replace('#', '', $request->search));
            $query->where(function ($q) use ($s) {
                $q->where('request_number', 'like', "%{$s}%")
                    ->orWhere('id', $s)
                    ->orWhere('transfer_reference', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhere('address_details', 'like', "%{$s}%")
                    ->orWhereHas('customer', function ($cq) use ($s) {
                        $cq->where('full_name', 'like', "%{$s}%")
                            ->orWhere('phone_encrypted', 'like', "%{$s}%")
                            ->orWhere('email_encrypted', 'like', "%{$s}%");
                    })
                    ->orWhereHas('assignments.craftsman', function ($cmq) use ($s) {
                        $cmq->where('full_name', 'like', "%{$s}%")
                            ->orWhere('phone_encrypted', 'like', "%{$s}%");
                    });
            });
        }

        $orders = $query->latest()->paginate(15);
        $categories = Category::with('services')->where('is_active', true)->get();
        $craftsmen = User::craftsmen()->with('craftsmanProfile')->get();
        $customers = User::clientAccounts()->where('account_status', 'active')->get();
        $statuses = RequestStatus::where('is_active', true)->orderBy('sort_order')->get();

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

        if (! empty($validated['craftsman_id'])) {
            $craftsman = User::with('craftsmanProfile')->findOrFail($validated['craftsman_id']);
            $isAvailable = $craftsman->account_status === 'active'
                && ($craftsman->craftsmanProfile?->approval_status ?? '') === 'approved';

            if (! $isAvailable) {
                return back()->withErrors([
                    'craftsman_id' => "لا يمكن إسناد الطلب للحرفي ({$craftsman->full_name}) لأن حسابه غير نشط أو لم يتم اعتماده.",
                ])->withInput();
            }
        }

        $statusCode = ! empty($validated['craftsman_id']) ? 'assigned' : 'pending';
        $defaultStatus = RequestStatus::where('code', $statusCode)->first();
        if (! $defaultStatus) {
            $defaultStatus = RequestStatus::firstOrCreate(
                ['code' => $statusCode],
                ['name' => $statusCode === 'assigned' ? 'تم الإسناد' : 'قيد الانتظار', 'sort_order' => 1]
            );
        }

        $order = ServiceRequest::create([
            'request_number' => 'HRF-'.date('Ymd').'-'.rand(1000, 9999),
            'customer_id' => $validated['customer_id'],
            'craftsman_id' => $validated['craftsman_id'] ?? null,
            'service_id' => $validated['service_id'],
            'description' => $validated['description'],
            'address_details' => $validated['address_details'] ?? 'العنوان الافتراضي للعميل',
            'preferred_date' => $validated['preferred_date'] ?? now()->toDateString(),
            'status_id' => $defaultStatus->id,
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('orders', 'public');
            RequestAttachment::create([
                'request_id' => $order->id,
                'file_path' => '/storage/'.$path,
                'file_type' => 'IMAGE',
                'original_file_name' => $request->file('image')->getClientOriginalName(),
                'uploaded_by' => auth()->id() ?? $order->customer_id,
            ]);
        }

        if (! empty($validated['craftsman_id'])) {
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
            'notes' => 'تم إنشاء الطلب يدوياً بواسطة الإدارة'.(! empty($validated['craftsman_id']) ? ' وإسناده للحرفي' : ''),
        ]);

        return back()->with('success', 'تم إنشاء الطلب بنجاح برقم: '.$order->request_number);
    }

    public function show($id)
    {
        $order = ServiceRequest::with([
            'customer.addresses',
            'service.category',
            'status',
            'assignments.craftsman.craftsmanProfile',
            'assignments.assignedBy',
            'history.changedBy',
            'attachments',
            'reviews',
            'transactions.paymentMethod',
        ])->findOrFail($id);

        $craftsmen = User::craftsmen()->with('craftsmanProfile')->get();
        $statuses = RequestStatus::where('is_active', true)->orderBy('sort_order')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return view('admin.orders.show', compact('order', 'craftsmen', 'statuses', 'paymentMethods'));
    }

    public function update(Request $request, $id)
    {
        $order = ServiceRequest::findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string',
            'address_details' => 'nullable|string',
            'preferred_date' => 'nullable|date',
            'preferred_time' => 'nullable',
            'image' => 'nullable|image|max:5120',
        ]);

        $order->update([
            'description' => $validated['description'],
            'address_details' => $validated['address_details'] ?? $order->address_details,
            'preferred_date' => $validated['preferred_date'] ?? $order->preferred_date,
            'preferred_time' => $validated['preferred_time'] ?? $order->preferred_time,
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('orders', 'public');
            RequestAttachment::create([
                'request_id' => $order->id,
                'file_path' => '/storage/'.$path,
                'file_type' => 'IMAGE',
                'original_file_name' => $request->file('image')->getClientOriginalName(),
                'uploaded_by' => auth()->id() ?? $order->customer_id,
            ]);
        }

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

        if (! empty($validated['craftsman_id'])) {
            $craftsman = User::with('craftsmanProfile')->findOrFail($validated['craftsman_id']);
            $isAvailable = $craftsman->account_status === 'active'
                && ($craftsman->craftsmanProfile?->approval_status ?? '') === 'approved';

            if (! $isAvailable) {
                return back()->withErrors([
                    'craftsman_id' => "لا يمكن إسناد الطلب للحرفي ({$craftsman->full_name}) لأن حسابه غير متاح حالياً.",
                ]);
            }
        }

        $oldStatusId = $order->status_id;
        $order->status_id = $validated['status_id'];
        if (array_key_exists('craftsman_id', $validated)) {
            $order->craftsman_id = $validated['craftsman_id'] ?: null;
        }
        $order->save();

        if (! empty($validated['craftsman_id'])) {
            RequestAssignment::updateOrCreate(
                ['request_id' => $order->id],
                [
                    'craftsman_id' => $validated['craftsman_id'],
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

        return back()->with('success', 'تم تحديث حالة الطلب بنجاح');
    }

    public function destroy($id)
    {
        $order = ServiceRequest::findOrFail($id);
        $reqNum = $order->request_number;
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', "تم حذف الطلب ($reqNum) نهائياً من النظام");
    }

    public function assignCraftsman(Request $request, $id)
    {
        $validated = $request->validate([
            'craftsman_id' => 'required|exists:users,id',
        ]);

        $order = ServiceRequest::findOrFail($id);
        $craftsman = User::with('craftsmanProfile')->findOrFail($validated['craftsman_id']);

        $assignedStatus = RequestStatus::where('code', 'assigned')->first();
        if ($assignedStatus) {
            $oldStatusId = $order->status_id;
            $order->status_id = $assignedStatus->id;
            $order->craftsman_id = $craftsman->id;
            $order->save();

            RequestStatusHistory::create([
                'request_id' => $order->id,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $assignedStatus->id,
                'changed_by' => auth()->id(),
                'notes' => 'تم إسناد الطلب للحرفي ('.$craftsman->full_name.') بواسطة الإدارة',
            ]);
        }

        RequestAssignment::updateOrCreate(
            ['request_id' => $order->id],
            [
                'craftsman_id' => $craftsman->id,
                'assigned_by' => auth()->id(),
                'assignment_status' => 'accepted',
                'assigned_at' => now(),
            ]
        );

        return back()->with('success', "تم إسناد الطلب ({$order->request_number}) للحرفي ({$craftsman->full_name}) بنجاح ✓");
    }

    public function confirmPayment(Request $request, $id)
    {
        $order = ServiceRequest::with('transactions')->findOrFail($id);

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'transfer_reference' => 'nullable|string|max:150',
            'receipt_image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('receipt_image')) {
            $path = $request->file('receipt_image')->store('receipts', 'public');
            $order->receipt_image = 'storage/'.$path;
        }

        if (! empty($validated['transfer_reference'])) {
            $order->transfer_reference = $validated['transfer_reference'];
        }

        $order->payment_status = 'paid';
        $order->save();

        $latestTxn = $order->transactions()->latest()->first();
        if ($latestTxn) {
            $latestTxn->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'provider_transaction_id' => $order->transfer_reference ?: $latestTxn->provider_transaction_id,
                'receipt_image' => $order->receipt_image ?: $latestTxn->receipt_image,
                'notes' => $validated['notes'] ?? $latestTxn->notes,
            ]);
        } else {
            $pm = PaymentMethod::where('code', $order->payment_method)->first()
                ?? PaymentMethod::first();
            Transaction::create([
                'transaction_number' => 'TXN-'.time().'-'.rand(100, 999),
                'request_id' => $order->id,
                'user_id' => $order->customer_id,
                'payment_method_id' => $pm?->id ?? 1,
                'amount' => $order->total_amount,
                'currency' => 'YER',
                'provider_transaction_id' => $order->transfer_reference,
                'payment_status' => 'paid',
                'receipt_image' => $order->receipt_image,
                'notes' => $validated['notes'] ?? 'تم تأكيد السداد واعتماد السند بواسطة الإدارة',
                'paid_at' => now(),
            ]);
        }

        RequestStatusHistory::create([
            'request_id' => $order->id,
            'old_status_id' => $order->status_id,
            'new_status_id' => $order->status_id,
            'changed_by' => auth()->id(),
            'notes' => 'تم تأكيد السند واعتماد عملية السداد بنجاح (المبلغ: '.number_format($order->total_amount, 2).' ر.ي)'.(! empty($validated['notes']) ? ' - '.$validated['notes'] : ''),
        ]);

        return back()->with('success', 'تم اعتماد وتأكيد عملية السداد للطلب بنجاح ✓');
    }

    public function updatePayment(Request $request, $id)
    {
        $order = ServiceRequest::findOrFail($id);

        $validated = $request->validate([
            'payment_method' => 'required|string',
            'payment_status' => 'required|in:unpaid,pending_verification,paid,refunded',
            'transfer_reference' => 'nullable|string|max:150',
            'receipt_image' => 'nullable|image|max:5120',
            'notes' => 'nullable|string',
        ]);

        $order->payment_method = $validated['payment_method'];
        $order->payment_status = $validated['payment_status'];

        if (! empty($validated['transfer_reference'])) {
            $order->transfer_reference = $validated['transfer_reference'];
        }

        if ($request->hasFile('receipt_image')) {
            $path = $request->file('receipt_image')->store('receipts', 'public');
            $order->receipt_image = 'storage/'.$path;
        }

        $order->save();

        $latestTxn = $order->transactions()->latest()->first();
        if ($latestTxn) {
            $latestTxn->update([
                'payment_status' => $validated['payment_status'] === 'pending_verification' ? 'pending' : $validated['payment_status'],
                'provider_transaction_id' => $order->transfer_reference ?: $latestTxn->provider_transaction_id,
                'receipt_image' => $order->receipt_image ?: $latestTxn->receipt_image,
                'paid_at' => $validated['payment_status'] === 'paid' ? now() : null,
                'notes' => $validated['notes'] ?? $latestTxn->notes,
            ]);
        }

        RequestStatusHistory::create([
            'request_id' => $order->id,
            'old_status_id' => $order->status_id,
            'new_status_id' => $order->status_id,
            'changed_by' => auth()->id(),
            'notes' => 'تحديث بيانات وسيلة وحالة السداد ('.$order->payment_method_name.' - '.$order->payment_status.')',
        ]);

        return back()->with('success', 'تم تحديث وسيلة وحالة الدفع بنجاح ✓');
    }
}
