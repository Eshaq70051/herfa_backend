@extends('admin.layout')

@section('title', 'دليل الخدمات والتسعير')
@section('page_title', 'دليل الخدمات وقوائم الأسعار (Services & Pricing)')

@section('content')
<div class="space-y-6">

    <!-- Filters & Add button -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.catalog.services') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <select name="category_id" class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                <option value="">جميع التصنيفات</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>

            <select name="pricing_type" class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                <option value="">جميع أنواع التسعير</option>
                <option value="FIXED" {{ request('pricing_type') == 'FIXED' ? 'selected' : '' }}>سعر ثابت (FIXED)</option>
                <option value="HOURLY" {{ request('pricing_type') == 'HOURLY' ? 'selected' : '' }}>بالساعة (HOURLY)</option>
                <option value="CUSTOM_QUOTE" {{ request('pricing_type') == 'CUSTOM_QUOTE' ? 'selected' : '' }}>عرض سعر مخصص (CUSTOM_QUOTE)</option>
            </select>

            <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                تصفية
            </button>
        </form>

        <button onclick="document.getElementById('addServiceModal').classList.remove('hidden')" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة خدمة جديدة</span>
        </button>
    </div>

    <!-- Services Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">اسم الخدمة</th>
                        <th class="py-3.5 px-4">التصنيف</th>
                        <th class="py-3.5 px-4">طريقة التسعير</th>
                        <th class="py-3.5 px-4">السعر الافتراضي</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($services as $srv)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800 block text-sm">{{ $srv->name }}</span>
                                <span class="text-slate-400 text-[11px] block mt-0.5 line-clamp-1">{{ $srv->description ?? '' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-600">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px]">
                                    {{ $srv->category->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700">
                                {{ $srv->pricing_type }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($srv->servicePrices->count() > 0)
                                    @foreach($srv->servicePrices as $sp)
                                        <span class="font-bold font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-xs">
                                            {{ number_format($sp->fixed_price ?? $sp->first_hour_price, 2) }} {{ $sp->currency->code ?? '' }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-slate-400">حسب المعاينة / الاتفاق</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $srv->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $srv->is_active ? 'نشط' : 'معطل' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <!-- زر التعديل -->
                                    <button onclick="editService({{ json_encode($srv) }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل الخدمة">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- زر الحذف -->
                                    <form action="{{ route('admin.catalog.services.destroy', $srv->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الخدمة؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف الخدمة">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-screwdriver-wrench text-4xl mb-3 text-slate-300 block"></i>
                                <span>لا توجد خدمات مضافة حالياً</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $services->links() }}
            </div>
        @endif
    </div>

    <!-- Add Service Modal -->
    <div id="addServiceModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">إضافة خدمة جديدة</h4>
                <button onclick="document.getElementById('addServiceModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.catalog.services.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">التصنيف التابع له</label>
                    <select name="category_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم الخدمة</label>
                    <input type="text" name="name" required placeholder="مثال: فحص وإصلاح تسريبات المياه" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">طريقة التسعير</label>
                        <select name="pricing_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                            <option value="FIXED">سعر ثابت (FIXED)</option>
                            <option value="HOURLY">بالساعة (HOURLY)</option>
                            <option value="CUSTOM_QUOTE">عرض سعر مخصص (CUSTOM_QUOTE)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">السعر (إن وجد)</label>
                        <input type="number" step="0.01" name="fixed_price" placeholder="المبلغ" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">العملة</label>
                    <select name="currency_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                        @foreach($currencies as $cr)
                            <option value="{{ $cr->id }}">{{ $cr->name }} ({{ $cr->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الوصف والملاحظات</label>
                    <textarea name="description" rows="3" placeholder="تفاصيل ما تشمله هذه الخدمة..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('addServiceModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition">حفظ الخدمة</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div id="editServiceModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تعديل بيانات الخدمة</h4>
                <button onclick="document.getElementById('editServiceModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="editServiceForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">التصنيف التابع له</label>
                    <select id="edit_srv_category_id" name="category_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم الخدمة</label>
                    <input type="text" id="edit_srv_name" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">طريقة التسعير</label>
                        <select id="edit_srv_pricing_type" name="pricing_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="FIXED">سعر ثابت (FIXED)</option>
                            <option value="HOURLY">بالساعة (HOURLY)</option>
                            <option value="CUSTOM_QUOTE">عرض سعر مخصص (CUSTOM_QUOTE)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">الحالة</label>
                        <select id="edit_srv_active" name="is_active" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="1">نشط</option>
                            <option value="0">معطل</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الوصف</label>
                    <textarea id="edit_srv_desc" name="description" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('editServiceModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function editService(srv) {
        document.getElementById('editServiceForm').action = '/admin/services/' + srv.id;
        document.getElementById('edit_srv_category_id').value = srv.category_id;
        document.getElementById('edit_srv_name').value = srv.name;
        document.getElementById('edit_srv_pricing_type').value = srv.pricing_type;
        document.getElementById('edit_srv_active').value = srv.is_active ? '1' : '0';
        document.getElementById('edit_srv_desc').value = srv.description || '';
        document.getElementById('editServiceModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
