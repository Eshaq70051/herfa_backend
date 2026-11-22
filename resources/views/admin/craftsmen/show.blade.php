@extends('admin.layout')

@section('title', 'الملف المهني: ' . $craftsman->full_name)
@section('page_title', 'الملف المهني والوثائق: ' . $craftsman->full_name)

@section('content')
<div class="space-y-6" x-data="{
    activeTab: window.location.hash ? window.location.hash.substring(1) : 'orders'
}">

    <!-- Top Action Nav -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-gray-200 shadow-sm">
        <a href="{{ route('admin.craftsmen.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-emerald-600 transition">
            <i class="fa-solid fa-arrow-right"></i>
            <span>العودة لقائمة الحرفيين</span>
        </a>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-print text-primary-600"></i>
                <span>طباعة التقرير المهني</span>
            </button>

            <form action="{{ route('admin.craftsmen.block', $craftsman->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $craftsman->account_status === 'active' ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                    <i class="fa-solid fa-ban"></i>
                    <span>{{ $craftsman->account_status === 'active' ? 'حظر الحساب' : 'إلغاء الحظر والتفعيل' }}</span>
                </button>
            </form>

            <form action="{{ route('admin.craftsmen.destroy', $craftsman->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف الحرفي نهائياً؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-bold transition flex items-center gap-2">
                    <i class="fa-solid fa-trash"></i>
                    <span>حذف الحرفي</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Craftsman Info Card -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-gray-100">
            <div class="flex items-start sm:items-center gap-4">
                @if($craftsman->profile_image)
                    <img src="{{ str_starts_with($craftsman->profile_image, 'http') ? $craftsman->profile_image : url($craftsman->profile_image) }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-emerald-200 shadow-md flex-shrink-0">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-100 text-emerald-700 font-extrabold text-3xl flex items-center justify-center border border-emerald-200 shadow-sm flex-shrink-0">
                        {{ mb_substr($craftsman->full_name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
                        <h3 class="text-xl font-bold text-gray-900">{{ $craftsman->full_name }}</h3>
                        
                        @if($craftsman->account_status === 'active')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">نشط</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">محظور</span>
                        @endif

                        @php
                            $isOpen = ($craftsman->craftsmanProfile->availability_status ?? false) || $craftsman->is_online;
                        @endphp
                        @if($isOpen)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> متاح ومفتوح
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span> مغلق الحالة
                            </span>
                        @endif

                        @if(($craftsman->craftsmanProfile->approval_status ?? '') === 'approved')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-blue-600 text-[11px]"></i> موثق رسمياً
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-gray-600 mt-2">
                        <div><i class="fa-solid fa-phone text-emerald-600 ml-1"></i> <span class="font-mono font-bold" dir="ltr">{{ $craftsman->phone }}</span></div>
                        @if(!empty($craftsman->craftsmanProfile->alternative_phone))
                            <div><i class="fa-solid fa-phone-flip text-gray-400 ml-1"></i> بديل: <span class="font-mono" dir="ltr">{{ $craftsman->craftsmanProfile->alternative_phone }}</span></div>
                        @endif
                        @if(!empty($craftsman->craftsmanProfile->age))
                            <div><i class="fa-solid fa-user-clock text-gray-400 ml-1"></i> العمر: <span class="font-bold text-gray-800">{{ $craftsman->craftsmanProfile->age }} سنة</span></div>
                        @endif
                        <div><i class="fa-solid fa-briefcase text-gray-400 ml-1"></i> الخبرة: <span class="font-bold text-gray-800">{{ $craftsman->craftsmanProfile->experience_years ?? 1 }} سنوات</span></div>
                        <div><i class="fa-solid fa-wrench text-emerald-600 ml-1"></i> {{ $craftsman->craftsmanProfile->custom_specialty ?? $craftsman->craftsmanProfile->profession ?? 'صيانة عامة' }}</div>
                        @if(!empty($craftsman->craftsmanProfile->national_id_number))
                            <div><i class="fa-solid fa-id-card text-gray-400 ml-1"></i> بطاقة: <span class="font-mono text-gray-800 font-bold">{{ $craftsman->craftsmanProfile->national_id_number }}</span></div>
                        @endif
                        <div><i class="fa-solid fa-location-dot text-rose-500 ml-1"></i> {{ $craftsman->craftsmanProfile->address ?? $craftsman->craftsmanProfile->city ?? 'صنعاء' }}</div>
                        @if(!empty($craftsman->craftsmanProfile->latitude) && !empty($craftsman->craftsmanProfile->longitude))
                            <a href="https://www.google.com/maps?q={{ $craftsman->craftsmanProfile->latitude }},{{ $craftsman->craftsmanProfile->longitude }}" target="_blank" class="text-emerald-600 hover:underline flex items-center gap-1 font-bold">
                                <i class="fa-solid fa-map-pin"></i> الموقع بالخريطة
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats Highlights -->
            <div class="flex items-center gap-6 bg-gray-50/80 p-4 rounded-2xl border border-gray-100">
                <div class="text-center px-2">
                    <span class="text-xs text-gray-400 block mb-1">التقييم العام</span>
                    <span class="text-xl font-black text-amber-500 flex items-center justify-center gap-1">
                        <i class="fa-solid fa-star text-sm"></i> {{ number_format((float)($craftsman->craftsmanProfile->rating_avg ?? 5.0), 1) }}
                    </span>
                    <span class="text-[10px] text-gray-400 block">({{ $craftsman->reviewsReceived->count() }} تقييم)</span>
                </div>
                <div class="h-8 w-[1px] bg-gray-200"></div>
                <div class="text-center px-2">
                    <span class="text-xs text-gray-400 block mb-1">رصيد المحفظة</span>
                    <span class="text-xl font-mono font-black text-emerald-700">{{ number_format($craftsman->wallet_balance, 2) }}</span>
                    <span class="text-[10px] text-emerald-600 font-bold block">ر.ي</span>
                </div>
                <div class="h-8 w-[1px] bg-gray-200"></div>
                <div class="text-center px-2">
                    <span class="text-xs text-gray-400 block mb-1">نسبة العمولة</span>
                    <span class="text-xl font-black text-gray-800">{{ $craftsman->craftsmanProfile->commission_rate ?? 15 }}%</span>
                    <span class="text-[10px] text-gray-400 block">للمنظومة</span>
                </div>
            </div>
        </div>

        @if(!empty($craftsman->craftsmanProfile->bio))
            <div class="mt-4 p-3 bg-slate-50 rounded-xl text-xs text-slate-700 leading-relaxed border border-slate-200/60">
                <strong class="font-bold text-slate-900 block mb-1">نبذة عن الحرفي والخبرات:</strong>
                {{ $craftsman->craftsmanProfile->bio }}
            </div>
        @endif

        <!-- Verification & Availability Update Form -->
        <div class="mt-6 pt-4 border-t border-gray-100">
            <h4 class="font-bold text-gray-800 text-xs mb-3">إدارة حالة الاعتماد وتوثيق الهوية ونسبة العمولة:</h4>
            <form action="{{ route('admin.craftsmen.verify', $craftsman->id) }}" method="POST" class="flex flex-col sm:flex-row items-center gap-4">
                @csrf
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <select name="status" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="approved" {{ ($craftsman->craftsmanProfile->approval_status ?? '') === 'approved' ? 'selected' : '' }}>معتمد وموثق رسمياً (Approved)</option>
                        <option value="pending" {{ ($craftsman->craftsmanProfile->approval_status ?? '') === 'pending' ? 'selected' : '' }}>بانتظار المراجعة (Pending)</option>
                        <option value="rejected" {{ ($craftsman->craftsmanProfile->approval_status ?? '') === 'rejected' ? 'selected' : '' }}>مرفوض (Rejected)</option>
                    </select>

                    <input type="number" step="0.5" name="commission_rate" value="{{ $craftsman->craftsmanProfile->commission_rate ?? 15 }}" placeholder="نسبة العمولة %" class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none w-32">

                    <label class="flex items-center gap-2 cursor-pointer bg-gray-50 px-3 py-2 rounded-xl border border-gray-200">
                        <input type="checkbox" name="availability_status" value="1" {{ ($craftsman->craftsmanProfile->availability_status ?? false) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs font-bold text-gray-700">متاح لاستقبال الطلبات الآن</span>
                    </label>
                </div>

                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition">
                    تحديث الحالة والاعتماد
                </button>
            </form>
        </div>
    </div>

    <!-- Navigation Tabs for Detailed Craftsman Information -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex border-b border-gray-200 bg-gray-50 overflow-x-auto text-xs font-bold">
            <button type="button" @click="activeTab = 'orders'; window.location.hash = 'orders'" :class="activeTab === 'orders' ? 'border-emerald-600 text-emerald-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>الطلبات والمهام المسندة</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'orders' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700'">{{ $craftsman->assignedRequests->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'services'; window.location.hash = 'services'" :class="activeTab === 'services' ? 'border-emerald-600 text-emerald-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-wrench"></i>
                <span>الخدمات والتخصصات</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'services' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700'">{{ $craftsman->craftsmanServices->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'portfolio'; window.location.hash = 'portfolio'" :class="activeTab === 'portfolio' ? 'border-emerald-600 text-emerald-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-images"></i>
                <span>معرض الأعمال والمشاريع</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'portfolio' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700'">{{ $craftsman->portfolio->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'documents'; window.location.hash = 'documents'" :class="activeTab === 'documents' ? 'border-emerald-600 text-emerald-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-id-card"></i>
                <span>وثائق الهوية والسيرة الذاتية (CV)</span>
            </button>

            <button type="button" @click="activeTab = 'certificates'; window.location.hash = 'certificates'" :class="activeTab === 'certificates' ? 'border-emerald-600 text-emerald-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-certificate"></i>
                <span>الشهادات والتراخيص</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'certificates' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700'">{{ $craftsman->certificates->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'reviews'; window.location.hash = 'reviews'" :class="activeTab === 'reviews' ? 'border-emerald-600 text-emerald-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-star"></i>
                <span>تقييمات العملاء</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'reviews' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700'">{{ $craftsman->reviewsReceived->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'financial'; window.location.hash = 'financial'" :class="activeTab === 'financial' ? 'border-emerald-600 text-emerald-700 bg-white border-b-2 shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-wallet"></i>
                <span>المحفظة والبيانات البنكية</span>
            </button>
        </div>

        <div class="p-6">
            <!-- TAB 1: Assigned Orders -->
            <div x-show="activeTab === 'orders'" class="space-y-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase">
                            <tr>
                                <th class="p-3">رقم الطلب</th>
                                <th class="p-3">الخدمة والقسم</th>
                                <th class="p-3">العميل</th>
                                <th class="p-3">حالة الطلب</th>
                                <th class="p-3">تاريخ ووقت الإسناد</th>
                                <th class="p-3">من أسند الطلب</th>
                                <th class="p-3 text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($craftsman->assignedRequests as $assign)
                                <tr class="hover:bg-gray-50/70 transition">
                                    <td class="p-3 font-mono font-bold text-xs">
                                        <a href="{{ route('admin.orders.show', $assign->request_id) }}" class="text-emerald-700 hover:underline">
                                            #{{ $assign->request->clean_number ?? $assign->request_id }}
                                        </a>
                                    </td>
                                    <td class="p-3 font-semibold text-gray-800">
                                        <div>{{ $assign->request->service->name ?? '-' }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $assign->request->service->category->name ?? '' }}</div>
                                    </td>
                                    <td class="p-3 text-gray-700">
                                        <div>{{ $assign->request->customer->full_name ?? 'عميل' }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $assign->request->customer->phone ?? '' }}</div>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $assign->request->status->name ?? $assign->assignment_status }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-500 font-mono">
                                        {{ $assign->assigned_at ? $assign->assigned_at->format('Y-m-d H:i') : $assign->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="p-3 text-gray-600">
                                        @if($assign->assignedBy)
                                            <span class="font-bold text-gray-800">{{ $assign->assignedBy->full_name }}</span>
                                        @else
                                            <span class="text-gray-400">النظام الآلي</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center">
                                        <a href="{{ route('admin.orders.show', $assign->request_id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold transition">
                                            <span>تفاصيل الطلب</span>
                                            <i class="fa-solid fa-angle-left text-[10px]"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-10 text-center text-gray-400">لا توجد مهام أو طلبات مسندة لهذا الحرفي حتى الآن</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: Services Provided -->
            <div x-show="activeTab === 'services'" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @forelse($craftsman->craftsmanServices as $service)
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/60 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-wrench"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-xs text-gray-900">{{ $service->name }}</h5>
                                <div class="text-[11px] text-gray-500">{{ $service->category->name ?? 'صيانة' }}</div>
                                @if($service->pivot && isset($service->pivot->custom_price))
                                    <div class="text-[11px] font-mono font-bold text-emerald-700 mt-0.5">السعر: {{ number_format($service->pivot->custom_price, 2) }} ر.ي</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-8 text-center text-gray-400">لم يتم تحديد خدمات خاصة، يعمل تحت تخصص: {{ $craftsman->craftsmanProfile->profession ?? 'صيانة عامة' }}</div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 3: Portfolio -->
            <div x-show="activeTab === 'portfolio'" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @forelse($craftsman->portfolio as $item)
                        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm">
                            @if($item->image_path)
                                <a href="{{ asset($item->image_path) }}" target="_blank">
                                    <img src="{{ asset($item->image_path) }}" class="w-full h-40 object-cover hover:opacity-90 transition">
                                </a>
                            @else
                                <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-300">
                                    <i class="fa-solid fa-image text-3xl"></i>
                                </div>
                            @endif
                            <div class="p-3">
                                <h5 class="font-bold text-xs text-gray-900">{{ $item->title }}</h5>
                                @if($item->description)
                                    <p class="text-[11px] text-gray-500 mt-1 line-clamp-2">{{ $item->description }}</p>
                                @endif
                                <div class="text-[10px] text-gray-400 mt-2 font-mono">{{ $item->work_date ? $item->work_date->format('Y-m-d') : '' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-8 text-center text-gray-400">لم يقم الحرفي بإضافة أعمال في المعرض بعد</div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 4: Identity & Documents -->
            <div x-show="activeTab === 'documents'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Front ID Card -->
                    <div class="p-4 rounded-xl border border-gray-100 bg-gray-50 flex flex-col justify-between">
                        <span class="text-xs font-bold text-gray-700 block mb-2">بطاقة الهوية (الوجه الأمامي):</span>
                        @php
                            $frontDoc = $craftsman->craftsmanProfile->identity_image 
                                ?? $craftsman->documents->where('document_type', 'NATIONAL_ID_FRONT')->first()?->file_path;
                        @endphp
                        @if(!empty($frontDoc))
                            <a href="{{ str_starts_with($frontDoc, 'http') ? $frontDoc : url($frontDoc) }}" target="_blank">
                                <img src="{{ str_starts_with($frontDoc, 'http') ? $frontDoc : url($frontDoc) }}" class="w-full h-44 object-cover rounded-xl border border-gray-200 shadow-sm hover:opacity-95 transition">
                            </a>
                        @else
                            <div class="w-full h-44 rounded-xl bg-white border border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400 text-xs">
                                <i class="fa-solid fa-id-card text-3xl mb-1 text-gray-300"></i>
                                <span>لم يتم رفع الوجه الأمامي</span>
                            </div>
                        @endif
                    </div>

                    <!-- Back ID Card -->
                    <div class="p-4 rounded-xl border border-gray-100 bg-gray-50 flex flex-col justify-between">
                        <span class="text-xs font-bold text-gray-700 block mb-2">بطاقة الهوية (الوجه الخلفي):</span>
                        @php
                            $backDoc = $craftsman->craftsmanProfile->national_id_back 
                                ?? $craftsman->documents->where('document_type', 'NATIONAL_ID_BACK')->first()?->file_path;
                        @endphp
                        @if(!empty($backDoc))
                            <a href="{{ str_starts_with($backDoc, 'http') ? $backDoc : url($backDoc) }}" target="_blank">
                                <img src="{{ str_starts_with($backDoc, 'http') ? $backDoc : url($backDoc) }}" class="w-full h-44 object-cover rounded-xl border border-gray-200 shadow-sm hover:opacity-95 transition">
                            </a>
                        @else
                            <div class="w-full h-44 rounded-xl bg-white border border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400 text-xs">
                                <i class="fa-solid fa-id-card text-3xl mb-1 text-gray-300"></i>
                                <span>لم يتم رفع الوجه الخلفي</span>
                            </div>
                        @endif
                    </div>

                    <!-- CV PDF Document -->
                    <div class="p-4 rounded-xl border border-gray-100 bg-gray-50 flex flex-col justify-between">
                        <span class="text-xs font-bold text-gray-700 block mb-2">السيرة الذاتية (CV / PDF):</span>
                        @php
                            $cvDoc = $craftsman->craftsmanProfile->cv_document 
                                ?? $craftsman->documents->where('document_type', 'CV')->first()?->file_path;
                        @endphp
                        @if(!empty($cvDoc))
                            <div class="w-full h-44 rounded-xl bg-emerald-50 border border-emerald-200 flex flex-col items-center justify-center p-3 text-center">
                                <i class="fa-solid fa-file-pdf text-red-500 text-4xl mb-2"></i>
                                <a href="{{ str_starts_with($cvDoc, 'http') ? $cvDoc : url($cvDoc) }}" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow">
                                    <i class="fa-solid fa-eye"></i> معاينة وتحميل PDF
                                </a>
                            </div>
                        @else
                            <div class="w-full h-44 rounded-xl bg-white border border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400 text-xs">
                                <i class="fa-solid fa-file-lines text-3xl mb-1 text-gray-300"></i>
                                <span>لم يرفق ملف CV</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 5: Certificates -->
            <div x-show="activeTab === 'certificates'" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @forelse($craftsman->certificates as $cert)
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex flex-col justify-between space-y-2">
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 inline-block mb-1">شهادة معتمدة</span>
                                <h5 class="font-bold text-xs text-gray-900">{{ $cert->certificate_name }}</h5>
                                <div class="text-[11px] text-gray-500">الجهة المصدرة: {{ $cert->issuing_authority }}</div>
                            </div>
                            <div class="text-[10px] text-gray-400 font-mono pt-2 border-t border-gray-200/60 flex items-center justify-between">
                                <span>تاريخ الإصدار: {{ $cert->issue_date ? $cert->issue_date->format('Y-m-d') : '-' }}</span>
                                <span class="font-bold text-emerald-700">{{ $cert->verification_status }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-8 text-center text-gray-400">لا توجد شهادات مهنية مرفقة لهذا الحرفي</div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 6: Client Reviews -->
            <div x-show="activeTab === 'reviews'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($craftsman->reviewsReceived as $rev)
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-gray-900">{{ $rev->customer->full_name ?? 'عميل' }}</span>
                                <div class="flex items-center text-amber-400 text-xs">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star {{ $i <= $rev->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                    <span class="mr-1 font-mono font-bold text-gray-700">({{ $rev->rating }})</span>
                                </div>
                            </div>
                            @if($rev->comment)
                                <p class="text-xs text-gray-600 bg-white p-2.5 rounded-lg border border-gray-100 italic">"{{ $rev->comment }}"</p>
                            @endif
                            <div class="text-[10px] text-gray-400 font-mono flex items-center justify-between pt-1">
                                <span>{{ $rev->created_at ? $rev->created_at->format('Y-m-d H:i') : '' }}</span>
                                @if($rev->request_id)
                                    <a href="{{ route('admin.orders.show', $rev->request_id) }}" class="text-emerald-700 hover:underline">طلب #{{ $rev->request_id }}</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-8 text-center text-gray-400">لم يسجل العملاء أي تقييمات لهذا الحرفي حتى الآن</div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 7: Wallet & Bank Account Info -->
            <div x-show="activeTab === 'financial'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-5 rounded-2xl bg-emerald-50/50 border border-emerald-200 space-y-3">
                        <h5 class="font-bold text-xs text-emerald-900 flex items-center gap-2">
                            <i class="fa-solid fa-building-columns text-emerald-700"></i>
                            <span>بيانات الحساب البنكي لتحويل المستحقات والأرباح</span>
                        </h5>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between border-b border-emerald-200/40 pb-1">
                                <span class="text-gray-500">اسم البنك / المحفظة:</span>
                                <span class="font-bold text-gray-800">{{ $craftsman->craftsmanProfile->bank_name ?? 'بنك الكريمي للتمويل الأصغر' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-emerald-200/40 pb-1">
                                <span class="text-gray-500">رقم الحساب البنكي / المحفظة:</span>
                                <span class="font-mono font-bold text-gray-800" dir="ltr">{{ $craftsman->craftsmanProfile->bank_account_number ?? $craftsman->phone }}</span>
                            </div>
                            <div class="flex justify-between border-b border-emerald-200/40 pb-1">
                                <span class="text-gray-500">الآيبان الدولي (IBAN):</span>
                                <span class="font-mono font-bold text-gray-800" dir="ltr">{{ $craftsman->craftsmanProfile->iban ?? 'YE----------------------' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">رقم الطوارئ:</span>
                                <span class="font-mono text-gray-800" dir="ltr">{{ $craftsman->craftsmanProfile->emergency_contact ?? 'لا يوجد' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <h5 class="font-bold text-xs text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-coins text-amber-500"></i>
                            <span>ملخص رصيد المحفظة وعمولات المنظومة</span>
                        </h5>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between border-b border-slate-200/60 pb-1">
                                <span class="text-gray-500">الرصيد القابل للسحب:</span>
                                <span class="font-mono font-black text-emerald-700">{{ number_format($craftsman->wallet_balance, 2) }} ر.ي</span>
                            </div>
                            <div class="flex justify-between border-b border-slate-200/60 pb-1">
                                <span class="text-gray-500">نسبة عمولة منصة حِرفة:</span>
                                <span class="font-bold text-gray-800">{{ $craftsman->craftsmanProfile->commission_rate ?? 15 }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">حالة استقبال طلبات السحب:</span>
                                <span class="font-bold text-emerald-700">مفعلة وتتم التسوية عبر الحساب البنكي</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
