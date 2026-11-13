<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Service;
use App\Models\OrderAttachment;
use App\Models\Coupon;
use App\Models\WalletTransaction;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\SupportTicket;
use App\Models\SupportMessage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with(['category', 'service', 'client', 'craftsman.craftsmanProfile', 'attachments', 'review']);

        $mode = $user->current_mode ?? ($user->role === 'craftsman' ? 'craftsman' : 'client');

        if ($mode === 'craftsman') {
            // يشمل الطلبات التي يكون الحرفي مسنداً إليها مباشرة أو عبر جدول assignments
            $query->where(function ($q) use ($user) {
                $q->where('craftsman_id', $user->id)
                  ->orWhereHas('assignments', function ($aq) use ($user) {
                      $aq->where('craftsman_id', $user->id);
                  });
            });
        } else {
            $query->where('customer_id', $user->id);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }

    public function store(Request $request)
    {
        // 1. التحقق من صحة مدخلات الطلب المرسلة من فلاتر (POST Request Validation)
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'address_text' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'required|string',
            'voice_note' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'is_urgent' => 'nullable|boolean',
            'payment_method' => 'nullable|in:cash,kuraimi,jawwali,jeeb,onecash,wallet',
            'transfer_reference' => 'nullable|string',
            'receipt_image' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'attachments' => 'nullable|array',
        ]);

        $service = Service::with('category')->findOrFail($validated['service_id']);

        // 2. التحقق من كوبون الخصم واحتساب قيمة التخفيض للعميل
        $discount = 0.00;
        if (!empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', $validated['coupon_code'])->first();
            if ($coupon && $coupon->isValidForAmount((float)$service->base_price)) {
                $discount = $coupon->calculateDiscount((float)$service->base_price);
                $coupon->increment('used_count');
            }
        }

        // 3. احتساب عمولة المنصة ومستحقات الحرفي الصافية
        $totalAmount = max(0, (float)$service->base_price - $discount);
        $commissionPercent = (float)Setting::get('platform_commission_percent', 15);
        $commission = round(($totalAmount * $commissionPercent) / 100, 2);
        $craftsmanEarnings = round($totalAmount - $commission, 2);

        // 4. معالجة وفك تشفير التسجيل الصوتي وحفظه كملف .m4a دائم على السيرفر
        $savedVoice = $this->saveVoiceNote($validated['voice_note'] ?? null);

        // 5. حفظ وإنشاء طلب الصيانة في قاعدة البيانات (جدول service_requests)
        $order = Order::create([
            'request_number' => Order::generateOrderNumber(),
            'customer_id' => $request->user()->id,
            'craftsman_id' => null,
            'category_id' => $service->category_id,
            'service_id' => $service->id,
            'address_details' => $validated['address_text'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'description' => $validated['description'],
            'voice_note' => $savedVoice,
            'preferred_date' => !empty($validated['scheduled_at']) ? date('Y-m-d', strtotime($validated['scheduled_at'])) : now()->toDateString(),
            'preferred_time' => !empty($validated['scheduled_at']) ? date('H:i:s', strtotime($validated['scheduled_at'])) : now()->toTimeString(),
            'scheduled_at' => $validated['scheduled_at'] ?? now(),
            'is_urgent' => $validated['is_urgent'] ?? false,
            'status_id' => 1,
            'status' => 'pending_craftsman',
            'pricing_type' => $service->pricing_type ?? 'fixed',
            'inspection_fee' => (float)$service->base_price,
            'labor_fee' => 0.00,
            'materials_fee' => 0.00,
            'discount' => $discount,
            'total_amount' => $totalAmount,
            'platform_commission' => $commission,
            'craftsman_earnings' => $craftsmanEarnings,
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'payment_status' => 'unpaid',
            'warranty_until' => now()->addDays($service->warranty_days ?? 30),
        ]);

        // 6. ربط التسجيل الصوتي في جدول المرفقات كملف صوتي Audio
        if ($savedVoice) {
            try {
                \App\Models\RequestAttachment::create([
                    'request_id' => $order->id,
                    'file_path' => $savedVoice,
                    'file_type' => 'audio',
                    'uploaded_by' => $request->user()->id,
                ]);
            } catch (\Exception $e) {}
        }

        // حفظ المرفقات والصور إن وجدت
        if (!empty($validated['attachments'])) {
            foreach ($validated['attachments'] as $att) {
                try {
                    \App\Models\RequestAttachment::create([
                        'request_id' => $order->id,
                        'file_path' => is_array($att) ? ($att['path'] ?? '') : $att,
                        'file_type' => is_array($att) ? ($att['type'] ?? 'image') : 'image',
                        'uploaded_by' => $request->user()->id,
                    ]);
                } catch (\Exception $e) {}
            }
        }

        // 7. في حال الدفع بالتحويل البنكي أو المحفظة الإلكترونية مع إرفاق السند
        $method = $validated['payment_method'] ?? 'cash';
        if (in_array($method, ['kuraimi', 'jawwali', 'jeeb', 'onecash']) && (!empty($validated['transfer_reference']) || !empty($validated['receipt_image']))) {
            $savedReceipt = $this->saveReceiptImage($validated['receipt_image'] ?? null);
            $pm = PaymentMethod::where('code', $method)->first();
            Transaction::create([
                'transaction_number' => 'TXN-' . time() . '-' . rand(100, 999),
                'request_id' => $order->id,
                'user_id' => $request->user()->id,
                'payment_method_id' => $pm?->id ?? 1,
                'amount' => $order->total_amount,
                'currency' => 'YER',
                'provider_transaction_id' => $validated['transfer_reference'] ?? null,
                'payment_status' => 'pending_verification',
                'receipt_image' => $savedReceipt,
                'paid_at' => now(),
            ]);
            $order->payment_status = 'pending_verification';
            $order->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء طلب الصيانة بنجاح، جاري البحث عن أقرب حرفي معتمد',
            'data' => $order->load(['category', 'service', 'attachments']),
        ], 201);
    }

    public function show($id)
    {
        $order = Order::with([
            'category',
            'service',
            'client',
            'craftsman.craftsmanProfile',
            'attachments',
            'review',
            'transactions.paymentMethod',
        ])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $order,
        ]);
    }

    // Craftsman nearby orders board
    public function nearby(Request $request)
    {
        $user = $request->user();

        $categoryIds = $user->craftsmanCategories()->pluck('categories.id')->toArray();

        $query = Order::with(['category', 'service', 'client', 'attachments'])
            ->where('status', 'pending_craftsman');

        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        $orders = $query->latest()->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }

    // Craftsman accepts an order
    public function accept(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'pending_craftsman' && !empty($order->craftsman_id)) {
            return response()->json([
                'status' => false,
                'message' => 'عذراً، هذا الطلب تم استلامه من قبل فني آخر أو تم إلغاؤه',
            ], 400);
        }

        $order->craftsman_id = $request->user()->id;
        $order->status = 'accepted';
        $order->status_id = 2; // accepted status in request_statuses if exists
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'تم قبول الطلب وتعيينك لتنفيذه بنجاح!',
            'data' => $order->fresh(['category', 'service', 'client']),
        ]);
    }

    // Update status (e.g. on_the_way -> arrived -> in_progress -> completed)
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,on_the_way,arrived,in_progress,completed,cancelled',
            'reason' => 'nullable|string',
        ]);

        $order = Order::findOrFail($id);
        $user = $request->user();

        if ($order->craftsman_id !== $user->id && $order->client_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'غير مصرح'], 403);
        }

        $order->status = $validated['status'];
        if ($validated['status'] === 'cancelled') {
            $order->cancellation_reason = $validated['reason'] ?? 'إلغاء من قبل المستخدم';
            $order->cancelled_by = $user->current_mode === 'craftsman' ? 'craftsman' : 'client';
        }

        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'data' => $order->fresh(['category', 'service', 'client', 'craftsman']),
        ]);
    }

    // Craftsman adds labor fee and spare parts / materials
    public function updateInvoice(Request $request, $id)
    {
        $validated = $request->validate([
            'labor_fee' => 'required|numeric|min:0',
            'materials_fee' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $order = Order::findOrFail($id);
        if ($order->craftsman_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'غير مصرح'], 403);
        }

        $labor = (float)$validated['labor_fee'];
        $materials = (float)$validated['materials_fee'];
        $subtotal = (float)$order->inspection_fee + $labor + $materials;
        $total = max(0, $subtotal - (float)$order->discount);

        $commissionPercent = (float)Setting::get('platform_commission_percent', 15);
        $commission = round(($total * $commissionPercent) / 100, 2);
        $craftsmanEarnings = round($total - $commission, 2);

        $order->labor_fee = $labor;
        $order->materials_fee = $materials;
        $order->total_amount = $total;
        $order->platform_commission = $commission;
        $order->craftsman_earnings = $craftsmanEarnings;
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث الفاتورة وتفاصيل العمل بنجاح',
            'data' => $order,
        ]);
    }

    // Client pays for the order with proof of payment & receipt support
    public function pay(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,kuraimi,jawwali,jeeb,onecash,wallet',
            'transfer_reference' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|string',
        ]);

        $order = Order::with('craftsman')->findOrFail($id);
        $client = $request->user();

        if ($order->client_id !== $client->id) {
            return response()->json(['status' => false, 'message' => 'غير مصرح'], 403);
        }

        $methodCode = $validated['payment_method'];
        $paymentStatus = 'paid';
        $responseMessage = 'تم تأكيد السداد بنجاح، شكراً لاختيارك حِرفة ✓';

        if ($methodCode === 'wallet') {
            if ($client->wallet_balance < $order->total_amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'رصيد محفظتك غير كافٍ لإتمام السداد، يرجى اختيار وسيلة دفع أخرى أو شحن المحفظة',
                ], 400);
            }

            $client->wallet_balance -= $order->total_amount;
            $client->save();

            WalletTransaction::create([
                'user_id' => $client->id,
                'order_id' => $order->id,
                'type' => 'withdrawal',
                'amount' => $order->total_amount,
                'balance_after' => $client->wallet_balance,
                'description' => "سداد قيمة طلب الصيانة رقم #{$order->order_number}",
                'status' => 'completed',
            ]);
        } elseif (in_array($methodCode, ['kuraimi', 'jawwali', 'jeeb', 'onecash'])) {
            $paymentStatus = 'pending_verification';
            $responseMessage = 'تم استلام بيانات التحويل بنجاح! جاري مطابقة إشعار السداد وتأكيده من قبل الإدارة.';

            $savedReceipt = $this->saveReceiptImage($validated['receipt_image'] ?? null);
            $pm = PaymentMethod::where('code', $methodCode)->first();
            Transaction::create([
                'transaction_number' => 'TXN-' . time() . '-' . rand(100, 999),
                'request_id' => $order->id,
                'user_id' => $client->id,
                'payment_method_id' => $pm?->id ?? 1,
                'amount' => $order->total_amount,
                'currency' => 'YER',
                'provider_transaction_id' => $validated['transfer_reference'] ?? null,
                'payment_status' => 'pending_verification',
                'receipt_image' => $savedReceipt,
                'paid_at' => now(),
            ]);
        }

        // Credit craftsman wallet
        if ($order->craftsman && $methodCode === 'wallet') {
            $craftsman = $order->craftsman;
            $craftsman->wallet_balance += $order->craftsman_earnings;
            $craftsman->save();

            WalletTransaction::create([
                'user_id' => $craftsman->id,
                'order_id' => $order->id,
                'type' => 'job_earning',
                'amount' => $order->craftsman_earnings,
                'balance_after' => $craftsman->wallet_balance,
                'description' => "أرباح تنفيذ الطلب #{$order->order_number} بعد خصم عمولة المنصة",
                'status' => 'completed',
            ]);
        }

        $order->payment_method = $methodCode;
        $order->payment_status = $paymentStatus;
        if ($paymentStatus === 'paid') {
            $order->status = 'completed';
        }
        $order->save();

        return response()->json([
            'status' => true,
            'message' => $responseMessage,
            'data' => $order->fresh(['category', 'service', 'client', 'craftsman']),
        ]);
    }

    // Client claims warranty for completed order
    public function claimWarranty(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|string',
        ]);

        $order = Order::findOrFail($id);
        $client = $request->user();

        if ($order->client_id !== $client->id) {
            return response()->json(['status' => false, 'message' => 'غير مصرح'], 403);
        }

        $ticket = SupportTicket::create([
            'ticket_number' => 'WAR-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
            'user_id' => $client->id,
            'subject' => "طلب ضمان وصيانة مجانية للطلب #{$order->order_number}",
            'description' => "سبب طلب الضمان: {$validated['reason']}
تفاصيل العطل: " . ($validated['notes'] ?? 'لا يوجد تفاصيل إضافية'),
            'ticket_status' => 'open',
            'priority' => 'high',
        ]);

        if (!empty($validated['attachment'])) {
            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $client->id,
                'message' => 'مرفق توضيحي لمشكلة الضمان',
                'attachment_path' => $validated['attachment'],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل طلب الضمان بنجاح، سيتواصل معك فريق الدعم لتحديد موعد الزيارة التصحيحية المجانية.',
            'data' => $ticket,
        ]);
    }

    // Update craftsman live location (called by craftsman app)
    public function updateLocation(Request $request, $id)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $order = Order::findOrFail($id);
        $cacheKey = "order_{$id}_craftsman_location";
        $data = [
            'latitude' => (float)$validated['latitude'],
            'longitude' => (float)$validated['longitude'],
            'updated_at' => now()->toIso8601String(),
        ];
        cache()->put($cacheKey, $data, now()->addHours(2));

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث الموقع الميداني للحرفي',
            'data' => $data,
        ]);
    }

    // Get craftsman live location (called by client app for live tracking)
    public function getLocation($id)
    {
        $order = Order::findOrFail($id);
        $cacheKey = "order_{$id}_craftsman_location";
        $location = cache()->get($cacheKey);

        if (!$location) {
            $location = [
                'latitude' => (float)$order->latitude + 0.0025,
                'longitude' => (float)$order->longitude + 0.0025,
                'updated_at' => now()->toIso8601String(),
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $location,
        ]);
    }

    private function saveVoiceNote(?string $voiceData): ?string
    {
        if (empty($voiceData)) {
            return null;
        }

        if (str_starts_with($voiceData, 'data:audio') || strlen($voiceData) > 200) {
            $cleanBase64 = preg_replace('#^data:audio/\w+;base64,#i', '', $voiceData);
            $cleanBase64 = str_replace(' ', '+', $cleanBase64);
            $decoded = base64_decode($cleanBase64);
            if ($decoded !== false) {
                $filename = 'voice_' . time() . '_' . rand(1000, 9999) . '.m4a';
                \Illuminate\Support\Facades\Storage::disk('public')->put('voice_notes/' . $filename, $decoded);
                return 'storage/voice_notes/' . $filename;
            }
        }

        return $voiceData;
    }

    private function saveReceiptImage(?string $receiptData): ?string
    {
        if (empty($receiptData)) {
            return null;
        }

        if (str_starts_with($receiptData, 'data:image') || strlen($receiptData) > 500) {
            $cleanBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $receiptData);
            $cleanBase64 = str_replace(' ', '+', $cleanBase64);
            $decoded = base64_decode($cleanBase64);
            if ($decoded !== false) {
                $filename = 'receipt_' . time() . '_' . rand(1000, 9999) . '.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put('receipts/' . $filename, $decoded);
                return 'storage/receipts/' . $filename;
            }
        }

        return $receiptData;
    }
}
