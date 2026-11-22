@extends('admin.layout')

@section('title', 'إشعارات تطبيق الهاتف')
@section('page_title', 'مركز الإشعارات والتنبيهات المباشرة (Push & In-App Notifications)')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">إشعارات التطبيق</h3>
            <p class="text-xs text-slate-400">إرسال تنبيهات جماعية وفردية للعملاء والحرفيين على هواتفهم</p>
        </div>
        <button onclick="document.getElementById('sendNotifModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2">
            <i class="fa-solid fa-paper-plane"></i>
            <span>إرسال إشعار جديد</span>
        </button>
    </div>

    <!-- Notifications Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">المستلم</th>
                        <th class="py-3.5 px-4">نوع الإشعار</th>
                        <th class="py-3.5 px-4">عنوان الإشعار</th>
                        <th class="py-3.5 px-4">نص الرسالة</th>
                        <th class="py-3.5 px-4">حالة القراءة</th>
                        <th class="py-3.5 px-4">تاريخ الإرسال</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($notifications as $notif)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $notif->user->full_name ?? 'جميع المستخدمين' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex px-2 py-0.5 rounded-lg bg-brand-50 text-brand-700 font-semibold text-[11px]">
                                    {{ $notif->notificationType->name ?? 'عام' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $notif->title }}</td>
                            <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">{{ $notif->body }}</td>
                            <td class="py-3.5 px-4">
                                @if($notif->is_read)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700">تمت القراءة</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700">جديد وغير مقروء</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 font-mono">{{ $notif->created_at ? $notif->created_at->format('Y-m-d H:i') : '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('admin.notifications.destroy', $notif->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإشعار؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-bell-slash text-4xl mb-3 text-slate-300 block"></i>
                                <span>لم يتم إرسال إشعارات بعد</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notifications->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    <!-- Send Notification Modal -->
    <div id="sendNotifModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">إرسال إشعار فوري للتطبيق</h4>
                <button onclick="document.getElementById('sendNotifModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.notifications.send') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الجمهور المستهدف</label>
                    <select name="target_audience" id="target_audience" onchange="toggleUserSelect(this.value)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="all">جميع مستخدمي وحرفيي التطبيق</option>
                        <option value="specific">مستخدم محدد</option>
                    </select>
                </div>

                <div id="user_select_div" class="hidden">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اختر المستخدم</label>
                    <select name="user_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <option value="">-- اختر المستخدم --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->full_name }} ({{ $u->phone_encrypted }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">نوع الإشعار</label>
                    <select name="notification_type_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        @foreach($types as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان الإشعار</label>
                    <input type="text" name="title" required placeholder="مثال: خصم خاص اليوم!" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">نص الإشعار</label>
                    <textarea name="body" required rows="3" placeholder="اكتب نص الإشعار الذي سيظهر في شريط تنبيهات هاتف العميل..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('sendNotifModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition">إرسال التنبيه الآن</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function toggleUserSelect(val) {
        const div = document.getElementById('user_select_div');
        if (val === 'specific') {
            div.classList.remove('hidden');
        } else {
            div.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection
