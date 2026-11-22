@extends('admin.layout')

@section('title', 'مساحات الإعلانات')
@section('page_title', 'مساحات وإعلانات المنصة (Advertisements)')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">قائمة الحملات الإعلانية والبنرات</h3>
            <p class="text-xs text-slate-400">إدارة الإعلانات الترويجية للشركاء وتحديد الروابط المستهدفة</p>
        </div>
        <button onclick="document.getElementById('addAdModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة إعلان جديد</span>
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">عنوان الإعلان</th>
                        <th class="py-3.5 px-4">اسم المعلن / الشريك</th>
                        <th class="py-3.5 px-4">الرابط المستهدف</th>
                        <th class="py-3.5 px-4">الفترة الزمنية</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ads as $ad)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $ad->title }}</td>
                            <td class="py-3.5 px-4 text-slate-600 font-semibold">{{ $ad->advertiser_name }}</td>
                            <td class="py-3.5 px-4 font-mono text-blue-600">
                                @if($ad->target_url)
                                    <a href="{{ $ad->target_url }}" target="_blank" class="hover:underline">{{ $ad->target_url }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ date('Y-m-d', strtotime($ad->start_date)) }} إلى {{ date('Y-m-d', strtotime($ad->end_date)) }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $ad->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $ad->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <button onclick="editAd({{ json_encode($ad) }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل الإعلان">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('admin.marketing.ads.destroy', $ad->id) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا الإعلان؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف الإعلان">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">لا توجد حملات إعلانية مسجلة حالياً</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addAdModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
            <h4 class="font-bold text-slate-800 text-sm border-b pb-2">إضافة إعلان جديد</h4>
            <form action="{{ route('admin.marketing.ads.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان الحملة الإعلانية</label>
                    <input type="text" name="title" required placeholder="مثال: خصومات الربيع من شركة الأناقة" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم المعلن</label>
                    <input type="text" name="advertiser_name" required placeholder="اسم الشركة أو المعلن" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الرابط المستهدف (URL)</label>
                    <input type="url" name="target_url" placeholder="https://example.com/promo" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
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
                    <button type="button" onclick="document.getElementById('addAdModal').classList.add('hidden')" class="px-3 py-1.5 bg-slate-100 rounded-lg text-xs">إلغاء</button>
                    <button type="submit" class="px-4 py-1.5 bg-brand-600 text-white rounded-lg text-xs font-bold">حفظ الإعلان</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editAdModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
            <h4 class="font-bold text-slate-800 text-sm border-b pb-2">تعديل بيانات الإعلان</h4>
            <form id="editAdForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان الإعلان</label>
                    <input type="text" id="edit_ad_title" name="title" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم المعلن</label>
                    <input type="text" id="edit_ad_advertiser" name="advertiser_name" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الرابط المستهدف (URL)</label>
                    <input type="url" id="edit_ad_url" name="target_url" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ البدء</label>
                        <input type="date" id="edit_ad_start" name="start_date" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ الانتهاء</label>
                        <input type="date" id="edit_ad_end" name="end_date" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الحالة</label>
                    <select id="edit_ad_status" name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                        <option value="active">نشط (active)</option>
                        <option value="inactive">معطل (inactive)</option>
                        <option value="pending">معلق (pending)</option>
                        <option value="expired">منتهي (expired)</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('editAdModal').classList.add('hidden')" class="px-3 py-1.5 bg-slate-100 rounded-lg text-xs">إلغاء</button>
                    <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold">حفظ التعديل</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function editAd(ad) {
        document.getElementById('editAdForm').action = '/admin/marketing/ads/' + ad.id;
        document.getElementById('edit_ad_title').value = ad.title;
        document.getElementById('edit_ad_advertiser').value = ad.advertiser_name;
        document.getElementById('edit_ad_url').value = ad.target_url || '';
        document.getElementById('edit_ad_start').value = ad.start_date.substring(0, 10);
        document.getElementById('edit_ad_end').value = ad.end_date.substring(0, 10);
        document.getElementById('edit_ad_status').value = ad.status;
        document.getElementById('editAdModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
