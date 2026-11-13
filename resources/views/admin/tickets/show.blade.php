@extends('admin.layout')

@section('title', 'تفاصيل تذكرة الدعم ' . $ticket->ticket_number)
@section('page_title', 'متابعة تذكرة الدعم: ' . $ticket->ticket_number)

@section('content')
<div class="space-y-6">

    <!-- Top Action Nav -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.tickets.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-emerald-600 transition">
            <i class="fa-solid fa-arrow-right"></i>
            <span>العودة لقائمة التذاكر</span>
        </a>
        <form action="{{ route('admin.tickets.destroy', $ticket->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذه التذكرة نهائياً؟');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-trash"></i>
                <span>حذف التذكرة</span>
            </button>
        </form>
    </div>

    <!-- Ticket Summary Card -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <div class="flex items-center gap-3">
                    <span class="font-mono text-base font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200">{{ $ticket->ticket_number }}</span>
                    <h3 class="text-xl font-bold text-gray-800">{{ $ticket->subject }}</h3>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-500 mt-2">
                    <div>المستخدم: <span class="font-bold text-gray-700">{{ $ticket->user->full_name ?? '-' }}</span> ({{ $ticket->user->phone ?? '-' }})</div>
                    <div>تاريخ الفتح: <span class="font-bold text-gray-700">{{ $ticket->created_at->format('Y-m-d H:i') }}</span></div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($ticket->priority === 'urgent')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">أولوية حرجة</span>
                @elseif($ticket->priority === 'high')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">أولوية عالية</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">أولوية عادية</span>
                @endif

                @if($ticket->ticket_status === 'resolved')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">تم الحل</span>
                @elseif($ticket->ticket_status === 'closed')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">مغلقة</span>
                @elseif($ticket->ticket_status === 'in_progress')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">قيد المعالجة</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">مفتوحة</span>
                @endif
            </div>
        </div>

        <!-- Initial Description -->
        <div class="mt-4 p-4 rounded-xl bg-gray-50 border border-gray-100 text-sm text-gray-700 leading-relaxed">
            <span class="font-bold text-gray-900 block mb-1">وصف المشكلة الأساسي:</span>
            {{ $ticket->description }}
        </div>
    </div>

    <!-- Messages / Replies Timeline -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-6">
        <h4 class="font-bold text-gray-800 text-base flex items-center gap-2">
            <i class="fa-solid fa-comments text-emerald-600"></i>
            <span>سجل الردود والمحادثات</span>
        </h4>

        <div class="space-y-4">
            @forelse($ticket->messages as $msg)
                <div class="p-4 rounded-2xl border {{ $msg->sender_id === auth()->id() ? 'bg-emerald-50/50 border-emerald-100 mr-8' : 'bg-gray-50 border-gray-200 ml-8' }}">
                    <div class="flex items-center justify-between text-xs mb-2">
                        <span class="font-bold {{ $msg->sender_id === auth()->id() ? 'text-emerald-800' : 'text-gray-800' }}">
                            {{ $msg->sender->full_name ?? ($msg->sender_id === auth()->id() ? 'إدارة المنظومة' : 'العميل') }}
                        </span>
                        <span class="text-gray-400 font-mono">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $msg->message }}</p>
                </div>
            @empty
                <div class="text-center py-6 text-gray-400 text-xs">لا توجد ردود سابقة على هذه التذكرة بعد</div>
            @endforelse
        </div>

        <!-- Reply Form -->
        <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST" class="pt-6 border-t border-gray-100 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">إرسال رد جديد وتحديث الحالة</label>
                <textarea name="message" rows="3" required placeholder="اكتب ردك هنا..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
            </div>
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <label class="text-xs font-bold text-gray-700 whitespace-nowrap">تحديث حالة التذكرة:</label>
                    <select name="ticket_status" class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="in_progress" {{ $ticket->ticket_status === 'in_progress' ? 'selected' : '' }}>قيد المعالجة (In Progress)</option>
                        <option value="resolved" {{ $ticket->ticket_status === 'resolved' ? 'selected' : '' }}>تم الحل (Resolved)</option>
                        <option value="closed" {{ $ticket->ticket_status === 'closed' ? 'selected' : '' }}>إغلاق التذكرة (Closed)</option>
                        <option value="open" {{ $ticket->ticket_status === 'open' ? 'selected' : '' }}>إبقاء مفتوحة (Open)</option>
                    </select>
                </div>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>إرسال الرد وتحديث الحالة</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
