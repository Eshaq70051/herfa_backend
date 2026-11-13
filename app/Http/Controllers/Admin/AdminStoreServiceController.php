<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreService;
use Illuminate\Support\Facades\Storage;

class AdminStoreServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = StoreService::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $stores = $query->orderBy('display_order')->orderBy('id', 'desc')->paginate(12);

        $totalStores = StoreService::count();
        $activeStores = StoreService::where('is_active', true)->count();
        $avgRating = round(StoreService::avg('rating') ?? 0, 1);
        $totalReviews = StoreService::sum('reviews_count');

        return view('admin.stores.index', compact('stores', 'totalStores', 'activeStores', 'avgRating', 'totalReviews'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'rating' => 'nullable|numeric|min:0|max:5',
            'reviews_count' => 'nullable|integer|min:0',
            'badge' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer',
            'tags' => 'nullable|string',
            'logo_file' => 'nullable|image|max:3072',
            'logo_url' => 'nullable|string|max:500',
        ]);

        $logo = $validated['logo_url'] ?? null;
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('stores', 'public');
            $logo = 'storage/' . $path;
        }

        // Process tags comma-separated or newline
        $tags = [];
        if (!empty($validated['tags'])) {
            $tags = array_values(array_filter(array_map('trim', preg_split('/[,\n]+/', $validated['tags']))));
        }

        StoreService::create([
            'name' => $validated['name'],
            'company_name' => $validated['company_name'],
            'description' => $validated['description'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'rating' => $validated['rating'] ?? 4.8,
            'reviews_count' => $validated['reviews_count'] ?? 0,
            'badge' => $validated['badge'] ?? 'معتمد',
            'display_order' => $validated['display_order'] ?? 0,
            'tags' => $tags,
            'logo' => $logo ?: 'https://api.dicebear.com/7.x/shapes/svg?seed=' . urlencode($validated['company_name']),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.stores.index')->with('success', 'تمت إضافة متجر / خدمة الشريك بنجاح ✓');
    }

    public function update(Request $request, $id)
    {
        $store = StoreService::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'rating' => 'nullable|numeric|min:0|max:5',
            'reviews_count' => 'nullable|integer|min:0',
            'badge' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer',
            'tags' => 'nullable|string',
            'logo_file' => 'nullable|image|max:3072',
            'logo_url' => 'nullable|string|max:500',
        ]);

        $logo = $store->logo;
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('stores', 'public');
            $logo = 'storage/' . $path;
        } elseif (!empty($validated['logo_url'])) {
            $logo = $validated['logo_url'];
        }

        $tags = [];
        if (!empty($validated['tags'])) {
            $tags = array_values(array_filter(array_map('trim', preg_split('/[,\n]+/', $validated['tags']))));
        }

        $store->update([
            'name' => $validated['name'],
            'company_name' => $validated['company_name'],
            'description' => $validated['description'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'rating' => $validated['rating'] ?? $store->rating,
            'reviews_count' => $validated['reviews_count'] ?? $store->reviews_count,
            'badge' => $validated['badge'] ?? $store->badge,
            'display_order' => $validated['display_order'] ?? $store->display_order,
            'tags' => $tags,
            'logo' => $logo,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.stores.index')->with('success', 'تم تحديث بيانات المتجر بنجاح ✓');
    }

    public function toggleStatus($id)
    {
        $store = StoreService::findOrFail($id);
        $store->is_active = !$store->is_active;
        $store->save();

        return response()->json([
            'status' => true,
            'is_active' => $store->is_active,
            'message' => $store->is_active ? 'تم تفعيل المتجر بنجاح' : 'تم تعطيل المتجر',
        ]);
    }

    public function destroy($id)
    {
        $store = StoreService::findOrFail($id);
        $store->delete();

        return redirect()->route('admin.stores.index')->with('success', 'تم حذف المتجر بنجاح');
    }
}
