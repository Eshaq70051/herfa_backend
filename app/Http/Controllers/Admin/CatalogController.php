<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Service;
use App\Models\ServicePrice;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function categories()
    {
        $categories = Category::withCount('services')->orderBy('sort_order')->get();

        return view('admin.catalog.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $imagePath ?? ($request->icon ?? 'layer-group'),
            'image' => $imagePath,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => 1,
        ]);

        return back()->with('success', 'تمت إضافة التصنيف بنجاح.');
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'sort_order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $cat = Category::findOrFail($id);
        $data = $request->only('name', 'description', 'sort_order', 'is_active');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
            $data['icon'] = $data['image'];
        } elseif ($request->filled('icon')) {
            $data['icon'] = $request->icon;
        }

        $cat->update($data);

        return back()->with('success', 'تم تحديث بيانات التصنيف بنجاح.');
    }

    public function destroyCategory($id)
    {
        $cat = Category::findOrFail($id);
        if ($cat->services()->count() > 0) {
            return back()->withErrors(['msg' => 'لا يمكن حذف هذا التصنيف لوجود خدمات تابعة له.']);
        }
        $cat->delete();

        return back()->with('success', 'تم حذف التصنيف بنجاح.');
    }

    public function services(Request $request)
    {
        $query = Service::with(['category', 'servicePrices.currency']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('pricing_type')) {
            $query->where('pricing_type', $request->pricing_type);
        }

        $services = $query->latest('id')->paginate(15);
        $categories = Category::where('is_active', 1)->orderBy('name')->get();
        $currencies = Currency::where('is_active', 1)->get();

        return view('admin.catalog.services', compact('services', 'categories', 'currencies'));
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:FIXED,HOURLY,CUSTOM_QUOTE',
            'fixed_price' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
        ]);

        $service = Service::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'pricing_type' => $request->pricing_type,
            'is_active' => 1,
        ]);

        if ($request->filled('fixed_price') && $request->filled('currency_id')) {
            ServicePrice::create([
                'service_id' => $service->id,
                'currency_id' => $request->currency_id,
                'fixed_price' => $request->fixed_price,
                'is_active' => 1,
            ]);
        }

        return back()->with('success', 'تمت إضافة الخدمة بنجاح.');
    }

    public function updateService(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:FIXED,HOURLY,CUSTOM_QUOTE',
            'is_active' => 'required|boolean',
        ]);

        $service = Service::findOrFail($id);
        $service->update($request->only('category_id', 'name', 'description', 'pricing_type', 'is_active'));

        return back()->with('success', 'تم تحديث بيانات الخدمة بنجاح.');
    }

    public function destroyService($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return back()->with('success', 'تم حذف الخدمة بنجاح.');
    }
}
