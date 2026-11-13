@extends('admin.layout')

@section('title', 'تفاصيل الطلب ' . $order->request_number)
@section('page_title', 'تفاصيل طلب الصيانة: ' . $order->request_number)

@section('content')
<div class="space-y-6" x-data="{ editModalOpen: false }">

    <!-- Top Action Nav -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-emerald-600 transition">
            <i class="fa-solid fa-arrow-right"></i>
            <span>العودة لقائمة الطلبات</span>
        </a>

        <div class="flex items-center gap-3">
            <button @click="editModalOpen = true" class="px-4 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>تعديل تفاصيل الطلب</span>
            </button>

            <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا الطلب نهائياً؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-trash"></i>
                    <span>حذف الطلب</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Order Details Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Details & Timeline -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Primary Info -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <span class="font-mono text-sm font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200">{{ $order->request_number }}</span>
                        <h3 class="text-xl font-bold text-gray-800 mt-2">{{ $order->service->name ?? 'خدمة صيانة' }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">قسم: {{ $order->service->category->name ?? 'عام' }}</p>
                    </div>
                    <div class="text-left">
                        <span class="text-xs text-gray-400 block">السعر الأساسي</span>
                        <span class="text-2xl font-mono font-black text-gray-900">{{ number_format($order->service->base_price ?? 80, 2) }} ر.س</span>
                        <span class="text-[11px] text-amber-600 block mt-0.5">ضمان {{ $order->service->warranty_days ?? 30 }} يوم</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">وصف العطل / ملاحظات العميل:</h4>
                    <p class="text-sm text-gray-700 bg-gray-50 p-4 rounded-xl leading-relaxed">{{ $order->description }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs pt-2">
                    <div>
                        <span class="text-gray-400 block mb-0.5">العنوان المسجل:</span>
                        <span class="font-bold text-gray-800">{{ $order->address_details ?? 'حي النرجس، الرياض' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block mb-0.5">تاريخ وتوقيت الزيارة المفضل:</span>
                        <span class="font-bold text-gray-800 font-mono">{{ $order->preferred_date ? \Carbon\Carbon::parse($order->preferred_date)->format('Y-m-d') : ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') : '-') }}</span>
                    </div>
                </div>
            </div>

            <!-- Status History Timeline -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <h4 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                    <span>سجل تتبع مراحل الطلب (Timeline)</span>
                </h4>

                <div class="space-y-4 pr-2">
                    @forelse($order->history as $h)
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-gray-800">{{ $h->notes ?? 'تغيير حالة الطلب' }}</span>
                                    <span class="text-gray-400 font-mono">{{ $h->created_at ? \Carbon\Carbon::parse($h->created_at)->format('Y-m-d H:i') : '-' }}</span>
                                </div>
                                <span class="text-[11px] text-gray-500">بواسطة: {{ $h->changedBy->full_name ?? 'النظام الآلي' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-gray-400">لا يوجد سجل تاريخي حتى الآن</div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Col: Status Management, Customer, Craftsman -->
        <div class="space-y-6">

            <!-- Status & Assignment Form -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <h4 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-emerald-600"></i>
                    <span>إدارة وتحديث حالة الطلب</span>
                </h4>

                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">الحالة الحالية</label>
                        <select name="status_id" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                            @foreach($statuses as $st)
                                <option value="{{ $st->id }}" {{ $order->status_id == $st->id ? 'selected' : '' }}>{{ $st->description ?? $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">الحرفي المسند للطلب (يشترط فتح الحالة)</label>
                        <select name="craftsman_id" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">بدون حرفي (بانتظار قبول)</option>
                            @php
                                $curCraftsmanId = $order->assignments->first()->craftsman_id ?? null;
                            @endphp
                            @foreach($craftsmen as $cm)
                                @php
                                    $isOpen = $cm->account_status === 'active'
                                        && ($cm->craftsmanProfile->approval_status ?? '') === 'approved'
                                        && (($cm->craftsmanProfile->availability_status ?? false) || $cm->is_online);
                                @endphp
                                <option value="{{ $cm->id }}" {{ $curCraftsmanId == $cm->id ? 'selected' : '' }} {{ !$isOpen && $curCraftsmanId != $cm->id ? 'disabled class=text-gray-400 bg-gray-100' : '' }}>
                                    {{ $cm->full_name }} ({{ $cm->phone }}) - {{ $isOpen ? '🟢 متاح' : '🔴 مغلق الحالة' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1">لا يمكن تعيين حرفي حالته مغلقة.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">ملاحظات الإدارة للتحديث</label>
                        <input type="text" name="notes" placeholder="سبب التغيير..." class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition">
                        حفظ التعديل وإشعار الأطراف
                    </button>
                </form>
            </div>

            <!-- Customer Info Card -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-3">
                <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user text-emerald-600"></i>
                    <span>بيانات العميل</span>
                </h4>
                <div class="text-xs space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-400">الاسم:</span>
                        <span class="font-bold text-gray-800">{{ $order->customer->full_name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">رقم الجوال:</span>
                        <span class="font-bold text-gray-800 font-mono">{{ $order->customer->phone ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">البريد:</span>
                        <span class="text-gray-600">{{ $order->customer->email ?? 'لا يوجد' }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Edit Order Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="editModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                    <span>تعديل تفاصيل الطلب</span>
                </h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">وصف العطل والملاحظات *</label>
                    <textarea name="description" rows="3" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-blue-500">{{ $order->description }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">العنوان والتفاصيل</label>
                    <input type="text" name="address_details" value="{{ $order->address_details }}" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">تاريخ الزيارة المفضل</label>
                    <input type="date" name="preferred_date" value="{{ $order->preferred_date ? date('Y-m-d', strtotime($order->preferred_date)) : date('Y-m-d') }}" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">تحديث البيانات</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
