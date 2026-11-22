@extends('admin.layout')

@section('title', 'لوحة التحكم الرئيسية')
@section('page_title', 'نظرة عامة على المنظومة والعمليات (Analytics Dashboard)')

@section('content')
<div class="space-y-6">

    @if($openDisputes > 0 || $pendingPayoutRequests > 0 || $pendingVerifications > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @if($openDisputes > 0)
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center font-bold">
                    <i class="fa-solid fa-scale-unbalanced text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-red-900">{{ $openDisputes }} نزاعات بانتظار الفصل</h4>
                    <p class="text-xs text-red-600">شكاوى ومطالبات ضمان من العملاء</p>
                </div>
            </div>
            <a href="{{ route('admin.tickets.index') }}" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 transition">
                مراجعة
            </a>
        </div>
        @endif

        @if($pendingPayoutRequests > 0)
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold">
                    <i class="fa-solid fa-money-bill-transfer text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-amber-900">{{ $pendingPayoutRequests }} طلبات سحب أرباح</h4>
                    <p class="text-xs text-amber-600">بانتظار التحويل والتدقيق المالي</p>
                </div>
            </div>
            <a href="{{ route('admin.accounting.payouts') }}" class="px-3 py-1.5 bg-amber-600 text-white text-xs font-bold rounded-lg hover:bg-amber-700 transition">
                معالجة
            </a>
        </div>
        @endif

        @if($pendingVerifications > 0)
        <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">
                    <i class="fa-solid fa-id-card text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-blue-900">{{ $pendingVerifications }} فني بانتظار الاعتماد</h4>
                    <p class="text-xs text-blue-600">فحص وثائق الهوية والشهادات</p>
                </div>
            </div>
            <a href="{{ route('admin.craftsmen.index', ['verification' => 'pending']) }}" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition">
                فحص
            </a>
        </div>
        @endif
    </div>
    @endif

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Card 1: Total Revenue -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase">إجمالي المبيعات المنفذة</p>
                <h3 class="text-2xl font-black text-gray-900 mt-1">{{ number_format($totalRevenue, 2) }} <span class="text-xs font-bold text-primary-600">ر.ي</span></h3>
                <p class="text-xs text-gray-400 mt-1 flex items-center gap-1 font-medium">
                    <i class="fa-solid fa-arrow-trend-up text-primary-500"></i>
                    <span>مكتمل ومدفوع</span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>

        <!-- Card 2: Platform Net Commission -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase">صافي عمولات المنصة</p>
                <h3 class="text-2xl font-black text-primary-600 mt-1">{{ number_format($platformEarnings, 2) }} <span class="text-xs font-bold text-primary-700">ر.ي</span></h3>
                <p class="text-xs text-gray-400 mt-1 flex items-center gap-1 font-medium">
                    <i class="fa-solid fa-percent text-primary-500"></i>
                    <span>متوسط عمولة 15%</span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>

        <!-- Card 3: Total Orders -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase">الطلبات الكلية</p>
                <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $totalOrders }} <span class="text-xs font-bold text-gray-400">طلب</span></h3>
                <p class="text-xs text-amber-600 mt-1 flex items-center gap-1 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>{{ $activeOrders }} طلب جاري التنفيذ</span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
        </div>

        <!-- Card 4: Craftsmen -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase">شبكة الحرفيين</p>
                <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $totalCraftsmen }} <span class="text-xs font-bold text-gray-400">فني</span></h3>
                <p class="text-xs text-emerald-600 mt-1 flex items-center gap-1 font-semibold">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>معتمدين وموثقين</span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-gear"></i>
            </div>
        </div>

    </div>

    <!-- Quick Actions Banner -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <a href="{{ route('admin.orders.index') }}" class="p-4 rounded-2xl bg-white border border-gray-200 shadow-sm hover:border-emerald-500 hover:shadow-md transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-plus"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-gray-800">إنشاء طلب جديد</h4>
                <p class="text-[11px] text-gray-400">إسناد خدمة لصالح عميل</p>
            </div>
        </a>

        <a href="{{ route('admin.craftsmen.index') }}" class="p-4 rounded-2xl bg-white border border-gray-200 shadow-sm hover:border-emerald-500 hover:shadow-md transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-gray-800">اعتماد الحرفيين</h4>
                <p class="text-[11px] text-gray-400">مراجعة وثائق الهوية والمهنة</p>
            </div>
        </a>

        <a href="{{ route('admin.locations.index') }}" class="p-4 rounded-2xl bg-white border border-gray-200 shadow-sm hover:border-emerald-500 hover:shadow-md transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-gray-800">التغطية الجغرافية</h4>
                <p class="text-[11px] text-gray-400">المحافظات والمدن والأحياء</p>
            </div>
        </a>

        <a href="{{ route('admin.advertisements.index') }}" class="p-4 rounded-2xl bg-white border border-gray-200 shadow-sm hover:border-emerald-500 hover:shadow-md transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-gray-800">العروض والخصومات</h4>
                <p class="text-[11px] text-gray-400">إنشاء عروض ترويجية للعملاء</p>
            </div>
        </a>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-base">أحدث طلبات الصيانة بالمنظومة</h3>
                <p class="text-xs text-gray-400 mt-0.5">آخر الطلبات المنشأة والمطلوب متابعتها</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1">
                <span>عرض الكل</span>
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50/70 text-gray-500 text-xs font-bold border-b border-gray-100 uppercase">
                        <th class="py-3 px-4">رقم الطلب</th>
                        <th class="py-3 px-4">الخدمة</th>
                        <th class="py-3 px-4">العميل</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4">التاريخ</th>
                        <th class="py-3 px-4 text-center">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="py-4 px-4 font-mono font-bold text-xs">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-800 hover:underline" title="عرض تفاصيل الطلب">
                                <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-black">#{{ $order->clean_number }}</span>
                                <span class="text-[10px] text-gray-400 font-normal">({{ $order->request_number }})</span>
                            </a>
                        </td>
                        <td class="py-4 px-4 font-semibold text-gray-900 text-xs">
                            {{ $order->service->name ?? '-' }}
                        </td>
                        <td class="py-4 px-4 text-xs">
                            <div class="font-bold text-gray-800">{{ $order->customer->full_name ?? '-' }}</div>
                            <div class="text-gray-400 font-mono">{{ $order->customer->phone ?? '' }}</div>
                        </td>
                        <td class="py-4 px-4">
                            @php
                                $statusCode = strtolower($order->status->code ?? $order->status->name ?? 'pending');
                            @endphp
                            @if(in_array($statusCode, ['completed', 'مكتمل']))
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">مكتمل</span>
                            @elseif(in_array($statusCode, ['in_progress', 'قيد التنفيذ']))
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-teal-50 text-teal-700 border border-teal-200">قيد التنفيذ</span>
                            @elseif(in_array($statusCode, ['cancelled', 'ملغي']))
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-red-50 text-red-700 border border-red-200">ملغي</span>
                            @elseif(in_array($statusCode, ['assigned', 'accepted', 'تم الإسناد', 'مقبول']))
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-blue-50 text-blue-700 border border-blue-200">{{ $order->status->name ?? 'مسند' }}</span>
                            @else
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-amber-50 text-amber-700 border border-amber-200">بانتظار فني</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-xs text-gray-400 font-mono">
                            {{ $order->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-xs text-emerald-600 font-bold hover:underline">
                                التفاصيل
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-400 text-xs">لا توجد طلبات حديثة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
