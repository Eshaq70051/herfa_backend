@extends('admin.layout')

@section('title', 'تذكرة #' . $ticket->ticket_number)
@section('page_title', 'تذكرة دعم فني: #' . $ticket->ticket_number)

@section('content')
<div class="space-y-6">

    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-bold text-slate-800">{{ $ticket->subject }}</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    {{ $ticket->ticket_status }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">المرسل: <strong class="text-slate-700">{{ $ticket->user->full_name ?? 'مستخدم' }}</strong> | التاريخ: {{ $ticket->created_at }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.support.tickets') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                العودة للتذاكر
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Messages thread (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Original Description -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-2">
                <span class="text-xs font-bold text-slate-400">الوصف الأولي للمشكلة:</span>
                <p class="text-xs text-slate-700 leading-relaxed font-medium bg-slate-50 p-4 rounded-xl border border-slate-100">
                    {{ $ticket->description }}
                </p>
            </div>

            <!-- Messages List -->
            <div class="space-y-3">
                @foreach($ticket->supportMessages as $msg)
                    <div class="p-4 rounded-2xl border {{ $msg->sender_id === auth()->id() ? 'bg-brand-50/40 border-brand-200 mr-8' : 'bg-white border-slate-200/80 ml-8' }} space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-800">{{ $msg->user->full_name ?? 'مستخدم' }}</span>
                            <span class="font-mono text-slate-400 text-[11px]">{{ $msg->created_at }}</span>
                        </div>
                        <p class="text-xs text-slate-700 leading-relaxed font-medium">{{ $msg->message }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Reply Box -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <form action="{{ route('admin.support.tickets.reply', $ticket->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">الرد على التذكرة:</label>
                        <textarea name="message" rows="3" required placeholder="اكتب ردك هنا..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500"></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-xs text-slate-500 cursor-pointer">
                            <input type="checkbox" name="is_internal" value="1" class="rounded text-brand-600">
                            <span>ملاحظة داخلية لفريق الإدارة فقط</span>
                        </label>
                        <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition">
                            إرسال الرد
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Status update & Ticket Info (1 col) -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <h4 class="font-bold text-slate-800 text-sm border-b pb-2">تحديث حالة التذكرة</h4>
                <form action="{{ route('admin.support.tickets.status', $ticket->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <select name="ticket_status" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                            <option value="open" {{ $ticket->ticket_status == 'open' ? 'selected' : '' }}>مفتوحة (Open)</option>
                            <option value="in_progress" {{ $ticket->ticket_status == 'in_progress' ? 'selected' : '' }}>قيد المتابعة (In Progress)</option>
                            <option value="resolved" {{ $ticket->ticket_status == 'resolved' ? 'selected' : '' }}>تم الحل (Resolved)</option>
                            <option value="closed" {{ $ticket->ticket_status == 'closed' ? 'selected' : '' }}>مغلقة (Closed)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                        حفظ الحالة
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
