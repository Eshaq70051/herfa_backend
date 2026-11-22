@extends('admin.layout')

@section('title', 'تحصيلات الميدان والتسويات')
@section('page_title', 'تحصيلات الميدان (Craftsman Collections)')

@section('content')
<div class="space-y-6" x-data="{
    showViewModal: false,
    selectedCollection: null,
    viewCollection(col) {
        this.selectedCollection = col;
        this.showViewModal = true;
    }
}">

    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">سجل تحصيلات الحرفيين الميدانية</h3>
            <p class="text-xs text-slate-400">المبالغ النقدية المحصلة من العملاء والتحقق من توريدها بالريال اليمني</p>
        </div>
        <form method="GET" action="{{ route('admin.accounting.collections') }}">
            <select name="status" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <option value="">جميع الحالات</option>
                <option value="PENDING_VERIFICATION" {{ request('status') == 'PENDING_VERIFICATION' ? 'selected' : '' }}>بانتظار التحقق</option>
                <option value="CONFIRMED" {{ request('status') == 'CONFIRMED' ? 'selected' : '' }}>مؤكد ومورد</option>
            </select>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم السند/الإيصال</th>
                        <th class="py-3.5 px-4">رقم الطلب</th>
                        <th class="py-3.5 px-4">الحرفي المحصل</th>
                        <th class="py-3.5 px-4">العميل</th>
                        <th class="py-3.5 px-4">المبلغ المحصل</th>
                        <th class="py-3.5 px-4">تاريخ التحصيل</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($collections as $col)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-primary-700">#{{ $col->receipt_number }}</td>
                            <td class="py-3.5 px-4 font-mono">
                                @if($col->serviceRequest)
                                    <a href="{{ route('admin.orders.show', $col->service_request_id) }}" class="text-blue-600 font-bold hover:underline">
                                        #{{ $col->serviceRequest->request_number }}
                                    </a>
                                @else
                                    #{{ $col->service_request_id }}
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $col->craftsmanUser->full_name ?? 'حرفي' }}</td>
                            <td class="py-3.5 px-4 text-slate-600">{{ $col->customerUser->full_name ?? 'عميل' }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-700 bg-emerald-50/50">
                                {{ number_format($col->collected_amount, 2) }} ر.ي
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-400">{{ $col->collected_at }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $col->status === 'CONFIRMED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $col->status === 'CONFIRMED' ? 'مؤكد ومورد' : 'بانتظار التحقق' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button @click="viewCollection({{ json_encode($col) }})" class="p-1.5 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1" title="عرض تفاصيل السند">
                                        <i class="fa-solid fa-eye text-primary-600"></i>
                                        <span>تفاصيل</span>
                                    </button>

                                    @if($col->status === 'PENDING_VERIFICATION')
                                        <form action="{{ route('admin.accounting.collections.verify', $col->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                                تأكيد
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                لا توجد سندات تحصيل مسجلة حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($collections->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $collections->links() }}
            </div>
        @endif
    </div>

    {{-- نافذة عرض تفاصيل سند التحصيل --}}
    <div x-show="showViewModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4" @click.outside="showViewModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-primary-600 text-lg"></i>
                    <h4 class="font-bold text-slate-800 text-sm">تفاصيل إيصال التحصيل الميداني</h4>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-print text-primary-600"></i>
                        <span>طباعة السند</span>
                    </button>
                    <button @click="showViewModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <template x-if="selectedCollection">
                <div class="space-y-4 text-xs">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-slate-500">رقم الإيصال:</span>
                            <span class="font-mono font-bold text-primary-700" x-text="'#' + selectedCollection.receipt_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">رقم طلب الصيانة:</span>
                            <span class="font-mono font-bold text-blue-600" x-text="selectedCollection.service_request ? '#' + selectedCollection.service_request.request_number : '#' + selectedCollection.service_request_id"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">الحرفي المحصل:</span>
                            <span class="font-bold text-slate-800" x-text="selectedCollection.craftsman_user ? selectedCollection.craftsman_user.full_name : '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">العميل المسدد:</span>
                            <span class="font-bold text-slate-800" x-text="selectedCollection.customer_user ? selectedCollection.customer_user.full_name : '-'"></span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 font-mono">
                            <span class="text-emerald-700 font-bold">المبلغ المحصل نقداً:</span>
                            <span class="font-bold text-emerald-700 text-sm" x-text="parseFloat(selectedCollection.collected_amount).toFixed(2) + ' ر.ي'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">تاريخ وساعة التحصيل:</span>
                            <span class="font-mono text-slate-700" x-text="selectedCollection.collected_at || '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">حالة التوريد:</span>
                            <span class="font-bold" :class="selectedCollection.status === 'CONFIRMED' ? 'text-emerald-700' : 'text-amber-600'" x-text="selectedCollection.status === 'CONFIRMED' ? 'تم توريده وتأكيده' : 'بانتظار التحقق والتوريد'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-100 text-center text-[11px] text-slate-500">
                        <div class="space-y-1">
                            <span>توقيع الحرفي المورّد</span>
                            <div class="h-10 border-b border-dashed border-slate-300"></div>
                        </div>
                        <div class="space-y-1">
                            <span>توقيع أمين الصندوق المستلم</span>
                            <div class="h-10 border-b border-dashed border-slate-300"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
