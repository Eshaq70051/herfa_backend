@extends('admin.layout')

@section('title', 'إدارة كوبونات الخصم')
@section('page_title', 'كوبونات وقسائم الخصم الترويجية (Coupons & Promo Codes)')

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    editCoupon: {},
    editUrl: ''
}">

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-800">إدارة الحملات الترويجية وأكواد الخصم</h3>
            <p class="text-xs text-gray-500 mt-1">تحديد نسب ومبالغ الخصم، الحد الأدنى للطلب، وتاريخ انتهاء الصلاحية وسقف الاستخدام</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showAddModal = true" class="px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>إنشاء كود خصم جديد</span>
            </button>
        </div>
    </div>

    <!-- Coupons Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-gray-50 text-gray-600 border-b border-gray-200 font-bold">
                    <tr>
                        <th class="p-4">كود الخصم</th>
                        <th class="p-4">نوع الخصم</th>
                        <th class="p-4">قيمة الخصم</th>
                        <th class="p-4">الحد الأدنى للطلب</th>
                        <th class="p-4">الحد الأقصى للخصم</th>
                        <th class="p-4">مرات الاستخدام</th>
                        <th class="p-4">تاريخ الانتهاء</th>
                        <th class="p-4">الحالة</th>
                        <th class="p-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($coupons as $c)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-lg bg-gray-100 text-primary-700 font-mono font-bold text-sm tracking-wider border border-gray-200">
                                {{ $c->code }}
                            </span>
                        </td>
                        <td class="p-4 font-bold text-gray-700">
                            {{ $c->discount_type === 'percentage' ? 'نسبة مئوية (%)' : 'مبلغ مقطوع ثابت' }}
                        </td>
                        <td class="p-4 font-bold text-primary-600 text-sm font-mono">
                            {{ $c->discount_type === 'percentage' ? $c->discount_value . '%' : number_format($c->discount_value, 2) . ' ر.ي' }}
                        </td>
                        <td class="p-4 text-gray-600 font-mono">{{ $c->min_order_value > 0 ? number_format($c->min_order_value, 2) . ' ر.ي' : 'بدون حد' }}</td>
                        <td class="p-4 text-gray-600 font-mono">{{ $c->max_discount ? number_format($c->max_discount, 2) . ' ر.ي' : 'غير محدد' }}</td>
                        <td class="p-4 font-medium text-gray-800 font-mono">
                            {{ $c->used_count }} / {{ $c->usage_limit ?? '∞' }}
                        </td>
                        <td class="p-4 text-gray-500 font-mono">
                            {{ $c->expires_at ? $c->expires_at->format('Y-m-d') : 'ساري دائماً' }}
                        </td>
                        <td class="p-4">
                            <form action="{{ route('admin.coupons.toggle', $c->id) }}" method="POST">
                                @csrf
                                <button type="submit" title="انقر للتبديل بين التفعيل والتعطيل" class="cursor-pointer">
                                    @if($c->is_active && (!$c->expires_at || $c->expires_at->isFuture()))
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 hover:bg-emerald-200 transition">نشط وفعال</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200 hover:bg-gray-200 transition">معطل / منتهي</span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="p-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <button @click="editCoupon = {
                                    id: {{ $c->id }},
                                    code: '{{ $c->code }}',
                                    discount_type: '{{ $c->discount_type }}',
                                    discount_value: '{{ $c->discount_value }}',
                                    min_order_value: '{{ $c->min_order_value }}',
                                    max_discount: '{{ $c->max_discount ?? '' }}',
                                    usage_limit: '{{ $c->usage_limit ?? '' }}',
                                    expires_at: '{{ $c->expires_at ? $c->expires_at->format('Y-m-d') : '' }}',
                                    is_active: {{ $c->is_active ? '1' : '0' }}
                                }; editUrl = '{{ route('admin.coupons.update', $c->id) }}'; showEditModal = true" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل الكوبون">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.coupons.destroy', $c->id) }}" method="POST" onsubmit="return confirm('هل تريد حذف كود الخصم هذا؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition" title="حذف">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-gray-400">
                            <i class="fa-solid fa-ticket-simple text-3xl mb-2 text-gray-300"></i>
                            <p>لا توجد كوبونات خصم مسجلة حالياً</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $coupons->links() }}
        </div>
    </div>

    <!-- MODAL: Add Coupon -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-100 text-right space-y-4 max-h-[90vh] overflow-y-auto" @click.away="showAddModal = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-base text-gray-800">إضافة قسيمة / كود خصم جديد</h3>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-gray-700 mb-1">كود الخصم (Promo Code)</label>
                    <input type="text" name="code" required placeholder="مثال: HERFA20" class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono uppercase focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">نوع الخصم</label>
                        <select name="discount_type" required class="w-full p-2.5 bg-gray-50 border rounded-xl">
                            <option value="percentage">نسبة مئوية (%)</option>
                            <option value="fixed">مبلغ مقطوع ثابت (ر.ي)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">قيمة الخصم</label>
                        <input type="number" step="0.01" name="discount_value" required placeholder="مثال: 15" class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">الحد الأدنى لقيمة الطلب (ر.ي)</label>
                        <input type="number" step="0.01" name="min_order_value" value="0" class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">سقف الخصم الأقصى (ر.ي)</label>
                        <input type="number" step="0.01" name="max_discount" placeholder="اختياري للنسبة" class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">الحد الأقصى لمرات الاستخدام</label>
                        <input type="number" name="usage_limit" placeholder="فارغ = غير محدود" class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">تاريخ انتهاء الصلاحية</label>
                        <input type="date" name="expires_at" class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono">
                    </div>
                </div>

                <div class="pt-3 border-t">
                    <button type="submit" class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition">
                        حفظ ونشر كود الخصم
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Coupon -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-100 text-right space-y-4 max-h-[90vh] overflow-y-auto" @click.away="showEditModal = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-base text-gray-800">تعديل كود الخصم: <span class="font-mono text-primary-600" x-text="editCoupon.code"></span></h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form :action="editUrl" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-gray-700 mb-1">كود الخصم</label>
                    <input type="text" name="code" x-model="editCoupon.code" required class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono uppercase focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">نوع الخصم</label>
                        <select name="discount_type" x-model="editCoupon.discount_type" required class="w-full p-2.5 bg-gray-50 border rounded-xl">
                            <option value="percentage">نسبة مئوية (%)</option>
                            <option value="fixed">مبلغ مقطوع ثابت (ر.ي)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">قيمة الخصم</label>
                        <input type="number" step="0.01" name="discount_value" x-model="editCoupon.discount_value" required class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">الحد الأدنى لقيمة الطلب (ر.ي)</label>
                        <input type="number" step="0.01" name="min_order_value" x-model="editCoupon.min_order_value" class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">سقف الخصم الأقصى (ر.ي)</label>
                        <input type="number" step="0.01" name="max_discount" x-model="editCoupon.max_discount" class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">الحد الأقصى لمرات الاستخدام</label>
                        <input type="number" name="usage_limit" x-model="editCoupon.usage_limit" class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">تاريخ انتهاء الصلاحية</label>
                        <input type="date" name="expires_at" x-model="editCoupon.expires_at" class="w-full p-2.5 bg-gray-50 border rounded-xl font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">حالة التفعيل</label>
                    <select name="is_active" x-model="editCoupon.is_active" class="w-full p-2.5 bg-gray-50 border rounded-xl">
                        <option value="1">نشط</option>
                        <option value="0">معطل</option>
                    </select>
                </div>

                <div class="pt-3 border-t">
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition">
                        تحديث بيانات كود الخصم
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
