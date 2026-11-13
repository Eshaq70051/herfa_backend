@extends('admin.layout')

@section('title', 'إدارة الطلبات والعمليات')
@section('page_title', 'سجل طلبات الصيانة والخدمات (Service Requests)')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false, deleteModalOpen: false, assignModalOpen: false, assignUrl: '', orderNumToAssign: '', currentCraftsmanId: '', deleteUrl: '', orderNumToDelete: '' }">

    <!-- Top Action & Search Bar -->
    <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0">
            <a href="{{ route('admin.orders.index', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request('status', 'all') === 'all' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                الكل ({{ \App\Models\ServiceRequest::count() }})
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request('status') === 'pending' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                بانتظار فني
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'in_progress']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request('status') === 'in_progress' ? 'bg-teal-600 text-white' : 'bg-teal-50 text-teal-700 hover:bg-teal-100' }}">
                جاري التنفيذ
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request('status') === 'completed' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                مكتمل
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request('status') === 'cancelled' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                ملغي
            </a>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <!-- Search input -->
            <form action="{{ route('admin.orders.index') }}" method="GET" class="w-full md:w-72 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث برقم الطلب، العميل..." class="w-full pr-10 pl-4 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 outline-none transition">
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
            </form>

            <!-- Add Order Button -->
            <button @click="createModalOpen = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-plus"></i>
                <span>إنشاء طلب جديد</span>
            </button>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs font-bold border-b border-gray-200 uppercase">
                        <th class="py-3 px-4">رقم الطلب والتاريخ</th>
                        <th class="py-3 px-4">الخدمة والقسم</th>
                        <th class="py-3 px-4">العميل والموقع</th>
                        <th class="py-3 px-4">الحرفي المسند</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4">الرسوم الأساسية</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="py-4 px-4">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="font-bold text-emerald-700 hover:underline font-mono">
                                {{ $order->request_number }}
                            </a>
                            <div class="text-[11px] text-gray-400 font-mono mt-0.5">
                                {{ $order->created_at->format('Y-m-d H:i') }}
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <span class="font-bold text-gray-900 block">{{ $order->service->name ?? 'خدمة عامة' }}</span>
                            <span class="text-xs text-gray-400">{{ $order->service->category->name ?? 'صيانة' }}</span>
                        </td>
                        <td class="py-4 px-4 text-xs">
                            <div class="font-bold text-gray-800">{{ $order->customer->full_name ?? 'عميل غير مسجل' }}</div>
                            <div class="text-gray-400 font-mono">{{ $order->customer->phone ?? '' }}</div>
                            <div class="text-[11px] text-gray-500 truncate max-w-xs">{{ $order->address_details ?? 'حي النرجس' }}</div>
                        </td>
                        <td class="py-4 px-4 text-xs">
                            @php
                                $assigned = $order->assignments->first();
                                $craftsmanUser = $assigned?->craftsman ?? ($order->craftsman_id ? \App\Models\User::find($order->craftsman_id) : null);
                            @endphp
                            @if($craftsmanUser)
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <div class="font-bold text-emerald-700">{{ $craftsmanUser->full_name }}</div>
                                        <div class="text-gray-400 font-mono">{{ $craftsmanUser->phone }}</div>
                                    </div>
                                    <button type="button" @click="assignModalOpen = true; assignUrl = '{{ route('admin.orders.assign', $order->id) }}'; orderNumToAssign = '{{ $order->request_number }}'; currentCraftsmanId = '{{ $craftsmanUser->id }}'" class="p-1 hover:bg-emerald-50 text-emerald-600 rounded transition" title="تغيير الحرفي">
                                        <i class="fa-solid fa-user-pen text-xs"></i>
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[11px] bg-gray-100 text-gray-500 font-semibold">لم يُسند بعد</span>
                                    <button type="button" @click="assignModalOpen = true; assignUrl = '{{ route('admin.orders.assign', $order->id) }}'; orderNumToAssign = '{{ $order->request_number }}'; currentCraftsmanId = ''" class="px-2 py-0.5 rounded text-[11px] bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold border border-emerald-200 transition flex items-center gap-1 shadow-sm">
                                        <i class="fa-solid fa-user-plus text-[10px]"></i>
                                        <span>إسناد الآن</span>
                                    </button>
                                </div>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            @php
                                $stName = $order->status->name ?? 'pending';
                            @endphp
                            @if($stName === 'completed')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-emerald-50 text-emerald-700 border-emerald-200 inline-block">مكتمل</span>
                            @elseif($stName === 'in_progress')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-teal-50 text-teal-700 border-teal-200 inline-block">قيد التنفيذ</span>
                            @elseif($stName === 'cancelled')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-red-50 text-red-700 border-red-200 inline-block">ملغي</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-amber-50 text-amber-700 border-amber-200 inline-block">بانتظار فني</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 font-mono font-bold text-gray-900">
                            {{ number_format($order->service->base_price ?? 80, 2) }} ر.س
                        </td>
                        <td class="py-4 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="عرض التفاصيل">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>

                                <button type="button" @click="assignModalOpen = true; assignUrl = '{{ route('admin.orders.assign', $order->id) }}'; orderNumToAssign = '{{ $order->request_number }}'; currentCraftsmanId = '{{ $craftsmanUser->id ?? '' }}'" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="إسناد / تغيير الحرفي">
                                    <i class="fa-solid fa-user-gear text-xs"></i>
                                </button>

                                <button type="button" @click="deleteModalOpen = true; deleteUrl = '{{ route('admin.orders.destroy', $order->id) }}'; orderNumToDelete = '{{ $order->request_number }}'" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف الطلب">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-400">
                            <i class="fa-solid fa-inbox text-4xl text-gray-300 mb-2"></i>
                            <p>لا توجد طلبات مسجلة حالياً</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    <!-- Create Order Modal -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="createModalOpen = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-emerald-600"></i>
                    <span>إنشاء طلب صيانة جديد</span>
                </h3>
                <button @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.orders.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">العميل صاحب الطلب *</label>
                    <select name="customer_id" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">اختر العميل...</option>
                        @foreach($customers as $client)
                            <option value="{{ $client->id }}">{{ $client->full_name }} ({{ $client->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">الخدمة المطلوبة *</label>
                    <select name="service_id" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">اختر الخدمة...</option>
                        @foreach($categories as $cat)
                            <optgroup label="{{ $cat->name }}">
                                @foreach($cat->services as $svc)
                                    <option value="{{ $svc->id }}">{{ $svc->name }} ({{ number_format($svc->base_price, 2) }} ر.س)</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">إسناد لحرفي مباشرة (يشترط أن تكون حالته مفتوحة ومتاحة) *</label>
                    <select name="craftsman_id" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">-- ترك الطلب عاماً ليقبله أول حرفي متاح --</option>
                        @foreach($craftsmen as $tech)
                            @php
                                $isOpen = $tech->account_status === 'active'
                                    && ($tech->craftsmanProfile->approval_status ?? '') === 'approved'
                                    && (($tech->craftsmanProfile->availability_status ?? false) || $tech->is_online);
                            @endphp
                            <option value="{{ $tech->id }}" {{ !$isOpen ? 'disabled class=text-gray-400 bg-gray-100' : 'class=font-bold text-emerald-800' }}>
                                {{ $tech->full_name }} ({{ $tech->phone }}) - {{ $isOpen ? '🟢 متاح ومفتوح لاستقبال الطلبات' : '🔴 مغلق الحالة / غير متاح' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">تنبيه: الحرفيون مغلقو الحالة لا يمكن إسناد الطلبات لهم حتى يقوموا بفتح الحالة في التطبيق.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">عنوان وتفاصيل الموقع</label>
                    <input type="text" name="address_details" placeholder="مثال: الرياض - حي النرجس - فيلا 12" value="الرياض - حي النرجس" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">وصف العطل أو متطلبات العمل *</label>
                    <textarea name="description" rows="3" required placeholder="اشرح تفاصيل المشكلة أو القطع المراد تركيبها..." class="w-full p-2.5 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20">إنشاء وتأكيد الطلب</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="deleteModalOpen = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto text-xl">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-900 text-base">تأكيد حذف الطلب</h4>
                <p class="text-xs text-gray-500 mt-1">
                    هل أنت متأكد من رغبتك في حذف الطلب رقم (<span class="font-mono font-bold text-red-600" x-text="orderNumToDelete"></span>)؟ لا يمكن التراجع عن هذا الإجراء.
                </p>
            </div>
            <form :action="deleteUrl" method="POST" class="flex items-center justify-center gap-3 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 text-xs font-bold">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-md shadow-red-600/20">تأكيد الحذف</button>
            </form>
        </div>
    </div>

    <!-- Quick Assign Craftsman Modal -->
    <div x-show="assignModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="assignModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-gear text-emerald-600"></i>
                    <span>إسناد الطلب للحرفي</span>
                </h3>
                <button @click="assignModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <p class="text-xs text-gray-500">
                إسناد الطلب رقم (<span class="font-mono font-bold text-emerald-700" x-text="orderNumToAssign"></span>) إلى حرفي معتمد لتنفيذه مباشرة:
            </p>

            <form :action="assignUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">اختر الحرفي / الفني *</label>
                    <select name="craftsman_id" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none" x-model="currentCraftsmanId">
                        <option value="">-- حدد الحرفي المكلف بالطلب --</option>
                        @foreach($craftsmen as $tech)
                            @php
                                $isAvailable = ($tech->is_online ?? false) || ($tech->craftsmanProfile->availability_status ?? false);
                            @endphp
                            <option value="{{ $tech->id }}">
                                {{ $tech->full_name }} ({{ $tech->phone }}) 
                                - {{ $isAvailable ? '🟢 متاح الآن' : '⚪ غير متاح' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-800 flex items-start gap-2">
                    <i class="fa-solid fa-circle-info mt-0.5 text-amber-600"></i>
                    <span>عند إسناد الطلب للحرفي، سيتحول الطلب تلقائياً إلى حالة "مقبول/مسند" ويظهر مباشرة في تطبيق الحرفي للبدء في التنفيذ.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="assignModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>تأكيد الإسناد للحرفي</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
