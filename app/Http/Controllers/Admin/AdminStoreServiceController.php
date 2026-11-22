<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Supplier;
use Illuminate\Http\Request;

class AdminStoreServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('contact_person', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('city', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $totalStores = Supplier::count();
        $activeStores = Supplier::where('is_active', true)->count();
        $avgRating = 4.8;
        $totalReviews = 142;

        $stores = $query->latest('id')->paginate(12);

        // تجهيز الحقول الإضافية للتوافق مع واجهة العرض
        $stores->getCollection()->transform(function ($s) {
            $s->company_name = $s->name;
            $s->badge = 'شريك معتمد';
            $s->rating = 4.8;
            $s->reviews_count = 24;
            $s->tags = [$s->city ?? 'صنعاء', 'خدمات معتمدة'];
            $s->logo_url = 'https://api.dicebear.com/7.x/shapes/svg?seed='.urlencode($s->name);

            return $s;
        });

        return view('admin.stores.index', compact(
            'stores',
            'totalStores',
            'activeStores',
            'avgRating',
            'totalReviews'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $defaultAccount = Account::where('is_postable', true)->first();
        $defaultCurrency = Currency::where('is_default', true)->first() ?? Currency::first();

        Supplier::create([
            'supplier_code' => 'STORE-'.strtoupper(uniqid()),
            'name' => $validated['name'],
            'contact_person' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? 'الرياض',
            'account_id' => $defaultAccount?->id ?? 1,
            'default_currency_id' => $defaultCurrency?->id ?? 1,
            'is_active' => true,
            'notes' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'تمت إضافة متجر / خدمة الشريك بنجاح.');
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
        ]);

        $supplier->update($validated);

        return back()->with('success', 'تم تحديث بيانات المتجر بنجاح.');
    }

    public function toggleStatus($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->is_active = ! $supplier->is_active;
        $supplier->save();

        return back()->with('success', 'تم تغيير حالة المتجر بنجاح.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return back()->with('success', 'تم حذف المتجر بنجاح.');
    }
}
