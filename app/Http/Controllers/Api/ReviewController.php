<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:service_requests,id',
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $order = Order::findOrFail($validated['request_id']);
        if ($order->customer_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'غير مصرح'], 403);
        }

        $craftsmanId = $order->assignments()->latest()->first()?->craftsman_id;
        if (! $craftsmanId) {
            return response()->json(['status' => false, 'message' => 'لا يوجد فني مسند لهذا الطلب للتقييم'], 400);
        }

        $existing = Review::where('request_id', $order->id)->first();
        if ($existing) {
            return response()->json(['status' => false, 'message' => 'تم تقييم هذا الطلب مسبقاً'], 400);
        }

        $review = Review::create([
            'request_id' => $order->id,
            'customer_id' => $request->user()->id,
            'craftsman_id' => $craftsmanId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'review_status' => 'visible',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'شكراً لك! تم تسجيل تقييمك بنجاح',
            'data' => $review,
        ], 201);
    }

    public function rateCustomer(Request $request, $orderId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل تقييمك للعميل بنجاح، شكراً لتعاونك ✓',
            'data' => [
                'order_id' => $orderId,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ],
        ], 201);
    }
}
