@extends('admin.layout')

@section('title', 'إدارة طلبات الخدمة')
@section('page_title', 'طلبات الخدمة (Service Requests)')

@section('content')
<div class="space-y-6">

    <!-- Filters & Search Bar -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.requests.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">بحث برقم الطلب أو اسم العميل</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث برقم الطلب أو العميل..." 
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">حالة الطلب</label>
                <select name="status_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                    <option value="">جميع الحالات</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st->id }}" {{ request('status_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">الخدمة المطلوبة</label>
                <select name="service_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                    <option value="">جميع الخدمات</option>
                    @foreach($services as $srv)
                        <option value="{{ $srv->id }}" {{ request('service_id') == $srv->id ? 'selected' : '' }}>{{ $srv->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-filter"></i>
                    <span>تصفية</span>
                </button>
                <a href="{{ route('admin.requests.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs transition">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم الطلب</th>
                        <th class="py-3.5 px-4">العميل</th>
                        <th class="py-3.5 px-4">الخدمة</th>
                        <th class="py-3.5 px-4">الموعد المطلوب</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4">تاريخ الإنشاء</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-brand-700">#{{ $req->request_number }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $req->user->full_name ?? 'غير محدد' }}</td>
                            <td class="py-3.5 px-4 text-slate-600 font-medium">{{ $req->service->name ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-slate-500 font-mono">{{ $req->preferred_date ? $req->preferred_date . ' ' . $req->preferred_time : 'فوري' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $req->requestStatus->name ?? 'غير محدد' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 font-mono">{{ $req->created_at ? $req->created_at->format('Y-m-d H:i') : '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.requests.show', $req->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-100 font-bold text-xs transition">
                                        <i class="fa-solid fa-eye"></i>
                                        <span>عرض</span>
                                    </a>
                                    <form action="{{ route('admin.requests.destroy', $req->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب نهائياً؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف الطلب">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-4xl mb-3 text-slate-300 block"></i>
                                <span>لا توجد طلبات خدمة مطابقة لمعايير البحث</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
