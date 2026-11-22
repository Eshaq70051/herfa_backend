<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Governorate;
use App\Models\Neighborhood;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $governorates = Governorate::withCount('districts')->orderBy('name')->get();
        $districts = District::with(['governorate'])->withCount('neighborhoods')->orderBy('name')->paginate(20);
        $neighborhoods = Neighborhood::with('district.governorate')->latest('id')->paginate(20);

        return view('admin.locations.index', compact('governorates', 'districts', 'neighborhoods'));
    }

    public function storeGov(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:governorates,name']);
        Governorate::create(['name' => $request->name, 'is_active' => 1]);

        return back()->with('success', 'تمت إضافة المحافظة بنجاح.');
    }

    public function updateGov(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100|unique:governorates,name,'.$id]);
        $gov = Governorate::findOrFail($id);
        $gov->update(['name' => $request->name]);

        return back()->with('success', 'تم تعديل اسم المحافظة بنجاح.');
    }

    public function destroyGov($id)
    {
        $gov = Governorate::findOrFail($id);
        if ($gov->districts()->count() > 0) {
            return back()->withErrors(['msg' => 'لا يمكن حذف المحافظة لوجود مديريات تابعة لها.']);
        }
        $gov->delete();

        return back()->with('success', 'تم حذف المحافظة بنجاح.');
    }

    public function storeDistrict(Request $request)
    {
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name' => 'required|string|max:100',
        ]);
        District::create([
            'governorate_id' => $request->governorate_id,
            'name' => $request->name,
            'is_active' => 1,
        ]);

        return back()->with('success', 'تمت إضافة المديرية بنجاح.');
    }

    public function updateDistrict(Request $request, $id)
    {
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name' => 'required|string|max:100',
        ]);
        $d = District::findOrFail($id);
        $d->update($request->only('governorate_id', 'name'));

        return back()->with('success', 'تم تعديل بيانات المديرية بنجاح.');
    }

    public function destroyDistrict($id)
    {
        $d = District::findOrFail($id);
        if ($d->neighborhoods()->count() > 0) {
            return back()->withErrors(['msg' => 'لا يمكن حذف المديرية لوجود أحياء تابعة لها.']);
        }
        $d->delete();

        return back()->with('success', 'تم حذف المديرية بنجاح.');
    }

    public function storeNeighborhood(Request $request)
    {
        $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name' => 'required|string|max:150',
        ]);
        Neighborhood::create([
            'district_id' => $request->district_id,
            'name' => $request->name,
            'is_active' => 1,
        ]);

        return back()->with('success', 'تمت إضافة الحي بنجاح.');
    }

    public function updateNeighborhood(Request $request, $id)
    {
        $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name' => 'required|string|max:150',
        ]);
        $n = Neighborhood::findOrFail($id);
        $n->update($request->only('district_id', 'name'));

        return back()->with('success', 'تم تعديل الحي بنجاح.');
    }

    public function destroyNeighborhood($id)
    {
        $n = Neighborhood::findOrFail($id);
        $n->delete();

        return back()->with('success', 'تم حذف الحي بنجاح.');
    }
}
