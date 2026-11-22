@extends('admin.layout')

@section('title', 'أرباح ومستحقات الحرفيين')
@section('page_title', 'أرباح ومستحقات الحرفيين (Craftsman Earnings)')

@section('content')
<div class="space-y-6" x-data="{
    showViewModal: false,
    selectedEarning: null,
    viewEarning(ern) {
        this.selectedEarning = ern;
        this.showViewModal = true;
    }
}">

    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">سجل أرباح الحرفيين وعمولة المنصة</h3>
            <p class="text-xs text-slate-400">توزيع إيرادات الطلبات المنجزة بين الحرفي وحصة المنصة بالريال اليمني</p>
        </div>
        <form method="GET" action="{{ route('admin.accounting.earnings') }}" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <option value="">جميع الحالات</option>
                <option value="PENDING_APPROVAL" {{ request('status') == 'PENDING_APPROVAL' ? 'selected' : '' }}>بانتظار الاعتماد</option>
                <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>معتمد ومطابق</option>
                <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>ملغي</option>
            </select>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم الطلب</th>
                        <th class="py-3.5 px-4">الحرفي المستفيد</th>
                        <th class="py-3.5 px-4">إجمالي الخدمة</th>
                        <th class="py-3.5 px-4">تكلفة المواد</th>
                        <th class="py-3.5 px-4">صافي حصة الحرفي</th>
                        <th class="py-3.5 px-4">عمولة المنصة</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($earnings as $ern)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-primary-700">
                                @if($ern->serviceRequest)
                                    <a href="{{ route('admin.orders.show', $ern->service_request_id) }}" class="hover:underline">
                                        #{{ $ern->serviceRequest->request_number }}
                                    </a>
                                @else
                                    #{{ $ern->service_request_id }}
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-sans font-bold text-slate-800">{{ $ern->craftsmanUser->full_name ?? 'حرفي' }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ number_format($ern->total_service_amount, 2) }} ر.ي</td>
                            <td class="py-3.5 px-4 text-slate-500">{{ number_format($ern->materials_cost, 2) }} ر.ي</td>
                            <td class="py-3.5 px-4 font-bold text-emerald-700 bg-emerald-50/50">{{ number_format($ern->craftsman_share_amount, 2) }} ر.ي</td>
                            <td class="py-3.5 px-4 font-bold text-primary-700">{{ number_format($ern->company_share_amount, 2) }} ر.ي</td>
                            <td class="py-3.5 px-4 font-sans">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $ern->status === 'APPROVED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $ern->status === 'APPROVED' ? 'معتمد' : 'بانتظار الاعتماد' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-sans">
                                <div class="inline-flex items-center gap-1.5">
                                    <button @click="viewEarning({{ json_encode($ern) }})" class="p-1.5 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1" title="عرض تفاصيل الاحتساب">
                                        <i class="fa-solid fa-eye text-primary-600"></i>
                                        <span>تفاصيل</span>
                                    </button>

                                    @if($ern->status === 'PENDING_APPROVAL')
                                        <form action="{{ route('admin.accounting.earnings.approve', $ern->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                                اعتماد
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 font-sans">
                                لا توجد سجلات أرباح مسجلة حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($earnings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $earnings->links() }}
            </div>
        @endif
    </div>

    {{-- نافذة عرض تفاصيل احتساب الأرباح --}}
    <div x-show="showViewModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4" @click.outside="showViewModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-primary-600 text-lg"></i>
                    <h4 class="font-bold text-slate-800 text-sm">تفاصيل احتساب عمولة وأرباح الطلب</h4>
                </div>
                <button @click="showViewModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <template x-if="selectedEarning">
                <div class="space-y-4 text-xs">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-slate-500">الحرفي المستفيد:</span>
                            <span class="font-bold text-slate-800" x-text="selectedEarning.craftsman_user ? selectedEarning.craftsman_user.full_name : '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">رقم طلب الصيانة:</span>
                            <span class="font-mono font-bold text-primary-700" x-text="selectedEarning.service_request ? '#' + selectedEarning.service_request.request_number : '#' + selectedEarning.service_request_id"></span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 font-mono">
                            <span class="text-slate-600">إجمالي قيمة الخدمة:</span>
                            <span class="font-bold text-slate-900" x-text="parseFloat(selectedEarning.total_service_amount).toFixed(2) + ' ر.ي'"></span>
                        </div>
                        <div class="flex justify-between font-mono">
                            <span class="text-slate-600">تكلفة المواد وقطع الغيار:</span>
                            <span class="text-slate-700" x-text="parseFloat(selectedEarning.materials_cost).toFixed(2) + ' ر.ي'"></span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 font-mono">
                            <span class="text-emerald-700 font-bold">صافي أرباح الحرفي المستحقة:</span>
                            <span class="font-bold text-emerald-700 text-sm" x-text="parseFloat(selectedEarning.craftsman_share_amount).toFixed(2) + ' ر.ي'"></span>
                        </div>
                        <div class="flex justify-between font-mono">
                            <span class="text-primary-700 font-bold">عمولة وإيراد المنصة:</span>
                            <span class="font-bold text-primary-700" x-text="parseFloat(selectedEarning.company_share_amount).toFixed(2) + ' ر.ي'"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
