@extends('admin.layout')

@section('title', 'تقييمات وآراء العملاء')
@section('page_title', 'تقييمات ومراجعات العملاء (Reviews)')

@section('content')
<div class="space-y-6">

    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <h3 class="font-bold text-slate-800 text-base">مراجعات وتقييمات العملاء للخدمات والحرفيين</h3>
        <p class="text-xs text-slate-400">مراقبة جودة الخدمة والموافقة على إظهار أو إخفاء التقييمات في المنصة</p>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم الطلب</th>
                        <th class="py-3.5 px-4">العميل</th>
                        <th class="py-3.5 px-4">الحرفي المقيم</th>
                        <th class="py-3.5 px-4">التقييم (النجوم)</th>
                        <th class="py-3.5 px-4">التعليق والملاحظات</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reviews as $rev)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700">#{{ $rev->serviceRequest->request_number ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $rev->customer->full_name ?? 'عميل' }}</td>
                            <td class="py-3.5 px-4 text-slate-700 font-semibold">{{ $rev->craftsmanProfile->user->full_name ?? 'حرفي' }}</td>
                            <td class="py-3.5 px-4 text-amber-500 font-bold">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star {{ $i <= $rev->rating ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                @endfor
                                <span class="text-slate-600 mr-1 font-mono">({{ $rev->rating }})</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 max-w-xs truncate">{{ $rev->comment ?? '-' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold 
                                    {{ $rev->review_status === 'visible' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $rev->review_status === 'hidden' ? 'bg-slate-100 text-slate-600' : '' }}
                                    {{ $rev->review_status === 'reported' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}">
                                    {{ $rev->review_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('admin.support.reviews.status', $rev->id) }}" method="POST" class="inline-flex gap-1">
                                    @csrf
                                    @if($rev->review_status !== 'visible')
                                        <input type="hidden" name="review_status" value="visible">
                                        <button type="submit" class="px-2.5 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded text-[11px] font-bold transition">
                                            إظهار
                                        </button>
                                    @else
                                        <input type="hidden" name="review_status" value="hidden">
                                        <button type="submit" class="px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded text-[11px] font-bold transition">
                                            إخفاء
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">لا توجد تقييمات مسجلة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
