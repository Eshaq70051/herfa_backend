@extends('admin.layout')

@section('title', 'طلبات سحب أرباح الحرفيين')
@section('page_title', 'طلبات سحب وتحويل مستحقات الحرفيين (Craftsmen Payout Requests)')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-800">إدارة طلبات السحب والتحويلات البنكية</h3>
            <p class="text-xs text-gray-500 mt-1">مراجعة طلبات سحب الأرباح المقدمة من الحرفيين والموافقة عليها بعد إجراء التحويل البنكي</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold">
                إجمالي طلبات السحب: {{ $payouts->total() }}
            </span>
        </div>
    </div>

    <!-- Payouts Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-gray-50 text-gray-600 border-b border-gray-200 font-bold">
                    <tr>
                        <th class="p-4"># المعرف</th>
                        <th class="p-4">الحرفي / الفني</th>
                        <th class="p-4">المبلغ المطلوب</th>
                        <th class="p-4">اسم البنك / المحفظة</th>
                        <th class="p-4">رقم الآيبان / الحساب</th>
                        <th class="p-4">الحالة</th>
                        <th class="p-4">تاريخ الطلب</th>
                        <th class="p-4">ملاحظات الإدارة</th>
                        <th class="p-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payouts as $p)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-4 font-bold text-gray-800">#{{ $p->id }}</td>
                        <td class="p-4">
                            <div class="font-bold text-gray-800">{{ $p->craftsman?->full_name ?? $p->craftsman?->name ?? 'حرفي' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $p->craftsman?->phone ?? '-' }}</div>
                        </td>
                        <td class="p-4 font-bold text-emerald-600 text-sm">
                            {{ number_format($p->amount, 2) }} ر.ي
                        </td>
                        <td class="p-4 font-bold text-gray-700">{{ $p->bank_name }}</td>
                        <td class="p-4 font-mono text-gray-800 dir-ltr text-left">{{ $p->iban }}</td>
                        <td class="p-4">
                            @if($p->status === 'pending')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">بانتظار التحويل</span>
                            @elseif($p->status === 'approved')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">تم التحويل بنجاح ✓</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">مرفوض مع إعادة الرصيد</span>
                            @endif
                        </td>
                        <td class="p-4 text-gray-500">{{ $p->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="p-4 text-gray-600 max-w-xs truncate">{{ $p->admin_notes ?? '-' }}</td>
                        <td class="p-4 text-center">
                            @if($p->status === 'pending')
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('admin.financials.payout-requests.approve', $p->id) }}" method="POST" onsubmit="return confirm('هل قمت بالتحويل البنكي الفعلي وتريد تأكيد الصرف؟')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                                        تأكيد الصرف ✓
                                    </button>
                                </form>
                                <form action="{{ route('admin.financials.payout-requests.reject', $p->id) }}" method="POST" onsubmit="return confirm('هل تريد رفض طلب السحب وإعادة الرصيد لمحفظة الحرفي؟')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs border border-red-200 transition">
                                        رفض وإعادة
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="text-xs text-gray-400">مكتمل</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-gray-400">
                            <i class="fa-solid fa-money-bill-transfer text-3xl mb-2 text-gray-300"></i>
                            <p>لا توجد طلبات سحب أرباح معلقة حالياً</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $payouts->links() }}
        </div>
    </div>

</div>
@endsection
