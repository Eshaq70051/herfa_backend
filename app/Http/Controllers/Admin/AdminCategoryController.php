<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceImage;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('services')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $imagePath = '/storage/' . $path;
        }

        Category::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? 'fa-wrench',
            'sort_order' => $validated['sort_order'] ?? 0,
            'image' => $imagePath,
            'is_active' => true,
        ]);

        return back()->with('success', 'تمت إضافة القسم الجديد بنجاح');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|max:5120',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $category->description,
            'icon' => $validated['icon'] ?? $category->icon,
            'sort_order' => $validated['sort_order'] ?? $category->sort_order,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = '/storage/' . $path;
        }

        $category->update($data);

        return back()->with('success', 'تم تعديل القسم بنجاح');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', "تم حذف القسم ($name) وكافة خدماته بنجاح");
    }

    public function services($categoryId)
    {
        $category = Category::with('services.images')->findOrFail($categoryId);
        return view('admin.categories.services', compact('category'));
    }

    public function storeService(Request $request, $categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'warranty_days' => 'required|integer|min:0',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'public');
            $imagePath = '/storage/' . $path;
        }

        $service = $category->services()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'warranty_days' => $validated['warranty_days'],
            'image' => $imagePath,
            'is_active' => true,
        ]);

        if ($imagePath) {
            ServiceImage::create([
                'service_id' => $service->id,
                'image_path' => $imagePath,
            ]);
        }

        return back()->with('success', 'تمت إضافة الخدمة الجديدة بنجاح');
    }

    public function updateService(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'warranty_days' => 'required|integer|min:0',
            'image' => 'nullable|image|max:5120',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $service->description,
            'base_price' => $validated['base_price'],
            'warranty_days' => $validated['warranty_days'],
            'is_active' => $request->has('is_active') ? true : false,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'public');
            $data['image'] = '/storage/' . $path;

            ServiceImage::create([
                'service_id' => $service->id,
                'image_path' => $data['image'],
            ]);
        }

        $service->update($data);

        return back()->with('success', 'تم تعديل الخدمة بنجاح');
    }

    public function destroyService($id)
    {
        $service = Service::findOrFail($id);
        $name = $service->name;
        $service->delete();

        return back()->with('success', "تم حذف الخدمة ($name) بنجاح");
    }
}
