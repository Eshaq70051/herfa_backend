@extends('admin.layout')

@section('title', 'الدعم الفني والشكاوى')
@section('page_title', 'إدارة تذاكر الدعم الفني وبلاغات العملاء')

@section('content')
<div class="space-y-6" x-data="{
    showAddTicketModal: false,
    showDeleteTicketModal: false,
    selectedTicket: {}
}">

    <!-- Stats & Header -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-800">تذاكر الدعم والشكاوى</h3>
            <p class="text-xs text-gray-500 mt-1">متابعة شكاوى العملاء وملاحظات الحرفيين وحلها بأعلى معايير الجودة</p>
        </div>
        <button @click="showAddTicketModal = true" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>فتح تذكرة دعم جديدة</span>
        </button>
    </div>

    <!-- Tickets Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs uppercase">
                    <tr>
                        <th class="p-4">رقم التذكرة</th>
                        <th class="p-4">المستخدم / الشاكي</th>
                        <th class="p-4">موضوع التذكرة</th>
                        <th class="p-4">الأولوية</th>
                        <th class="p-4">الحالة</th>
                        <th class="p-4">تاريخ الفتح</th>
                        <th class="p-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="p-4 font-mono font-bold text-emerald-700">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="hover:underline">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-gray-800">{{ $ticket->user->full_name ?? 'مستخدم غير معروف' }}</div>
                                <div class="text-xs text-gray-500">{{ $ticket->user->phone ?? '-' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-gray-800">{{ $ticket->subject }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($ticket->description, 50) }}</div>
                            </td>
                            <td class="p-4">
                                @if($ticket->priority === 'urgent')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">حرجة جداً</span>
                                @elseif($ticket->priority === 'high')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">عالية</span>
                                @elseif($ticket->priority === 'medium')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">متوسطة</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">عادية</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($ticket->ticket_status === 'resolved')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">تم الحل</span>
                                @elseif($ticket->ticket_status === 'closed')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">مغلقة</span>
                                @elseif($ticket->ticket_status === 'in_progress')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">قيد المعالجة</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">مفتوحة جديدة</span>
                                @endif
                            </td>
                            <td class="p-4 text-xs text-gray-500 font-mono">
                                {{ $ticket->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="p-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition" title="عرض والرد">
                                        <i class="fa-solid fa-reply text-xs"></i>
                                    </a>
                                    <button @click="selectedTicket = { id: {{ $ticket->id }}, number: '{{ $ticket->ticket_number }}' }; showDeleteTicketModal = true" class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition" title="حذف التذكرة">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-gray-400">
                                <i class="fa-solid fa-headset text-4xl text-gray-300 mb-2"></i>
                                <p>لا توجد تذاكر دعم مسجلة حالياً</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: Add Ticket -->
    <div x-show="showAddTicketModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showAddTicketModal = false" class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-headset text-emerald-600"></i>
                <span>فتح تذكرة دعم فني جديدة</span>
            </h3>
            <form action="{{ route('admin.tickets.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">المستخدم / العميل *</label>
                    <select name="user_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">اختر المستخدم...</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->full_name }} ({{ $u->phone }}) - {{ $u->role === 'craftsman' ? 'حرفي' : 'عميل' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">موضوع التذكرة *</label>
                    <input type="text" name="subject" required placeholder="مثال: استفسار حول تسوية مستحقات أو شكوى بخصوص خدمة" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">الأولوية</label>
                    <select name="priority" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="medium">متوسطة</option>
                        <option value="low">منخفضة</option>
                        <option value="high">عالية</option>
                        <option value="urgent">حرجة وعاجلة</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">تفاصيل المشكلة / الشكوى *</label>
                    <textarea name="description" rows="3" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showAddTicketModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">فتح التذكرة</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Delete Ticket -->
    <div x-show="showDeleteTicketModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showDeleteTicketModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash-can text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">حذف تذكرة الدعم</h3>
            <p class="text-xs text-gray-500 mt-1">هل أنت متأكد من حذف التذكرة (<span class="font-bold text-red-600" x-text="selectedTicket.number"></span>) نهائياً مع كافة رسائلها؟</p>
            <form :action="'{{ url('/admin/tickets') }}/' + selectedTicket.id" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteTicketModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 shadow-md shadow-red-600/20">تأكيد الحذف</button>
            </form>
        </div>
    </div>

</div>
@endsection
