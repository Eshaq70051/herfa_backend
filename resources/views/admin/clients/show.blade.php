@extends('admin.layout')

@section('title', 'تفاصيل العميل: ' . $client->full_name)
@section('page_title', 'ملف العميل والطلبات: ' . $client->full_name)

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: window.location.hash ? window.location.hash.substring(1) : 'orders',
    editModalOpen: false,
    deleteModalOpen: false,
    editData: {
        id: '{{ $client->id }}',
        full_name: '{{ addslashes($client->full_name) }}',
        phone: '{{ $client->phone }}',
        email: '{{ $client->email }}',
        wallet_balance: '{{ $client->wallet_balance }}',
        account_status: '{{ $client->account_status }}'
    }
}">

    <!-- Top Action Nav -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.clients.index') }}" class="p-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition" title="العودة لقائمة العملاء">
                <i class="fa-solid fa-arrow-right text-sm"></i>
            </a>
            <div>
                <h2 class="text-lg font-black text-gray-900 flex items-center gap-2">
                    <span>{{ $client->full_name }}</span>
                    <span class="text-xs font-mono font-normal text-gray-400 bg-gray-100 px-2 py-0.5 rounded-md">#{{ $client->id }}</span>
                </h2>
                <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-2">
                    <span><i class="fa-regular fa-calendar ml-1"></i> مسجل منذ: {{ $client->created_at->format('Y-m-d') }} ({{ $client->created_at->diffForHumans() }})</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Toggle Account Status (Block / Unblock) -->
            <form action="{{ route('admin.clients.block', $client->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $client->account_status === 'active' ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                    <i class="fa-solid {{ $client->account_status === 'active' ? 'fa-ban' : 'fa-check' }}"></i>
                    <span>{{ $client->account_status === 'active' ? 'تعطيل / حظر الحساب' : 'إلغاء الحظر وتفعيل الحساب' }}</span>
                </button>
            </form>

            <!-- Edit Client Modal Trigger -->
            <button type="button" @click="editModalOpen = true" class="px-4 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>تعديل البيانات</span>
            </button>

            <!-- Delete Client Trigger -->
            <button type="button" @click="deleteModalOpen = true" class="px-4 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-trash-can"></i>
                <span>حذف العميل</span>
            </button>
        </div>
    </div>

    <!-- Client Profile Overview Card -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-gray-100">
            <!-- Client Info Left -->
            <div class="flex items-start sm:items-center gap-4">
                @if($client->profile_image)
                    <img src="{{ str_starts_with($client->profile_image, 'http') ? $client->profile_image : url($client->profile_image) }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-primary-200 shadow-md flex-shrink-0">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-100 text-primary-600 font-black text-3xl flex items-center justify-center border border-primary-200 shadow-sm flex-shrink-0">
                        {{ mb_substr($client->full_name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
                        <h3 class="text-xl font-bold text-gray-900">{{ $client->full_name }}</h3>
                        
                        @if($client->account_status === 'active')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>حساب نشط</span>
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                <span>موقوف / محظور</span>
                            </span>
                        @endif

                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                            عميل (Client)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 text-xs text-gray-600 mt-2">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-phone text-emerald-600 w-4 text-center"></i>
                            <span class="text-gray-400">الجوال:</span>
                            <a href="tel:{{ $client->phone }}" class="font-mono font-bold text-gray-800 hover:text-emerald-600" dir="ltr">{{ $client->phone }}</a>
                            <button onclick="navigator.clipboard.writeText('{{ $client->phone }}'); alert('تم نسخ رقم الهاتف: {{ $client->phone }}');" class="text-gray-400 hover:text-emerald-600" title="نسخ الرقم">
                                <i class="fa-regular fa-copy text-[11px]"></i>
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-blue-500 w-4 text-center"></i>
                            <span class="text-gray-400">البريد:</span>
                            @if($client->email)
                                <a href="mailto:{{ $client->email }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $client->email }}</a>
                            @else
                                <span class="text-gray-400">غير مسجل</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-rose-500 w-4 text-center"></i>
                            <span class="text-gray-400">العناوين المسجلة:</span>
                            <span class="font-bold text-gray-800">{{ $client->addresses->count() }} عنوان</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-amber-500 w-4 text-center"></i>
                            <span class="text-gray-400">آخر تحديث:</span>
                            <span class="text-gray-700 font-mono">{{ $client->updated_at ? $client->updated_at->format('Y-m-d H:i') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Financial Highlights -->
            <div class="flex flex-wrap items-center gap-4 bg-gray-50/80 p-4 rounded-2xl border border-gray-100">
                <div class="text-center px-4">
                    <span class="text-xs text-gray-400 block mb-1">رصيد المحفظة</span>
                    <span class="text-xl font-mono font-black text-emerald-700">
                        {{ number_format($client->wallet_balance, 2) }}
                        <span class="text-xs font-normal text-emerald-600">ر.ي</span>
                    </span>
                </div>

                <div class="h-8 w-[1px] bg-gray-200 hidden sm:block"></div>

                <div class="text-center px-4">
                    <span class="text-xs text-gray-400 block mb-1">إجمالي الإنفاق</span>
                    <span class="text-xl font-mono font-black text-primary-700">
                        {{ number_format($totalSpent, 2) }}
                        <span class="text-xs font-normal text-primary-600">ر.ي</span>
                    </span>
                </div>

                <div class="h-8 w-[1px] bg-gray-200 hidden sm:block"></div>

                <div class="text-center px-4">
                    <span class="text-xs text-gray-400 block mb-1">إجمالي الطلبات</span>
                    <span class="text-xl font-bold text-gray-900">
                        {{ $totalOrders }}
                        <span class="text-xs font-normal text-gray-500">طلب</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- 4 Quick Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-blue-50/60 rounded-xl p-4 border border-blue-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-blue-600 block">إجمالي الطلبات</span>
                    <span class="text-2xl font-black text-blue-900 mt-1 block">{{ $totalOrders }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>

            <div class="bg-emerald-50/60 rounded-xl p-4 border border-emerald-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-600 block">الطلبات المكتملة</span>
                    <span class="text-2xl font-black text-emerald-900 mt-1 block">{{ $completedOrders }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>

            <div class="bg-amber-50/60 rounded-xl p-4 border border-amber-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-amber-600 block">طلبات قيد المتابعة</span>
                    <span class="text-2xl font-black text-amber-900 mt-1 block">{{ $inProgressOrders }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-spinner animate-spin"></i>
                </div>
            </div>

            <div class="bg-red-50/60 rounded-xl p-4 border border-red-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-red-600 block">الطلبات الملغاة</span>
                    <span class="text-2xl font-black text-red-900 mt-1 block">{{ $cancelledOrders }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-ban"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" id="orders-section">
        <div class="flex border-b border-gray-200 bg-gray-50/70 overflow-x-auto text-xs font-bold">
            <button type="button" @click="activeTab = 'orders'; window.location.hash = 'orders'" :class="activeTab === 'orders' ? 'border-primary-600 text-primary-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-wrench"></i>
                <span>طلبات الصيانة والخدمات</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'orders' ? 'bg-primary-100 text-primary-800' : 'bg-gray-200 text-gray-700'">{{ $client->serviceRequests->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'addresses'; window.location.hash = 'addresses'" :class="activeTab === 'addresses' ? 'border-primary-600 text-primary-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-location-dot"></i>
                <span>العناوين والمواقع المحفوظة</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'addresses' ? 'bg-primary-100 text-primary-800' : 'bg-gray-200 text-gray-700'">{{ $client->addresses->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'transactions'; window.location.hash = 'transactions'" :class="activeTab === 'transactions' ? 'border-primary-600 text-primary-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-wallet"></i>
                <span>سجل المحفظة والعمليات المالية</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'transactions' ? 'bg-primary-100 text-primary-800' : 'bg-gray-200 text-gray-700'">{{ $client->transactions->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'reviews'; window.location.hash = 'reviews'" :class="activeTab === 'reviews' ? 'border-primary-600 text-primary-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-star"></i>
                <span>التقييمات المقدمة</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'reviews' ? 'bg-primary-100 text-primary-800' : 'bg-gray-200 text-gray-700'">{{ $client->reviewsGiven->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'tickets'; window.location.hash = 'tickets'" :class="activeTab === 'tickets' ? 'border-primary-600 text-primary-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-headset"></i>
                <span>تذاكر الدعم والشكاوى</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'tickets' ? 'bg-primary-100 text-primary-800' : 'bg-gray-200 text-gray-700'">{{ $client->supportTickets->count() }}</span>
            </button>
        </div>

        <div class="p-6">
            <!-- Tab 1: Service Requests & Orders -->
            <div x-show="activeTab === 'orders'" class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-primary-600"></i>
                        <span>كافة طلبات الصيانة والخدمات للعميل ({{ $client->serviceRequests->count() }})</span>
                    </h4>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs uppercase">
                            <tr>
                                <th class="p-3">رقم الطلب</th>
                                <th class="p-3">الخدمة والقسم</th>
                                <th class="p-3">الحرفي المنفذ</th>
                                <th class="p-3">تاريخ الطلب</th>
                                <th class="p-3">المبلغ وطريقة الدفع</th>
                                <th class="p-3">الحالة</th>
                                <th class="p-3 text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($client->serviceRequests as $req)
                                <tr class="hover:bg-gray-50/70 transition">
                                    <!-- Request Number -->
                                    <td class="p-3">
                                        <a href="{{ route('admin.orders.show', $req->id) }}" class="font-mono font-bold text-xs text-emerald-700 hover:underline flex items-center gap-1">
                                            <span>{{ $req->clean_number ? '#' . $req->clean_number : ('#' . $req->id) }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-400"></i>
                                        </a>
                                        @if($req->is_urgent)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 mt-1 inline-block">عاجل طارئ</span>
                                        @endif
                                    </td>

                                    <!-- Service & Category -->
                                    <td class="p-3">
                                        <div class="flex items-center gap-2">
                                            @if($req->service && $req->service->image)
                                                <img src="{{ asset($req->service->image) }}" class="w-7 h-7 rounded-lg object-cover border border-gray-200">
                                            @else
                                                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                                    <i class="fa-solid fa-wrench"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-bold text-xs text-gray-900">{{ $req->service->name ?? 'خدمة عامة' }}</div>
                                                <div class="text-[11px] text-gray-400">{{ $req->service->category->name ?? 'صيانة' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Assigned Craftsman -->
                                    <td class="p-3 text-xs">
                                        @php
                                            $assignedCraftsman = $req->currentAssignment?->craftsman ?? ($req->craftsman_id ? \App\Models\User::find($req->craftsman_id) : null);
                                        @endphp
                                        @if($assignedCraftsman)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px]">
                                                    {{ mb_substr($assignedCraftsman->full_name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('admin.craftsmen.show', $assignedCraftsman->id) }}" class="font-bold text-gray-800 hover:text-emerald-600 hover:underline block">
                                                        {{ $assignedCraftsman->full_name }}
                                                    </a>
                                                    <span class="text-[11px] text-gray-400 font-mono">{{ $assignedCraftsman->phone }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="px-2 py-1 rounded-lg text-[11px] bg-gray-100 text-gray-500 font-semibold inline-block">
                                                قيد انتظار الإسناد
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Date -->
                                    <td class="p-3 text-xs text-gray-500 font-mono">
                                        <div>{{ $req->created_at->format('Y-m-d H:i') }}</div>
                                        @if($req->preferred_date)
                                            <div class="text-[11px] text-emerald-600 mt-0.5">
                                                <i class="fa-regular fa-clock ml-1"></i>الموعد: {{ $req->preferred_date->format('Y-m-d') }} {{ $req->preferred_time ?? '' }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Amount & Payment Method -->
                                    <td class="p-3 text-xs">
                                        <div class="font-mono font-bold text-gray-900">
                                            {{ number_format($req->total_amount, 2) }} ر.ي
                                        </div>
                                        <div class="text-[11px] text-gray-500 flex items-center gap-1 mt-0.5">
                                            <span class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-600">{{ $req->payment_method_name }}</span>
                                            @if($req->payment_status === 'paid')
                                                <span class="text-emerald-600 font-bold" title="تم السداد"><i class="fa-solid fa-check-circle"></i></span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="p-3 text-xs">
                                        @php
                                            $code = $req->status->code ?? 'pending';
                                            $statusColors = [
                                                'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                'on_the_way' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                                'arrived' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                                'accepted' => 'bg-sky-50 text-sky-700 border-sky-200',
                                                'assigned' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                                'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            ];
                                            $badgeClass = $statusColors[$code] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badgeClass }} inline-block">
                                            {{ $req->status->name ?? 'قيد المعالجة' }}
                                        </span>
                                    </td>

                                    <!-- Action -->
                                    <td class="p-3 text-center">
                                        <a href="{{ route('admin.orders.show', $req->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold transition">
                                            <span>تفاصيل الطلب</span>
                                            <i class="fa-solid fa-angle-left text-[10px]"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-solid fa-clipboard-list text-4xl text-gray-300 mb-2"></i>
                                            <span class="font-bold text-sm text-gray-600">لا توجد طلبات صيانة لهذا العميل حتى الآن</span>
                                            <span class="text-xs text-gray-400 mt-1">عند قيام العميل بحجز خدمات أو طلب صيانات ستظهر هنا بكافة تفاصيلها</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Saved Addresses -->
            <div x-show="activeTab === 'addresses'" class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-rose-500"></i>
                        <span>عناوين ومواقع العميل المحفوظة ({{ $client->addresses->count() }})</span>
                    </h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($client->addresses as $address)
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200 hover:border-gray-300 transition space-y-3 relative">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl bg-white border border-gray-200 text-rose-500 flex items-center justify-center text-sm shadow-sm">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </span>
                                    <span class="font-bold text-sm text-gray-900">{{ $address->label ?? 'عنوان محفوظ' }}</span>
                                </div>
                                @if($address->is_default)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        العنوان الافتراضي
                                    </span>
                                @endif
                            </div>

                            <div class="text-xs text-gray-600 space-y-1.5 pt-2 border-t border-gray-200/60">
                                <div>
                                    <span class="text-gray-400">المنطقة:</span>
                                    <span class="font-semibold text-gray-800">
                                        {{ $address->district->governorate->name ?? 'صنعاء' }} - {{ $address->district->name ?? 'الأمانة' }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-gray-400">تفاصيل العنوان:</span>
                                    <span class="text-gray-800 font-medium block mt-0.5">{{ $address->address_details ?: 'لا توجد تفاصيل إضافية مكتوبة' }}</span>
                                </div>

                                @if($address->latitude && $address->longitude)
                                    <div class="pt-2">
                                        <a href="https://www.google.com/maps?q={{ $address->latitude }},{{ $address->longitude }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-bold hover:underline">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                            <span>فتح الموقع على خرائط Google</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-gray-400">
                            <i class="fa-solid fa-map-pin text-4xl text-gray-300 mb-2"></i>
                            <p class="font-bold text-sm text-gray-600">لا توجد عناوين محفوظة لهذا العميل بعد</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Tab 3: Transactions & Wallet Ledger -->
            <div x-show="activeTab === 'transactions'" class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-emerald-600"></i>
                        <span>سجل المعاملات والمدفوعات والمحفظة ({{ $client->transactions->count() }})</span>
                    </h4>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs uppercase">
                            <tr>
                                <th class="p-3">رقم المعاملة</th>
                                <th class="p-3">الطلب المرتبط</th>
                                <th class="p-3">طريقة الدفع</th>
                                <th class="p-3">المبلغ</th>
                                <th class="p-3">الحالة</th>
                                <th class="p-3">التاريخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($client->transactions as $tx)
                                <tr class="hover:bg-gray-50/70 transition">
                                    <td class="p-3 font-mono font-bold text-xs text-gray-800">
                                        {{ $tx->transaction_number ?? ('#' . $tx->id) }}
                                    </td>
                                    <td class="p-3 text-xs">
                                        @if($tx->request)
                                            <a href="{{ route('admin.orders.show', $tx->request_id) }}" class="font-mono text-emerald-600 font-bold hover:underline">
                                                #{{ $tx->request->clean_number }} ({{ $tx->request->service->name ?? 'خدمة' }})
                                            </a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-xs">
                                        <span class="font-medium text-gray-700">{{ $tx->paymentMethod->name ?? 'محفظة / نقدي' }}</span>
                                    </td>
                                    <td class="p-3 text-xs font-mono font-bold text-emerald-700">
                                        {{ number_format($tx->amount, 2) }} ر.ي
                                    </td>
                                    <td class="p-3 text-xs">
                                        @if($tx->payment_status === 'paid')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">مدفوع</span>
                                        @elseif($tx->payment_status === 'pending')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">معلق</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-50 text-gray-700 border border-gray-200">{{ $tx->payment_status }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-xs text-gray-400 font-mono">
                                        {{ $tx->created_at->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-12 text-center text-gray-400">
                                        <i class="fa-solid fa-file-invoice-dollar text-4xl text-gray-300 mb-2"></i>
                                        <p class="font-bold text-sm text-gray-600">لا توجد حركات مالية مسجلة لهذا العميل حتى الآن</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 4: Reviews Given by Client -->
            <div x-show="activeTab === 'reviews'" class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-star text-amber-500"></i>
                        <span>التقييمات والمراجعات التي قدمها العميل للحرفيين ({{ $client->reviewsGiven->count() }})</span>
                    </h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($client->reviewsGiven as $review)
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400">الحرفي المقيم:</span>
                                    <span class="font-bold text-sm text-gray-900">{{ $review->craftsman->full_name ?? 'حرفي' }}</span>
                                </div>
                                <div class="flex items-center text-amber-400 text-xs">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                    <span class="mr-1 font-mono font-bold text-gray-700">({{ $review->rating }})</span>
                                </div>
                            </div>

                            @if($review->comment)
                                <div class="bg-white p-3 rounded-xl border border-gray-200/70 text-xs text-gray-700 leading-relaxed">
                                    "{{ $review->comment }}"
                                </div>
                            @endif

                            <div class="flex items-center justify-between text-[11px] text-gray-400 pt-1">
                                <span>{{ $review->created_at->format('Y-m-d H:i') }}</span>
                                @if($review->request_id)
                                    <a href="{{ route('admin.orders.show', $review->request_id) }}" class="text-blue-600 hover:underline">
                                        عرض الطلب #{{ $review->request_id }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-gray-400">
                            <i class="fa-solid fa-comment-dots text-4xl text-gray-300 mb-2"></i>
                            <p class="font-bold text-sm text-gray-600">لم يقدم العميل أي تقييمات حتى الآن</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Tab 5: Support Tickets -->
            <div x-show="activeTab === 'tickets'" class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-headset text-primary-600"></i>
                        <span>تذاكر الدعم والشكاوى للعميل ({{ $client->supportTickets->count() }})</span>
                    </h4>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs uppercase">
                            <tr>
                                <th class="p-3">رقم التذكرة</th>
                                <th class="p-3">الموضوع</th>
                                <th class="p-3">الأولوية</th>
                                <th class="p-3">الحالة</th>
                                <th class="p-3">تاريخ الفتح</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($client->supportTickets as $ticket)
                                <tr class="hover:bg-gray-50/70 transition">
                                    <td class="p-3 font-mono font-bold text-xs text-gray-800">
                                        #{{ $ticket->id }}
                                    </td>
                                    <td class="p-3 text-xs font-semibold text-gray-900">
                                        {{ $ticket->subject ?? 'تذكرة دعم فني' }}
                                    </td>
                                    <td class="p-3 text-xs">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700">
                                            {{ $ticket->priority ?? 'عادي' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-xs">
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $ticket->status ?? 'مفتوحة' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-xs text-gray-400 font-mono">
                                        {{ $ticket->created_at->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-12 text-center text-gray-400">
                                        <i class="fa-solid fa-circle-check text-4xl text-emerald-400 mb-2"></i>
                                        <p class="font-bold text-sm text-gray-600">لا توجد أي بلاغات أو تذاكر دعم فني مفتوحة لهذا العميل</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Client Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="editModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                    <span>تعديل بيانات العميل</span>
                </h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.clients.update', $client->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">الاسم الكامل *</label>
                    <input type="text" name="full_name" x-model="editData.full_name" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">تحديث الصورة الشخصية</label>
                    <input type="file" name="profile_image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs bg-gray-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:bg-blue-50 file:text-blue-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">رقم الجوال *</label>
                    <input type="text" name="phone" x-model="editData.phone" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none" dir="ltr">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" x-model="editData.email" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none" dir="ltr">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">حالة الحساب</label>
                    <select name="account_status" x-model="editData.account_status" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="active">نشط (Active)</option>
                        <option value="suspended">موقوف / محظور (Suspended)</option>
                        <option value="inactive">غير مفعل (Inactive)</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20 transition">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="deleteModalOpen = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4 text-center">
            <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto text-2xl shadow-inner">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            
            <h3 class="font-bold text-gray-900 text-base">تأكيد حذف العميل نهائياً</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                هل أنت متأكد من حذف العميل <strong class="text-gray-800">{{ $client->full_name }}</strong>؟ هذا الإجراء سيقوم بحذف حسابه وسجلاته ولا يمكن التراجع عنه.
            </p>

            <form action="{{ route('admin.clients.destroy', $client->id) }}" method="POST" class="flex items-center justify-center gap-3 pt-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 transition w-full">إلغاء</button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-md shadow-red-600/20 transition w-full">تأكيد الحذف</button>
            </form>
        </div>
    </div>

</div>
@endsection
