<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\AppSetting;
use App\Models\OrderMessage;
use App\Models\OrderOffer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Notification;
use App\Models\SupportTicket;
use App\Models\WalletTransaction;

class GeneralApiController extends Controller
{
    public function settings()
    {
        $settings = AppSetting::all()->mapWithKeys(function ($item) {
            $val = $item->setting_value;
            if ($val && str_starts_with($val, '/storage/')) {
                $val = asset($val);
            }
            return [$item->setting_key => $val];
        });

        return response()->json([
            'status' => true,
            'data' => $settings,
        ]);
    }

    public function paymentMethods()
    {
        $methods = PaymentMethod::where('is_active', true)
            ->where('code', '!=', 'card')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $methods,
        ]);
    }

    public function checkCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon || !$coupon->isValidForAmount((float)$request->amount)) {
            return response()->json([
                'status' => false,
                'message' => 'الكوبون غير صالح أو انتهت صلاحيته أو لم يبلغ الحد الأدنى',
            ], 400);
        }

        $discount = $coupon->calculateDiscount((float)$request->amount);

        return response()->json([
            'status' => true,
            'message' => 'تم تطبيق كود الخصم بنجاح',
            'data' => [
                'code' => $coupon->code,
                'discount' => $discount,
                'final_amount' => max(0, (float)$request->amount - $discount),
            ]
        ]);
    }

    public function submitOffer(Request $request, $orderId)
    {
        $validated = $request->validate([
            'proposed_price' => 'required|numeric|min:10',
            'estimated_duration' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $order = Order::findOrFail($orderId);
        $user = $request->user();

        $offer = OrderOffer::updateOrCreate(
            ['order_id' => $order->id, 'craftsman_id' => $user->id],
            [
                'proposed_price' => $validated['proposed_price'],
                'estimated_duration' => $validated['estimated_duration'] ?? 'ساعة واحدة',
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'تم تقديم عرض السعر للعميل بنجاح',
            'data' => $offer,
        ]);
    }

    public function getMessages($orderId)
    {
        $messages = OrderMessage::with('sender')
            ->where('order_id', $orderId)
            ->oldest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $messages,
        ]);
    }

    public function sendMessage(Request $request, $orderId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $order = Order::findOrFail($orderId);
        $user = $request->user();

        $message = OrderMessage::create([
            'order_id' => $order->id,
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        // تحديد الطرف الآخر (المستلم): إذا كان المرسل هو العميل يكون المستلم هو الحرفي والعكس
        $receiverId = ($user->id == $order->customer_id) ? $order->craftsman_id : $order->customer_id;

        if ($receiverId) {
            try {
                Notification::create([
                    'user_id' => $receiverId,
                    'title' => 'رسالة جديدة من ' . ($user->name ?? 'الطرف الآخر'),
                    'body' => $request->message,
                    'related_id' => $order->id,
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {}
        }

        return response()->json([
            'status' => true,
            'data' => $message->load('sender'),
        ], 201);
    }
    public function getNotifications(Request $request)
    {
        $user = $request->user();
        $notifications = Notification::where('user_id', $user->id)
            ->latest('created_at')
            ->take(30)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $notifications,
        ]);
    }

    public function markNotificationAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)->findOrFail($id);
        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تعيين الإشعار كمقروء',
        ]);
    }

    public function markAllNotificationsAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تعيين كافة الإشعارات كمقروءة',
        ]);
    }

    public function submitSupportTicket(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:191',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TCK-' . time(),
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? 'medium',
            'ticket_status' => 'open',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم استلام تذكرة الدعم الفني بنجاح، سيتم الرد عليك في أقرب وقت',
            'data' => $ticket,
        ], 201);
    }

    public function getMyTickets(Request $request)
    {
        $tickets = SupportTicket::with(['messages.sender'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $tickets,
        ]);
    }

    public function getTicketDetails(Request $request, $id)
    {
        $ticket = SupportTicket::with(['messages.sender'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $ticket,
        ]);
    }

    public function replyToTicket(Request $request, $id)
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $message = \App\Models\SupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        $ticket->update([
            'ticket_status' => 'open',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال ردك بنجاح',
            'data' => $message->load('sender'),
        ], 201);
    }

    public function depositWallet(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:500',
            'payment_method' => 'required|string',
            'transfer_reference' => 'nullable|string',
            'receipt_image' => 'nullable|string',
        ]);

        $user = $request->user();
        $amount = (float)$validated['amount'];

        $transaction = WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => $amount,
            'balance_after' => $user->wallet_balance + $amount,
            'description' => "طلب شحن المحفظة عبر {$validated['payment_method']} (رقم السند: " . ($validated['transfer_reference'] ?? 'مباشر') . ")",
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم استلام طلب شحن المحفظة بنجاح وجاري التحقق من إشعار الإيداع',
            'data' => $transaction,
        ]);
    }

    /// 1. جلب قائمة خدمات ومتاجر الشركاء المعتمدة لعرضها في التطبيق (GET /api/store-services)
    public function storeServices()
    {
        $stores = \App\Models\StoreService::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $stores,
        ]);
    }

    /// 2. جلب العروض والإعلانات الترويجية النشطة لعرضها في السلايدر المتحرك (GET /api/banners)
    public function banners()
    {
        // أ. جلب العروض الترويجية النشطة وخصومات الخدمات
        $offers = \App\Models\Offer::with('service')
            ->where('offer_status', 'active')
            ->latest()
            ->get()
            ->map(function ($offer) {
                return [
                    'id' => $offer->id,
                    'type' => 'offer',
                    'title' => $offer->title,
                    'description' => $offer->description ?? ($offer->service ? $offer->service->description_ar : null),
                    'badge' => $offer->discount_percentage > 0 ? 'خصم ' . (int)$offer->discount_percentage . '%' : 'عرض خاص',
                    'discount_percentage' => (float)$offer->discount_percentage,
                    'image_url' => $offer->image_url,
                    'service_id' => $offer->service_id,
                    'service' => $offer->service,
                ];
            });

        // ب. جلب الإعلانات العامة للشركاء والمنصة
        $advertisements = \App\Models\Advertisement::where('status', 'active')
            ->latest()
            ->get()
            ->map(function ($ad) {
                return [
                    'id' => $ad->id,
                    'type' => 'advertisement',
                    'title' => $ad->title,
                    'description' => $ad->description,
                    'badge' => 'إعلان خاص',
                    'discount_percentage' => 0,
                    'image_url' => $ad->image_url,
                    'target_url' => $ad->target_url,
                    'service_id' => null,
                    'service' => null,
                ];
            });

        // ج. دمج العروض والإعلانات في مصفوفة واحدة مرتبة للسلايدر
        $banners = $offers->concat($advertisements)->values();

        return response()->json([
            'status' => true,
            'data' => $banners,
        ]);
    }
}