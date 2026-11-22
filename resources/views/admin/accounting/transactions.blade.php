@extends('admin.layout')

@section('title', 'العمليات المالية والمدفوعات الإلكترونية')
@section('page_title', 'سجل المعاملات والمدفوعات (Transactions & Payments)')

@section('content')
<div class="space-y-6">

    <!-- Filters & Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-800 text-base">سجل مدفوعات التطبيق</h3>
            <p class="text-xs text-slate-400">متابعة عمليات الدفع عبر الكريمي، جوالي، ون كاش، جيب، والنقدية</p>
        </div>

        <form method="GET" action="{{ route('admin.accounting.transactions') }}" class="flex flex-wrap items-center gap-2.5">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="رقم العملية، الحوالة، العميل..." class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500 w-48">
            
            <select name="payment_method_id" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                <option value="">جميع طرق الدفع</option>
                @foreach($paymentMethods as $pm)
                    <option value="{{ $pm->id }}" {{ request('payment_method_id') == $pm->id ? 'selected' : '' }}>{{ $pm->name }}</option>
                @endforeach
            </select>

            <select name="payment_status" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                <option value="">جميع الحالات</option>
                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مكتملة ومؤكدة</option>
                <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>فاشلة</option>
                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>مسترجعة</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition">
                <i class="fa-solid fa-filter ml-1"></i> تصفية
            </button>
            @if(request()->hasAny(['search', 'payment_method_id', 'payment_status']))
                <a href="{{ route('admin.accounting.transactions') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</a>
            @endif
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم العملية</th>
                        <th class="py-3.5 px-4">العميل</th>
                        <th class="py-3.5 px-4">رقم الطلب</th>
                        <th class="py-3.5 px-4">طريقة الدفع</th>
                        <th class="py-3.5 px-4">المبلغ</th>
                        <th class="py-3.5 px-4">مرجع الحوالة / الإيصال</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4">تاريخ العملية</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-brand-700">#{{ $trx->transaction_number }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $trx->user->full_name ?? 'غير محدد' }}</td>
                            <td class="py-3.5 px-4">
                                @if($trx->request)
                                    <a href="{{ route('admin.requests.show', $trx->request->id) }}" class="font-mono text-brand-600 hover:underline">
                                        #{{ $trx->request->request_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                    {{ $trx->paymentMethod->name ?? 'غير محدد' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold font-mono text-slate-900">
                                {{ number_format($trx->amount, 2) }} {{ $trx->currency->code ?? 'YER' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">{{ $trx->provider_transaction_id ?? '-' }}</td>
                            <td class="py-3.5 px-4">
                                @if($trx->payment_status === 'paid')
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">مكتملة</span>
                                @elseif($trx->payment_status === 'pending')
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">قيد المراجعة</span>
                                @elseif($trx->payment_status === 'failed')
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">فاشلة</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">مسترجعة</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 font-mono">{{ $trx->created_at ? $trx->created_at->format('Y-m-d H:i') : '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <button onclick="changeStatus({{ $trx->id }}, '{{ $trx->payment_status }}')" class="px-2.5 py-1 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold rounded-lg text-xs transition">
                                    تغيير الحالة
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-4xl mb-3 text-slate-300 block"></i>
                                <span>لم يتم تسجيل عمليات مالية بعد</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Update Status Modal -->
    <div id="statusModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تحديث حالة المعاملة</h4>
                <button onclick="document.getElementById('statusModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="statusForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الحالة الجديدة</label>
                    <select id="modal_status" name="payment_status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="pending">قيد المراجعة والانتظار</option>
                        <option value="paid">مكتملة ومؤكدة (مدفوعة)</option>
                        <option value="failed">فاشلة / مرفوضة</option>
                        <option value="refunded">مسترجعة للعميل</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('statusModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition">حفظ الحالة</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function changeStatus(id, currentStatus) {
        document.getElementById('statusForm').action = '/admin/accounting/transactions/' + id + '/status';
        document.getElementById('modal_status').value = currentStatus;
        document.getElementById('statusModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
