<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\RequestAssignment;
use App\Models\RequestAttachment;
use App\Models\RequestStatus;
use App\Models\Service;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            $statusCode = $request->status === 'pending_craftsman' ? 'pending' : $request->status;
            $statusModel = RequestStatus::where('code', $statusCode)->first();
            if ($statusModel) {
                $query->where('status_id', $statusModel->id);
            }
        }

        $orders = $query->latest()->paginate(15);

        $orders->getCollection()->transform(function ($item) use ($user) {
            $this->maskOrderPrivacy($item, $user);

            return $item;
        });

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
            'image' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'attachments' => 'nullable|array',
        ]);

        $service = Service::with('category')->findOrFail($validated['service_id']);

        // 2. التحقق من كوبون الخصم واحتساب قيمة التخفيض للعميل
        $discount = 0.00;
        if (! empty($validated['coupon_code'])) {
            $discount = min((float) $service->base_price * 0.10, 50.00);
        }

        // 3. احتساب عمولة المنصة ومستحقات الحرفي الصافية
        $totalAmount = max(0, (float) $service->base_price - $discount);
        $commissionPercent = (float) AppSetting::get('platform_commission_percent', 15);
        $commission = round(($totalAmount * $commissionPercent) / 100, 2);
        $craftsmanEarnings = round($totalAmount - $commission, 2);

        // 4. معالجة وحفظ التسجيل الصوتي وصورة الطلب
        $savedVoice = $this->saveVoiceNote($validated['voice_note'] ?? null);
        $savedImage = $this->saveOrderImage($validated['image'] ?? null);

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
            'image' => $savedImage,
            'voice_note' => $savedVoice,
            'preferred_date' => ! empty($validated['scheduled_at']) ? date('Y-m-d', strtotime($validated['scheduled_at'])) : now()->toDateString(),
            'preferred_time' => ! empty($validated['scheduled_at']) ? date('H:i:s', strtotime($validated['scheduled_at'])) : now()->toTimeString(),
            'scheduled_at' => $validated['scheduled_at'] ?? now(),
            'is_urgent' => $validated['is_urgent'] ?? false,
            'is_emergency' => $validated['is_urgent'] ?? false,
            'completion_otp' => str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
            'status_id' => 1,
            'status' => 'pending_craftsman',
            'pricing_type' => $service->pricing_type ?? 'fixed',
            'inspection_fee' => (float) $service->base_price,
            'estimated_cost' => $totalAmount,
            'final_total_amount' => $totalAmount,
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
                RequestAttachment::create([
                    'request_id' => $order->id,
                    'file_path' => $savedVoice,
                    'file_type' => 'audio',
                    'uploaded_by' => $request->user()->id,
                ]);
            } catch (\Exception $e) {
            }
        }

        // حفظ المرفقات والصور إن وجدت
        if (! empty($validated['attachments'])) {
            foreach ($validated['attachments'] as $att) {
                try {
                    RequestAttachment::create([
                        'request_id' => $order->id,
                        'file_path' => is_array($att) ? ($att['path'] ?? '') : $att,
                        'file_type' => is_array($att) ? ($att['type'] ?? 'image') : 'image',
                        'uploaded_by' => $request->user()->id,
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        // 7. في حال الدفع بالتحويل البنكي أو المحفظة الإلكترونية مع إرفاق السند
        $method = $validated['payment_method'] ?? 'cash';
        if (in_array($method, ['kuraimi', 'jawwali', 'jeeb', 'onecash']) && (! empty($validated['transfer_reference']) || ! empty($validated['receipt_image']))) {
            $savedReceipt = $this->saveReceiptImage($validated['receipt_image'] ?? null);
            $pm = PaymentMethod::where('code', $method)->first();
            Transaction::create([
                'transaction_number' => 'TXN-'.time().'-'.rand(100, 999),
                'request_id' => $order->id,
                'user_id' => $request->user()->id,
                'payment_method_id' => $pm?->id ?? 1,
                'amount' => $order->total_amount,
                'currency_id' => 1,
                'provider_transaction_id' => $validated['transfer_reference'] ?? null,
                'payment_status' => 'pending_verification',
                'receipt_image' => $savedReceipt,
                'paid_at' => now(),
            ]);
            $order->payment_status = 'pending_verification';
            $order->receipt_image = $savedReceipt;
            $order->transfer_reference = $validated['transfer_reference'] ?? null;
            $order->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء طلب الصيانة بنجاح، جاري البحث عن أقرب حرفي معتمد',
            'data' => $order->load(['category', 'service', 'attachments']),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $order = Order::with([
            'category',
            'service',
            'client',
            'craftsman.craftsmanProfile',
            'attachments',
            'review',
            'transactions.paymentMethod',
            'assignments.craftsman',
        ])->findOrFail($id);

        $user = $request->user();
        if ($user && ($user->role === 'craftsman' || ($user->current_mode ?? '') === 'craftsman' || $order->craftsman_id === $user->id)) {
            $assignment = RequestAssignment::where('request_id', $order->id)
                ->where('craftsman_id', $user->id)
                ->first();
            if ($assignment && empty($assignment->opened_at)) {
                $assignment->update(['opened_at' => now()]);
            }
        }

        $this->maskOrderPrivacy($order, $user);

        return response()->json([
            'status' => true,
            'data' => $order,
        ]);
    }

    // Craftsman nearby orders board
    public function nearby(Request $request)
    {
        $user = $request->user();

        $categoryIds = $user->craftsmanServices()->pluck('category_id')->unique()->toArray();

        $query = Order::with(['category', 'service', 'client', 'attachments'])
            ->whereNull('craftsman_id')
            ->where('status_id', 1);

        if (! empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        $orders = $query->latest()->paginate(15);

        $orders->getCollection()->transform(function ($item) use ($user) {
            $this->maskOrderPrivacy($item, $user);

            return $item;
        });

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }

    // Craftsman accepts an order
    public function accept(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if (! empty($order->craftsman_id) && $order->craftsman_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'عذراً، هذا الطلب تم استلامه من قبل فني آخر أو تم إلغاؤه',
            ], 400);
        }

        $acceptedStatus = RequestStatus::where('code', 'accepted')->first();

        $order->craftsman_id = $request->user()->id;
        $order->status_id = $acceptedStatus?->id ?? 3;
        $order->save();

        RequestAssignment::updateOrCreate(
            ['request_id' => $order->id, 'craftsman_id' => $request->user()->id],
            [
                'assigned_by' => $request->user()->id,
                'assignment_status' => 'accepted',
                'opened_at' => now(),
                'accepted_at' => now(),
                'responded_at' => now(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'تم قبول الطلب وتعيينك لتنفيذه بنجاح!',
            'data' => $order->fresh(['category', 'service', 'client', 'craftsman']),
        ]);
    }

    // Update status (e.g. on_the_way -> arrived -> in_progress -> completed)
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,on_the_way,arrived,in_progress,completed,cancelled',
            'reason' => 'nullable|string',
            'completion_image' => 'nullable|string',
        ]);

        $order = Order::findOrFail($id);
        $user = $request->user();

        if ($order->craftsman_id !== $user->id && $order->customer_id !== $user->id && ! $user->isAdmin()) {
            return response()->json(['status' => false, 'message' => 'غير مصرح'], 403);
        }

        $order->status = $validated['status'];
        if ($validated['status'] === 'cancelled') {
            $order->cancellation_notes = $validated['reason'] ?? 'إلغاء من قبل المستخدم';
        }

        if ($validated['status'] === 'completed') {
            $order->completed_at = now();
            $order->status_id = 5;

            $savedCompletion = null;
            if ($request->hasFile('completion_image')) {
                $path = $request->file('completion_image')->store('completions', 'public');
                $savedCompletion = 'storage/'.$path;
            } elseif (! empty($request->completion_image)) {
                $savedCompletion = $this->saveCompletionImage($request->completion_image);
            }

            if ($savedCompletion) {
                $order->completion_image = $savedCompletion;
                try {
                    RequestAttachment::create([
                        'request_id' => $order->id,
                        'file_path' => $savedCompletion,
                        'file_type' => 'image',
                        'uploaded_by' => $user->id,
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        $order->save();

        $freshOrder = $order->fresh(['category', 'service', 'client', 'craftsman']);
        $this->maskOrderPrivacy($freshOrder, $user);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'data' => $freshOrder,
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
        if ($order->craftsman_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json(['status' => false, 'message' => 'غير مصرح'], 403);
        }

        $labor = (float) $validated['labor_fee'];
        $materials = (float) $validated['materials_fee'];
        $subtotal = (float) $order->inspection_fee + $labor + $materials;
        $total = max(0, $subtotal - (float) $order->discount);

        $commissionPercent = (float) AppSetting::get('platform_commission_percent', 15);
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

        if ($order->customer_id !== $client->id) {
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

            Transaction::create([
                'transaction_number' => 'TXN-'.time().'-'.rand(100, 999),
                'request_id' => $order->id,
                'user_id' => $client->id,
                'payment_method_id' => 1,
                'amount' => -$order->total_amount,
                'currency_id' => 1,
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);
        } elseif (in_array($methodCode, ['kuraimi', 'jawwali', 'jeeb', 'onecash'])) {
            $paymentStatus = 'pending_verification';
            $responseMessage = 'تم استلام بيانات التحويل بنجاح! جاري مطابقة إشعار السداد وتأكيده من قبل الإدارة.';

            $savedReceipt = $this->saveReceiptImage($validated['receipt_image'] ?? null);
            $pm = PaymentMethod::where('code', $methodCode)->first();
            Transaction::create([
                'transaction_number' => 'TXN-'.time().'-'.rand(100, 999),
                'request_id' => $order->id,
                'user_id' => $client->id,
                'payment_method_id' => $pm?->id ?? 1,
                'amount' => $order->total_amount,
                'currency_id' => 1,
                'provider_transaction_id' => $validated['transfer_reference'] ?? null,
                'payment_status' => 'pending_verification',
                'receipt_image' => $savedReceipt,
                'paid_at' => now(),
            ]);
        }

        // Credit craftsman wallet
        if ($order->craftsman && $methodCode === 'wallet') {
            $craftsman = $order->craftsman;

            Transaction::create([
                'transaction_number' => 'TXN-'.time().'-'.rand(100, 999),
                'request_id' => $order->id,
                'user_id' => $craftsman->id,
                'payment_method_id' => 1,
                'amount' => $order->craftsman_earnings,
                'currency_id' => 1,
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        $order->payment_method = $methodCode;
        $order->payment_status = $paymentStatus;
        if (! empty($savedReceipt)) {
            $order->receipt_image = $savedReceipt;
        }
        if (! empty($validated['transfer_reference'])) {
            $order->transfer_reference = $validated['transfer_reference'];
        }
        if ($paymentStatus === 'paid') {
            $order->status = 'completed';
        }
        $order->save();

        $freshOrder = $order->fresh(['category', 'service', 'client', 'craftsman']);
        $this->maskOrderPrivacy($freshOrder, $client);

        return response()->json([
            'status' => true,
            'message' => $responseMessage,
            'data' => $freshOrder,
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
            'ticket_number' => 'WAR-'.str_pad($order->id, 5, '0', STR_PAD_LEFT),
            'user_id' => $client->id,
            'subject' => "طلب ضمان وصيانة مجانية للطلب #{$order->order_number}",
            'description' => "سبب طلب الضمان: {$validated['reason']}
تفاصيل العطل: ".($validated['notes'] ?? 'لا يوجد تفاصيل إضافية'),
            'ticket_status' => 'open',
            'priority' => 'high',
        ]);

        if (! empty($validated['attachment'])) {
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
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
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

        if (! $location) {
            $location = [
                'latitude' => (float) $order->latitude + 0.0025,
                'longitude' => (float) $order->longitude + 0.0025,
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
                $filename = 'voice_'.time().'_'.rand(1000, 9999).'.m4a';
                Storage::disk('public')->put('voice_notes/'.$filename, $decoded);

                return 'storage/voice_notes/'.$filename;
            }
        }

        return $voiceData;
    }

    private function saveOrderImage(?string $imageData): ?string
    {
        if (empty($imageData)) {
            return null;
        }

        if (str_starts_with($imageData, 'data:image') || strlen($imageData) > 500) {
            $cleanBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
            $cleanBase64 = str_replace(' ', '+', $cleanBase64);
            $decoded = base64_decode($cleanBase64);
            if ($decoded !== false) {
                $filename = 'order_'.time().'_'.rand(1000, 9999).'.jpg';
                Storage::disk('public')->put('orders/'.$filename, $decoded);

                return 'storage/orders/'.$filename;
            }
        }

        return $imageData;
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
                $filename = 'receipt_'.time().'_'.rand(1000, 9999).'.jpg';
                Storage::disk('public')->put('receipts/'.$filename, $decoded);

                return 'storage/receipts/'.$filename;
            }
        }

        return $receiptData;
    }

    private function saveCompletionImage(?string $imageData): ?string
    {
        if (empty($imageData)) {
            return null;
        }

        if (str_starts_with($imageData, 'data:image') || strlen($imageData) > 500) {
            $cleanBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
            $cleanBase64 = str_replace(' ', '+', $cleanBase64);
            $decoded = base64_decode($cleanBase64);
            if ($decoded !== false) {
                $filename = 'completion_'.time().'_'.rand(1000, 9999).'.jpg';
                Storage::disk('public')->put('completions/'.$filename, $decoded);

                return 'storage/completions/'.$filename;
            }
        }

        return $imageData;
    }

    private function maskOrderPrivacy(Order $order, ?User $user): void
    {
        $isCompleted = in_array($order->status, ['completed', 'paid'])
            || $order->status_id == 5
            || ! empty($order->completed_at);

        if (! $isCompleted) {
            return;
        }

        $isCraftsman = $user && (
            $user->role === 'craftsman'
            || ($user->current_mode ?? '') === 'craftsman'
            || $order->craftsman_id === $user->id
        );

        $isClient = $user && (
            $user->role === 'client'
            || ($user->current_mode ?? '') === 'client'
            || $order->customer_id === $user->id
        );

        // عند إكمال الطلب: تختفي بيانات العميل من الطلب عند الحرفي
        if ($isCraftsman) {
            if ($order->client) {
                $order->client->phone = '';
                $order->client->email = '';
                $order->client->name = 'العميل (طلب مكتمل)';
            }
            $order->address_details = 'تم إخفاء العنوان بعد إكمال الطلب حِفاظاً على الخصوصية';
            $order->latitude = 0;
            $order->longitude = 0;
        }

        // عند إكمال الطلب: تختفي بيانات الحرفي من الطلب عند العميل
        if ($isClient) {
            if ($order->craftsman) {
                $order->craftsman->phone = '';
                $order->craftsman->email = '';
            }
        }
    }

    // Craftsman enters client's 6-digit completion OTP to verify and finalize order
    public function verifyCompletion(Request $request, $id)
    {
        $validated = $request->validate([
            'otp_code' => 'required|string|size:6',
            'completion_image' => 'nullable|string',
        ]);

        $order = Order::with('craftsman.craftsmanProfile')->findOrFail($id);
        $user = $request->user();

        if ($order->craftsman_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'غير مصرح لك بإنهاء هذا الطلب'], 403);
        }

        if ($order->completion_otp !== $validated['otp_code']) {
            return response()->json([
                'status' => false,
                'message' => 'رمز التحقق (OTP) غير صحيح! اطلب من العميل تزويدك بالرمز السري الظاهر في تطبيقه.',
            ], 400);
        }

        $order->status = 'completed';
        $order->status_id = 5;
        $order->completed_at = now();
        $order->final_total_amount = $order->total_amount;

        $savedCompletion = null;
        if ($request->hasFile('completion_image')) {
            $path = $request->file('completion_image')->store('completions', 'public');
            $savedCompletion = 'storage/'.$path;
        } elseif (! empty($request->completion_image)) {
            $savedCompletion = $this->saveCompletionImage($request->completion_image);
        }

        if ($savedCompletion) {
            $order->completion_image = $savedCompletion;
            try {
                RequestAttachment::create([
                    'request_id' => $order->id,
                    'file_path' => $savedCompletion,
                    'file_type' => 'image',
                    'uploaded_by' => $user->id,
                ]);
            } catch (\Exception $e) {
            }
        }

        $order->save();

        // Release craftsman earnings to their wallet balance if order was paid online
        if ($order->payment_status === 'paid' && $order->craftsman_earnings > 0) {
            $craftsman = $order->craftsman;
            if ($craftsman) {
                $craftsman->wallet_balance = (float) $craftsman->wallet_balance + (float) $order->craftsman_earnings;
                $craftsman->save();

                Transaction::create([
                    'transaction_number' => 'TXN-'.time().'-'.rand(100, 999),
                    'request_id' => $order->id,
                    'user_id' => $craftsman->id,
                    'payment_method_id' => 1,
                    'amount' => $order->craftsman_earnings,
                    'currency_id' => 1,
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
        }

        // Increment completed requests count on craftsman profile
        if ($order->craftsman?->craftsmanProfile) {
            $order->craftsman->craftsmanProfile->increment('completed_requests_count');
        }

        $freshOrder = $order->fresh(['category', 'service', 'client', 'craftsman']);
        $this->maskOrderPrivacy($freshOrder, $user);

        return response()->json([
            'status' => true,
            'message' => 'تم تأكيد إنجاز الطلب بنجاح عبر رمز العميل، وتم تحرير المستحقات ✓',
            'data' => $freshOrder,
        ]);
    }

    // Open a formal dispute on an order
    public function openDispute(Request $request, $id)
    {
        $validated = $request->validate([
            'dispute_reason' => 'required|string|max:255',
            'details' => 'required|string',
        ]);

        $order = Order::findOrFail($id);
        $user = $request->user();

        if ($order->client_id !== $user->id && $order->craftsman_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'غير مصرح'], 403);
        }

        $dispute = SupportTicket::create([
            'ticket_number' => 'TCK-DISP-'.time(),
            'user_id' => $user->id,
            'subject' => 'نزاع بخصوص الطلب #'.$order->request_number.' - '.$validated['dispute_reason'],
            'description' => $validated['details'],
            'priority' => 'high',
            'ticket_status' => 'open',
            'category' => 'DISPUTE',
            'related_service_request_id' => $order->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل النزاع والشكوى بنجاح، وسيقوم فريق الرقابة والجودة بمراجعته والتواصل معك.',
            'data' => $dispute,
        ], 201);
    }

    // List user disputes
    public function listDisputes(Request $request)
    {
        $user = $request->user();
        $disputes = SupportTicket::where('user_id', $user->id)
            ->whereNotNull('related_service_request_id')
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $disputes,
        ]);
    }

    // Apply coupon to an order
    public function applyCoupon(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $order = Order::findOrFail($id);
        $user = $request->user();

        if ($order->customer_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'غير مصرح'], 403);
        }

        $discount = 20.00; // قيمة تخفيض ترويجية افتراضية

        return response()->json([
            'status' => true,
            'message' => 'تم تطبيق كود الخصم بنجاح على الطلب ✓',
            'data' => [
                'order' => $order,
                'discount_applied' => $discount,
                'coupon' => strtoupper($validated['code']),
            ],
        ]);
    }
}
