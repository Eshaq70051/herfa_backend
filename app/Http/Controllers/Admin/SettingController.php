<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\CancellationReason;
use App\Models\CompanyWorkingHour;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::orderBy('setting_group')->orderBy('sort_order')->get();
        $currencies = Currency::all();
        $cancellationReasons = CancellationReason::all();
        $workingHours = CompanyWorkingHour::all();

        return view('admin.settings.index', compact('settings', 'currencies', 'cancellationReasons', 'workingHours'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            $setting = AppSetting::where('setting_key', $key)->first();
            if ($setting) {
                $setting->setting_value = is_array($value) ? json_encode($value) : $value;
                $setting->updated_by = Auth::id();
                $setting->save();
            }
        }

        return back()->with('success', 'تم حفظ وتحديث إعدادات النظام بنجاح.');
    }

    public function storeCurrency(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'decimal_places' => 'required|integer|min:0|max:4',
        ]);

        Currency::create([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'symbol' => $request->symbol,
            'decimal_places' => $request->decimal_places,
            'is_active' => 1,
            'is_default' => 0,
        ]);

        return back()->with('success', 'تمت إضافة العملة بنجاح.');
    }

    public function destroyCurrency($id)
    {
        $curr = Currency::findOrFail($id);
        if ($curr->is_default) {
            return back()->withErrors(['msg' => 'لا يمكن حذف العملة الافتراضية للنظام.']);
        }
        $curr->delete();

        return back()->with('success', 'تم حذف العملة بنجاح.');
    }

    public function storeCancellationReason(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'user_type' => 'nullable|string',
        ]);

        CancellationReason::create([
            'reason' => $request->reason,
            'user_type' => $request->user_type ?? 'ALL',
            'is_active' => 1,
        ]);

        return back()->with('success', 'تمت إضافة سبب الإلغاء بنجاح.');
    }

    public function destroyCancellationReason($id)
    {
        $reason = CancellationReason::findOrFail($id);
        $reason->delete();

        return back()->with('success', 'تم حذف سبب الإلغاء بنجاح.');
    }
}
