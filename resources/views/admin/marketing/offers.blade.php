@extends('admin.layout')

@section('title', 'العروض الترويجية')
@section('page_title', 'العروض الترويجية والخصومات (Offers)')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">قائمة العروض والخصومات</h3>
            <p class="text-xs text-slate-400">إدارة نسب الخصم على الخدمات ومواعيد سريانها</p>
        </div>
        <button onclick="document.getElementById('addOfferModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة عرض جديد</span>
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">عنوان العرض</th>
                        <th class="py-3.5 px-4">الخدمة المرتبطة</th>
                        <th class="py-3.5 px-4">نسبة الخصم</th>
                        <th class="py-3.5 px-4">تاريخ البداية</th>
                        <th class="py-3.5 px-4">تاريخ الانتهاء</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($offers as $off)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $off->title }}</td>
                            <td class="py-3.5 px-4 text-slate-600 font-medium">{{ $off->service->name ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-700 bg-emerald-50/50">{{ $off->discount_percentage }}%</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $off->start_date }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $off->end_date }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $off->offer_status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $off->offer_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <button onclick="editOffer({{ json_encode($off) }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل العرض">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('admin.marketing.offers.destroy', $off->id) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا العرض الترويجي؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف العرض">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">لا توجد عروض مسجلة حالياً</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addOfferModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
            <h4 class="font-bold text-slate-800 text-sm border-b pb-2">إضافة عرض ترويجي</h4>
            <form action="{{ route('admin.marketing.offers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان العرض</label>
                    <input type="text" name="title" required placeholder="مثال: خصم 20% على غسيل المكيفات" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الخدمة المستهدفة</label>
                    <select name="service_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                        @foreach($services as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">نسبة الخصم %</label>
                    <input type="number" step="0.5" min="1" max="100" name="discount_percentage" required placeholder="20" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ البدء</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ الانتهاء</label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('addOfferModal').classList.add('hidden')" class="px-3 py-1.5 bg-slate-100 rounded-lg text-xs">إلغاء</button>
                    <button type="submit" class="px-4 py-1.5 bg-brand-600 text-white rounded-lg text-xs font-bold">حفظ العرض</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editOfferModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
            <h4 class="font-bold text-slate-800 text-sm border-b pb-2">تعديل بيانات العرض</h4>
            <form id="editOfferForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان العرض</label>
                    <input type="text" id="edit_off_title" name="title" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الخدمة المستهدفة</label>
                    <select id="edit_off_service_id" name="service_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                        @foreach($services as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">نسبة الخصم %</label>
                    <input type="number" step="0.5" id="edit_off_discount" name="discount_percentage" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ البدء</label>
                        <input type="date" id="edit_off_start" name="start_date" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ الانتهاء</label>
                        <input type="date" id="edit_off_end" name="end_date" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الحالة</label>
                    <select id="edit_off_status" name="offer_status" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                        <option value="active">نشط (active)</option>
                        <option value="pending">معلق (pending)</option>
                        <option value="expired">منتهي (expired)</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('editOfferModal').classList.add('hidden')" class="px-3 py-1.5 bg-slate-100 rounded-lg text-xs">إلغاء</button>
                    <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold">حفظ التعديل</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function editOffer(off) {
        document.getElementById('editOfferForm').action = '/admin/marketing/offers/' + off.id;
        document.getElementById('edit_off_title').value = off.title;
        document.getElementById('edit_off_service_id').value = off.service_id;
        document.getElementById('edit_off_discount').value = off.discount_percentage;
        document.getElementById('edit_off_start').value = off.start_date;
        document.getElementById('edit_off_end').value = off.end_date;
        document.getElementById('edit_off_status').value = off.offer_status;
        document.getElementById('editOfferModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
