<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::latest('id')->get();

        return view('admin.settings.payment_methods', compact('methods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:payment_methods,code',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        PaymentMethod::create([
            'name' => $request->name,
            'code' => strtolower(trim($request->code)),
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);

        return back()->with('success', 'تمت إضافة وسيلة الدفع بنجاح.');
    }

    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:payment_methods,code,'.$id,
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $method->update([
            'name' => $request->name,
            'code' => strtolower(trim($request->code)),
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);

        return back()->with('success', 'تم تحديث وسيلة الدفع بنجاح.');
    }

    public function toggle($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->is_active = ! $method->is_active;
        $method->save();

        return back()->with('success', 'تم تغيير حالة وسيلة الدفع بنجاح.');
    }

    public function destroy($id)
    {
        $method = PaymentMethod::findOrFail($id);

        // التحقق من وجود معاملات مرتبطة بهذه الوسيلة
        if (Transaction::where('payment_method_id', $id)->exists()) {
            return back()->withErrors(['msg' => 'لا يمكن حذف وسيلة الدفع لوجود معاملات مالية مرتبطة بها، يمكنك تعطيلها بدلاً من ذلك.']);
        }

        $method->delete();

        return back()->with('success', 'تم حذف وسيلة الدفع بنجاح.');
    }
}
