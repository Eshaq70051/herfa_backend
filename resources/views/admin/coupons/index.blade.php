@extends('admin.layout')

@section('title', 'إدارة الكوبونات والخصومات')
@section('page_title', 'كوبونات الخصم والعروض الترويجية')

@section('content')
<div class="space-y-6" x-data="{ addCouponModal: false }">

    <div class="flex items-center justify-between">
        <p class="text-xs text-gray-500 font-semibold">إدارة أكواد الخصم والحملات التسويقية</p>
        <button @click="addCouponModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>إنشاء كود خصم جديد</span>
        </button>
    </div>

    <!-- Coupons Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($coupons as $cp)
        <div class="bg-white rounded-2xl p-5 border border-dashed border-emerald-300 shadow-sm space-y-3 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-mono font-black text-base tracking-wider border border-emerald-200">
                    {{ $cp->code }}
                </span>
                <span class="px-2 py-0.5 text-xs font-bold rounded {{ $cp->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                    {{ $cp->is_active ? 'سارٍ' : 'منتهي' }}
                </span>
            </div>

            <div class="pt-2">
                <p class="text-2xl font-black text-gray-900 font-mono">
                    @if($cp->discount_type === 'percentage')
                        {{ $cp->discount_value }}% <span class="text-xs font-bold text-gray-500">خصم نسبي</span>
                    @else
                        {{ number_format($cp->discount_value, 2) }} <span class="text-xs font-bold text-gray-500">ر.ي خصم ثابت</span>
                    @endif
                </p>
                @if($cp->max_discount)
                    <p class="text-xs text-gray-400 mt-1">أقصى خصم: {{ $cp->max_discount }} ر.ي</p>
                @endif
                <p class="text-xs text-gray-500 mt-1">الحد الأدنى للطلب: {{ $cp->min_order_value }} ر.ي</p>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                <span>مرات الاستخدام: <strong class="text-gray-800">{{ $cp->used_count }}</strong></span>
                <span>الصلاحية: <strong class="text-gray-800">{{ $cp->expires_at ? $cp->expires_at->format('Y-m-d') : 'دائم' }}</strong></span>
            </div>
        </div>
        @empty
        <div class="col-span-3 py-12 text-center text-gray-400">لا توجد كوبونات خصم منشأة بعد</div>
        @endforelse
    </div>

    <!-- Add Coupon Modal -->
    <div x-show="addCouponModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.away="addCouponModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base">إنشاء كود خصم جديد</h3>
                <button @click="addCouponModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">كود الكوبون (رمز الخصم)</label>
                    <input type="text" name="code" required placeholder="مثال: HERFA30" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:border-emerald-500 outline-none uppercase font-mono">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">نوع الخصم</label>
                        <select name="discount_type" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:border-emerald-500 outline-none">
                            <option value="percentage">نسبة مئوية (%)</option>
                            <option value="fixed">مبلغ ثابت (ر.ي)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">قيمة الخصم</label>
                        <input type="number" step="0.5" name="discount_value" required placeholder="20" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:border-emerald-500 outline-none font-mono">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">الحد الأدنى للطلب</label>
                        <input type="number" step="0.5" name="min_order_value" value="50" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:border-emerald-500 outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">الحد الأقصى للخصم</label>
                        <input type="number" step="0.5" name="max_discount" placeholder="50" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:border-emerald-500 outline-none font-mono">
                    </div>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition">حفظ وتفعيل الكوبون</button>
                    <button type="button" @click="addCouponModal = false" class="py-2.5 px-4 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl transition">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
