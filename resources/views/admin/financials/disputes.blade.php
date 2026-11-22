@extends('admin.layout')

@section('title', 'إدارة النزاعات والشكاوى')
@section('page_title', 'إدارة النزاعات وضمان الجودة (Service Disputes & Guarantees)')

@section('content')
<div class="space-y-6" x-data="{
    showResolveModal: false,
    selectedDispute: {},
    disputeStatus: 'RESOLVED_REFUND',
    refundAmount: 0,
    resolutionNotes: ''
}">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-800">قائمة النزاعات والاعتراضات على الطلبات</h3>
            <p class="text-xs text-gray-500 mt-1">متابعة ومعالجة شكاوى العملاء والحرفيين وتطبيق التعويضات أو إعادة التنفيذ بضمان المنصة</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold">
                إجمالي النزاعات: {{ $disputes->total() }}
            </span>
        </div>
    </div>

    <!-- Disputes Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-gray-50 text-gray-600 border-b border-gray-200 font-bold">
                    <tr>
                        <th class="p-4"># المعرف</th>
                        <th class="p-4">رقم الطلب / الخدمة</th>
                        <th class="p-4">مقدم النزاع</th>
                        <th class="p-4">سبب النزاع</th>
                        <th class="p-4">التفاصيل</th>
                        <th class="p-4">الحالة</th>
                        <th class="p-4">التعويض المسترد</th>
                        <th class="p-4">تاريخ الفتح</th>
                        <th class="p-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($disputes as $d)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-4 font-bold text-gray-800">#{{ $d->id }}</td>
                        <td class="p-4">
                            <div class="font-bold text-blue-600">#{{ $d->serviceRequest?->request_number ?? $d->service_request_id }}</div>
                            <div class="text-[11px] text-gray-500">{{ $d->serviceRequest?->service?->name ?? 'خدمة صيانة' }}</div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-gray-800">{{ $d->raisedBy?->full_name ?? $d->raisedBy?->name ?? 'مستخدم' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $d->raisedBy?->phone ?? '-' }}</div>
                        </td>
                        <td class="p-4 font-medium text-gray-700">{{ $d->dispute_reason }}</td>
                        <td class="p-4 text-gray-600 max-w-xs truncate" title="{{ $d->details }}">{{ $d->details }}</td>
                        <td class="p-4">
                            @if($d->dispute_status === 'OPENED')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">مفتوح قيد التدقيق</span>
                            @elseif($d->dispute_status === 'RESOLVED_REFUND')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">تم التعويض المالي</span>
                            @elseif($d->dispute_status === 'RESOLVED_REWORK')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">إعادة تنفيذ بضمان</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-200">مرفوض</span>
                            @endif
                        </td>
                        <td class="p-4 font-bold text-emerald-600">
                            {{ $d->refund_amount > 0 ? number_format($d->refund_amount, 2) . ' ر.ي' : '-' }}
                        </td>
                        <td class="p-4 text-gray-500">{{ $d->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="p-4 text-center">
                            @if($d->dispute_status === 'OPENED' || $d->dispute_status === 'UNDER_REVIEW')
                            <button @click="selectedDispute = {{ json_encode($d) }}; refundAmount = {{ $d->serviceRequest?->total_amount ?? 0 }}; showResolveModal = true;" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition">
                                الفصل والحل
                            </button>
                            @else
                            <span class="text-xs text-gray-400">مكتمل</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-gray-400">
                            <i class="fa-solid fa-scale-balanced text-3xl mb-2 text-gray-300"></i>
                            <p>لا توجد أي نزاعات أو اعتراضات مفتوحة حالياً</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $disputes->links() }}
        </div>
    </div>

    <!-- Resolve Modal -->
    <div x-show="showResolveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-100 text-right space-y-4" @click.away="showResolveModal = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-base text-gray-800">اتخاذ القرار وحل النزاع #<span x-text="selectedDispute.id"></span></h3>
                <button @click="showResolveModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form :action="'/admin/disputes/' + selectedDispute.id + '/resolve'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">القرار الرسمي لحل النزاع</label>
                    <select name="dispute_status" x-model="disputeStatus" class="w-full text-xs p-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        <option value="RESOLVED_REFUND">قبول النزاع ورد المبلغ لمحفظة العميل (Refund)</option>
                        <option value="RESOLVED_REWORK">إلزام الحرفي بالصيانة المجانية التصحيحية (Rework)</option>
                        <option value="REJECTED">رفض النزاع واعتماد صحة التنفيذ (Reject)</option>
                    </select>
                </div>

                <div x-show="disputeStatus === 'RESOLVED_REFUND'">
                    <label class="block text-xs font-bold text-gray-700 mb-1">مبلغ التعويض المسترد للعميل</label>
                    <input type="number" step="0.01" name="refund_amount" x-model="refundAmount" class="w-full text-xs p-2.5 rounded-xl border border-gray-300">
                    <p class="text-[11px] text-gray-400 mt-1">سيتم إضافة هذا المبلغ فورياً إلى رصيد محفظة العميل داخل التطبيق.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">بيان وملاحظات القرار الإداري</label>
                    <textarea name="resolution_notes" rows="3" required placeholder="أدخل أسباب القرار وتوجيهات الإدارة للطرفين..." class="w-full text-xs p-2.5 rounded-xl border border-gray-300"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="showResolveModal = false" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md">تأكيد وحفظ القرار</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
