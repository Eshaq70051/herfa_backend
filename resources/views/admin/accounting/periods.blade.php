@extends('admin.layout')

@section('title', 'الفترات المحاسبية')
@section('page_title', 'الفترات المحاسبية وإقفال الحسابات (Accounting Periods)')

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false
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

    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">سجل الفترات والسنة المالية</h3>
            <p class="text-xs text-slate-400">إدارة دورات الفترات المحاسبية (مفتوحة، مقفلة، مجمدة) والرقابة على قيود اليومية</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.accounting.journal') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-book-journal-whills"></i>
                <span>دفتر قيود اليومية</span>
            </a>
            <button @click="showAddModal = true" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2 shadow-md shadow-primary-600/20">
                <i class="fa-solid fa-plus"></i>
                <span>فتح فترة مالية جديدة</span>
            </button>
        </div>
    </div>

    <!-- Periods Table -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5">رمز الفترة</th>
                        <th class="px-4 py-3.5">اسم الفترة المالية</th>
                        <th class="px-4 py-3.5">تاريخ البداية</th>
                        <th class="px-4 py-3.5">تاريخ النهاية</th>
                        <th class="px-4 py-3.5">القيود المسجلة</th>
                        <th class="px-4 py-3.5">الحالة</th>
                        <th class="px-4 py-3.5">إقفال بواسطة</th>
                        <th class="px-4 py-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($periods as $period)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5 font-mono font-bold text-primary-700 text-sm">#{{ $period->period_code }}</td>
                            <td class="px-4 py-3.5 font-bold text-slate-800">{{ $period->name }}</td>
                            <td class="px-4 py-3.5 font-mono text-slate-600">{{ $period->start_date }}</td>
                            <td class="px-4 py-3.5 font-mono text-slate-600">{{ $period->end_date }}</td>
                            <td class="px-4 py-3.5 font-bold text-primary-700">{{ $period->journal_entries_count }} قيود</td>
                            <td class="px-4 py-3.5">
                                @if($period->status === 'OPEN')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1 w-max">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>مفتوحة (OPEN)</span>
                                    </span>
                                @elseif($period->status === 'CLOSED')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 w-max inline-block">
                                        مقفلة (CLOSED)
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-200 w-max inline-block">
                                        مجمدة (LOCKED)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-500">
                                @if($period->status === 'CLOSED')
                                    <span class="text-[11px]">{{ $period->closedByUser->full_name ?? 'المدير' }}</span>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $period->closed_at ? \Carbon\Carbon::parse($period->closed_at)->format('Y-m-d') : '' }}</div>
                                @elseif($period->status === 'LOCKED')
                                    <span class="text-[11px]">{{ $period->lockedByUser->full_name ?? 'المدير' }}</span>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $period->locked_at ? \Carbon\Carbon::parse($period->locked_at)->format('Y-m-d') : '' }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($period->status === 'OPEN')
                                        <form action="{{ route('admin.accounting.periods.status', $period->id) }}" method="POST" onsubmit="return confirm('هل تريد بالتأكيد إقفال هذه الفترة المحاسبية؟')">
                                            @csrf
                                            <input type="hidden" name="action" value="close">
                                            <button type="submit" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-bold transition flex items-center gap-1" title="إقفال الفترة">
                                                <i class="fa-solid fa-lock text-[10px]"></i>
                                                <span>إقفال</span>
                                            </button>
                                        </form>
                                    @elseif($period->status === 'CLOSED')
                                        <form action="{{ route('admin.accounting.periods.status', $period->id) }}" method="POST" onsubmit="return confirm('تنبيه: قفل الفترة سيجعلها غير قابلة للتعديل. هل أنت متأكد؟')">
                                            @csrf
                                            <input type="hidden" name="action" value="lock">
                                            <button type="submit" class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-bold transition flex items-center gap-1" title="قفل وتجميد نهائي">
                                                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                                <span>تجميد</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.accounting.periods.status', $period->id) }}" method="POST" onsubmit="return confirm('هل تريد إعادة فتح الفترة المحاسبية؟')">
                                            @csrf
                                            <input type="hidden" name="action" value="reopen">
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition flex items-center gap-1" title="إعادة فتح">
                                                <i class="fa-solid fa-lock-open text-[10px]"></i>
                                                <span>إعادة فتح</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] text-slate-400 font-bold"><i class="fa-solid fa-lock"></i> مقفلة نهائياً</span>
                                    @endif

                                    @if($period->journal_entries_count == 0)
                                        <form action="{{ route('admin.accounting.periods.destroy', $period->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفترة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف الفترة">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">لا توجد فترات محاسبية مسجلة بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($periods->hasPages())
            <div class="border-t border-slate-100 p-4">{{ $periods->links() }}</div>
        @endif
    </div>

    <!-- MODAL: Add Period -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="showAddModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-primary-600"></i>
                    <span>فتح فترة محاسبية جديدة</span>
                </h4>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.accounting.periods.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رمز الفترة (الكود)</label>
                    <input name="period_code" maxlength="30" required value="FY-{{ date('Y-m') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-xs uppercase">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم الفترة المالية</label>
                    <input name="name" maxlength="100" required placeholder="مثال: الربع الأول 2026 أو شهر نوفمبر 2026" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ البداية</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-01') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ النهاية</label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-t') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-mono">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ملاحظات توضيحية</label>
                    <textarea name="notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs"></textarea>
                </div>
                <button type="submit" class="w-full rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-primary-700">
                    فتح الفترة المحاسبية
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
