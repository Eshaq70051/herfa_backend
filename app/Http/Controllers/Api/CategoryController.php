<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Service;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->with(['services' => function ($query) {
                $query->where('is_active', true);
            }])
            ->withCount('services')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $categories,
        ]);
    }

    public function show($id)
    {
        $category = Category::with(['services' => function ($query) {
            $query->where('is_active', true);
        }])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $category,
        ]);
    }

    public function services(Request $request)
    {
        $query = Service::with('category')->where('is_active', true);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $services = $query->get();

        return response()->json([
            'status' => true,
            'data' => $services,
        ]);
    }
}
