<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AppSetting;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\PaymentMethod;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestQuote;
use App\Models\Supplier;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $discount = min((float) $request->amount * 0.10, 50.00);

        return response()->json([
            'status' => true,
            'message' => 'تم تطبيق كود الخصم بنجاح',
            'data' => [
                'code' => strtoupper($request->code),
                'discount' => $discount,
                'final_amount' => max(0, (float) $request->amount - $discount),
            ],
        ]);
    }

    public function submitOffer(Request $request, $orderId)
    {
        $validated = $request->validate([
            'proposed_price' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        $order = ServiceRequest::findOrFail($orderId);
        $user = $request->user();

        $quote = ServiceRequestQuote::updateOrCreate(
            ['request_id' => $order->id, 'craftsman_id' => $user->id],
            [
                'initial_quote_amount' => $validated['proposed_price'],
                'agreed_quote_amount' => $validated['proposed_price'],
                'status' => 'PENDING',
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'تم تقديم عرض السعر للعميل بنجاح',
            'data' => $quote,
        ]);
    }

    public function getMessages($orderId)
    {
        $order = Order::find($orderId);
        $isCompleted = false;
        if ($order) {
            $isCompleted = in_array($order->status, ['completed', 'paid'])
                || $order->status_id == 5
                || ! empty($order->completed_at);
        }

        $messages = OrderMessage::where('order_id', $orderId)
            ->oldest()
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'order_id' => $msg->order_id,
                    'sender_id' => $msg->sender_id,
                    'message' => $msg->message,
                    'file_path' => $msg->file_path,
                    'image_url' => $msg->image_url,
                    'created_at' => $msg->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'status' => true,
            'is_completed' => $isCompleted,
            'data' => $messages,
        ]);
    }

    public function sendMessage(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // منع إرسال الرسائل إذا كان الطلب مكتملاً
        $isCompleted = in_array($order->status, ['completed', 'paid'])
            || $order->status_id == 5
            || ! empty($order->completed_at);

        if ($isCompleted) {
            return response()->json([
                'status' => false,
                'message' => 'عذراً، تم إغلاق المحادثة نظراً لاكتمال هذا الطلب وإنجازه.',
            ], 403);
        }

        $request->validate([
            'message' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        if (empty($request->message) && empty($request->image) && ! $request->hasFile('image')) {
            return response()->json([
                'status' => false,
                'message' => 'يرجى كتابة نص أو إرفاق صورة لإرسال الرسالة.',
            ], 422);
        }

        $filePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('order_messages', 'public');
            $filePath = 'storage/'.$path;
        } elseif (! empty($request->image)) {
            $imageData = $request->image;
            if (str_starts_with($imageData, 'data:image') || strlen($imageData) > 200) {
                $cleanBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
                $cleanBase64 = str_replace(' ', '+', $cleanBase64);
                $decoded = base64_decode($cleanBase64);
                if ($decoded !== false) {
                    $filename = 'msg_'.time().'_'.rand(1000, 9999).'.jpg';
                    Storage::disk('public')->put('order_messages/'.$filename, $decoded);
                    $filePath = 'storage/order_messages/'.$filename;
                }
            } elseif (filter_var($imageData, FILTER_VALIDATE_URL)) {
                $filePath = $imageData;
            }
        }

        $msg = OrderMessage::create([
            'order_id' => $orderId,
            'sender_id' => $request->user()->id,
            'message' => $request->message ?? '',
            'file_path' => $filePath,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال الرسالة بنجاح',
            'data' => [
                'id' => $msg->id,
                'order_id' => $msg->order_id,
                'sender_id' => $msg->sender_id,
                'message' => $msg->message,
                'file_path' => $msg->file_path,
                'image_url' => $msg->image_url,
                'created_at' => $msg->created_at?->toIso8601String(),
            ],
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
            'priority' => 'nullable|in:low,medium,high',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TCK-'.time(),
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

        $message = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        $ticket->update([
            'ticket_status' => 'open',
            'last_message_at' => now(),
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
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
        ]);

        $user = $request->user();
        $amount = (float) $validated['amount'];

        $transaction = Transaction::create([
            'transaction_number' => 'TRX-DEP-'.time(),
            'user_id' => $user->id,
            'amount' => $amount,
            'currency_id' => 1,
            'payment_method_id' => $validated['payment_method_id'] ?? 1,
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم استلام طلب شحن المحفظة بنجاح وجاري التحقق من إشعار الإيداع',
            'data' => $transaction,
        ]);
    }

    public function storeServices()
    {
        $stores = Supplier::where('is_active', true)
            ->get()
            ->map(function ($sup) {
                return [
                    'id' => $sup->id,
                    'name' => $sup->name,
                    'company_name' => $sup->name,
                    'phone' => $sup->phone,
                    'address' => $sup->address,
                    'city' => $sup->city,
                    'rating' => 4.9,
                    'is_active' => true,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $stores,
        ]);
    }

    public function banners()
    {
        $offers = Offer::with('service')
            ->where('offer_status', 'active')
            ->latest()
            ->get()
            ->map(function ($offer) {
                return [
                    'id' => $offer->id,
                    'type' => 'offer',
                    'title' => $offer->title,
                    'description' => $offer->description,
                    'badge' => $offer->discount_percentage > 0 ? 'خصم '.(int) $offer->discount_percentage.'%' : 'عرض خاص',
                    'discount_percentage' => (float) $offer->discount_percentage,
                    'image_url' => $offer->image_url,
                    'service_id' => $offer->service_id,
                    'service' => $offer->service,
                ];
            });

        $advertisements = Advertisement::where('status', 'active')
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

        $banners = $offers->concat($advertisements)->values();

        return response()->json([
            'status' => true,
            'data' => $banners,
        ]);
    }
}
