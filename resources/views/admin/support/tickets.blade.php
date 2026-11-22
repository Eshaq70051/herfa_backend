@extends('admin.layout')

@section('title', 'تذاكر الدعم والشكاوى')
@section('page_title', 'تذاكر الدعم والشكاوى (support_tickets)')

@section('content')
<div class="space-y-6">

    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-800 text-base">مركز خدمة العملاء وتذاكر الدعم</h3>
            <p class="text-xs text-slate-400">إدارة البلاغات ومتابعة استفسارات وشكاوى العملاء والحرفيين</p>
        </div>
        <form method="GET" action="{{ route('admin.support.tickets') }}" class="flex items-center gap-3">
            <select name="ticket_status" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <option value="">جميع الحالات</option>
                <option value="open" {{ request('ticket_status') == 'open' ? 'selected' : '' }}>مفتوحة (Open)</option>
                <option value="in_progress" {{ request('ticket_status') == 'in_progress' ? 'selected' : '' }}>قيد المتابعة (In Progress)</option>
                <option value="resolved" {{ request('ticket_status') == 'resolved' ? 'selected' : '' }}>تم الحل (Resolved)</option>
                <option value="closed" {{ request('ticket_status') == 'closed' ? 'selected' : '' }}>مغلقة (Closed)</option>
            </select>

            <select name="priority" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <option value="">جميع الأولويات</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>عالية (High)</option>
                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>متوسطة (Medium)</option>
                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>منخفضة (Low)</option>
            </select>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم التذكرة</th>
                        <th class="py-3.5 px-4">صاحب التذكرة</th>
                        <th class="py-3.5 px-4">الموضوع</th>
                        <th class="py-3.5 px-4">الأولوية</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4">تاريخ الفتح</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $t)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-brand-700">#{{ $t->ticket_number }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $t->user->full_name ?? 'مستخدم' }}</td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium">{{ $t->subject }}</td>
                            <td class="py-3.5 px-4">
                                @if($t->priority === 'high')
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">عالية</span>
                                @elseif($t->priority === 'medium')
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">متوسطة</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">عادية</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $t->ticket_status === 'open' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $t->ticket_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-400">{{ $t->created_at ? $t->created_at->format('Y-m-d H:i') : '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admin.support.tickets.show', $t->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs transition">
                                    <i class="fa-solid fa-comments"></i>
                                    <span>المحادثة والرد</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                لا توجد تذاكر دعم مسجلة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
