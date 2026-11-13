<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Order;
use App\Models\User;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:service_requests,id',
            'rating' => 'required|numeric|min:1|max:5',
            'punctuality_rating' => 'nullable|integer|min:1|max:5',
            'cleanliness_rating' => 'nullable|integer|min:1|max:5',
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $order = Order::findOrFail($validated['request_id']);
        if ($order->client_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'غير مصرح'], 403);
        }

        if (!$order->craftsman_id) {
            return response()->json(['status' => false, 'message' => 'لا يوجد فني مرتبط بهذا الطلب للتقييم'], 400);
        }

        $existing = Review::where('request_id', $order->id)->first();
        if ($existing) {
            return response()->json(['status' => false, 'message' => 'تم تقييم هذا الطلب مسبقاً'], 400);
        }

        $review = Review::create([
            'request_id' => $order->id,
            'customer_id' => $request->user()->id,
            'craftsman_id' => $order->craftsman_id,
            'rating' => $validated['rating'],
            'punctuality_rating' => $validated['punctuality_rating'] ?? 5,
            'cleanliness_rating' => $validated['cleanliness_rating'] ?? 5,
            'quality_rating' => $validated['quality_rating'] ?? 5,
            'comment' => $validated['comment'] ?? null,
        ]);

        // Recalculate craftsman rating
        $craftsman = User::find($order->craftsman_id);
        if ($craftsman) {
            $avg = Review::where('craftsman_id', $craftsman->id)->avg('rating');
            $count = Review::where('craftsman_id', $craftsman->id)->count();
            $craftsman->rating = round($avg, 2);
            $craftsman->total_reviews = $count;
            $craftsman->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'شكراً لك! تم تسجيل تقييمك بنجاح',
            'data' => $review,
        ], 201);
    }
}
