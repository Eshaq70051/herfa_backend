<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Governorate;
use App\Models\Neighborhood;
use Illuminate\Http\Request;

class AdminLocationController extends Controller
{
    public function index()
    {
        $governorates = Governorate::with(['districts.neighborhoods'])->get();

        return view('admin.locations.index', compact('governorates'));
    }

    public function storeGov(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        Governorate::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return back()->with('success', 'تمت إضافة المنطقة / المحافظة بنجاح');
    }

    public function updateGov(Request $request, $id)
    {
        $gov = Governorate::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $gov->update([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return back()->with('success', 'تم تعديل المحافظة بنجاح');
    }

    public function destroyGov($id)
    {
        $gov = Governorate::findOrFail($id);
        $name = $gov->name;
        $gov->delete();

        return back()->with('success', "تم حذف المحافظة ($name) وكافة مديرياتها وأحيائها");
    }

    public function storeDist(Request $request)
    {
        $validated = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name' => 'required|string|max:100',
        ]);

        District::create([
            'governorate_id' => $validated['governorate_id'],
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return back()->with('success', 'تمت إضافة المديرية / المدينة بنجاح');
    }

    public function updateDist(Request $request, $id)
    {
        $dist = District::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $dist->update([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return back()->with('success', 'تم تعديل المديرية بنجاح');
    }

    public function destroyDist($id)
    {
        $dist = District::findOrFail($id);
        $name = $dist->name;
        $dist->delete();

        return back()->with('success', "تم حذف المديرية ($name)");
    }

    public function storeNeighborhood(Request $request)
    {
        $validated = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name' => 'required|string|max:100',
        ]);

        Neighborhood::create([
            'district_id' => $validated['district_id'],
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return back()->with('success', 'تمت إضافة الحي بنجاح');
    }

    public function updateNeighborhood(Request $request, $id)
    {
        $neigh = Neighborhood::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $neigh->update([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return back()->with('success', 'تم تعديل بيانات الحي بنجاح');
    }

    public function destroyNeighborhood($id)
    {
        $neigh = Neighborhood::findOrFail($id);
        $name = $neigh->name;
        $neigh->delete();

        return back()->with('success', "تم حذف الحي ($name)");
    }
}
