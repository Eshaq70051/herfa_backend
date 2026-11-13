@extends('admin.layout')

@section('title', 'الملف المهني: ' . $craftsman->full_name)
@section('page_title', 'الملف المهني والوثائق: ' . $craftsman->full_name)

@section('content')
<div class="space-y-6">

    <!-- Top Action Nav -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.craftsmen.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-emerald-600 transition">
            <i class="fa-solid fa-arrow-right"></i>
            <span>العودة لقائمة الحرفيين</span>
        </a>

        <div class="flex items-center gap-3">
            <form action="{{ route('admin.craftsmen.block', $craftsman->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $craftsman->account_status === 'active' ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    <i class="fa-solid fa-ban"></i>
                    <span>{{ $craftsman->account_status === 'active' ? 'حظر الحساب' : 'إلغاء الحظر والتفعيل' }}</span>
                </button>
            </form>

            <form action="{{ route('admin.craftsmen.destroy', $craftsman->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف الحرفي نهائياً؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold transition flex items-center gap-2">
                    <i class="fa-solid fa-trash"></i>
                    <span>حذف الحرفي</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Craftsman Info Card -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-gray-100">
            <div class="flex items-center gap-4">
                @if($craftsman->profile_image)
                    <img src="{{ str_starts_with($craftsman->profile_image, 'http') ? $craftsman->profile_image : url($craftsman->profile_image) }}" class="w-16 h-16 rounded-2xl object-cover border border-emerald-200 shadow-sm">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 font-extrabold text-2xl flex items-center justify-center border border-emerald-100">
                        {{ mb_substr($craftsman->full_name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <div class="flex items-center gap-3">
                        <h3 class="text-xl font-bold text-gray-800">{{ $craftsman->full_name }}</h3>
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
                    </div>
                    <div class="flex items-center gap-4 text-xs text-gray-500 mt-1">
                        <div><i class="fa-solid fa-phone text-gray-400 ml-1"></i> <span class="font-mono">{{ $craftsman->phone }}</span></div>
                        <div><i class="fa-solid fa-wrench text-gray-400 ml-1"></i> {{ $craftsman->craftsmanProfile->profession ?? 'صيانة عامة' }}</div>
                        <div><i class="fa-solid fa-location-dot text-gray-400 ml-1"></i> {{ $craftsman->craftsmanProfile->city ?? 'الرياض' }}</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="text-center">
                    <span class="text-xs text-gray-400 block">التقييم</span>
                    <span class="text-xl font-black text-amber-500 flex items-center justify-center gap-1">
                        <i class="fa-solid fa-star text-sm"></i> {{ $craftsman->craftsmanProfile->rating_avg ?? '5.0' }}
                    </span>
                </div>
                <div class="text-center">
                    <span class="text-xs text-gray-400 block">رصيد المحفظة</span>
                    <span class="text-xl font-mono font-bold text-emerald-700">{{ number_format($craftsman->wallet_balance, 2) }} ر.س</span>
                </div>
                <div class="text-center">
                    <span class="text-xs text-gray-400 block">العمولة</span>
                    <span class="text-xl font-bold text-gray-800">{{ $craftsman->craftsmanProfile->commission_rate ?? 15 }}%</span>
                </div>
            </div>
        </div>

        <!-- Verification & Availability Update Form -->
        <div class="mt-6">
            <h4 class="font-bold text-gray-800 text-sm mb-3">إدارة حالة الاعتماد وتوثيق الهوية وإمكانية استقبال الطلبات:</h4>
            <form action="{{ route('admin.craftsmen.verify', $craftsman->id) }}" method="POST" class="flex flex-col sm:flex-row items-center gap-4">
                @csrf
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <select name="status" class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="approved" {{ ($craftsman->craftsmanProfile->approval_status ?? '') === 'approved' ? 'selected' : '' }}>معتمد وموثق رسمياً (Approved)</option>
                        <option value="pending" {{ ($craftsman->craftsmanProfile->approval_status ?? '') === 'pending' ? 'selected' : '' }}>بانتظار المراجعة (Pending)</option>
                        <option value="rejected" {{ ($craftsman->craftsmanProfile->approval_status ?? '') === 'rejected' ? 'selected' : '' }}>مرفوض (Rejected)</option>
                    </select>

                    <input type="number" step="0.5" name="commission_rate" value="{{ $craftsman->craftsmanProfile->commission_rate ?? 15 }}" placeholder="نسبة العمولة %" class="px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none w-32">

                    <label class="flex items-center gap-2 cursor-pointer bg-gray-50 px-3 py-2 rounded-xl border border-gray-200">
                        <input type="checkbox" name="availability_status" value="1" {{ ($craftsman->craftsmanProfile->availability_status ?? false) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs font-bold text-gray-700">فتح الحالة (متاح لاستقبال الطلبات)</span>
                    </label>
                </div>

                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition">
                    تحديث الحالة والاعتماد
                </button>
            </form>
        </div>
    </div>

    <!-- Identity & Documents Preview -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
        <h4 class="font-bold text-gray-800 text-base flex items-center gap-2">
            <i class="fa-solid fa-id-card text-emerald-600"></i>
            <span>وثائق الهوية والتراخيص المرفقة</span>
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50">
                <span class="text-xs font-bold text-gray-700 block mb-2">صورة وثيقة الهوية الوطنية / الإقامة:</span>
                @if(!empty($craftsman->craftsmanProfile->identity_image))
                    <a href="{{ str_starts_with($craftsman->craftsmanProfile->identity_image, 'http') ? $craftsman->craftsmanProfile->identity_image : url($craftsman->craftsmanProfile->identity_image) }}" target="_blank">
                        <img src="{{ str_starts_with($craftsman->craftsmanProfile->identity_image, 'http') ? $craftsman->craftsmanProfile->identity_image : url($craftsman->craftsmanProfile->identity_image) }}" class="w-full h-48 object-cover rounded-xl border border-gray-200 shadow-sm hover:opacity-95 transition">
                    </a>
                @else
                    <div class="w-full h-40 rounded-xl bg-white border border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400 text-xs">
                        <i class="fa-solid fa-id-badge text-3xl mb-1 text-gray-300"></i>
                        <span>لم يتم رفع وثيقة هوية حتى الآن</span>
                    </div>
                @endif
            </div>

            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50">
                <span class="text-xs font-bold text-gray-700 block mb-2">الصورة الشخصية المعتمدة:</span>
                @if(!empty($craftsman->profile_image))
                    <a href="{{ str_starts_with($craftsman->profile_image, 'http') ? $craftsman->profile_image : url($craftsman->profile_image) }}" target="_blank">
                        <img src="{{ str_starts_with($craftsman->profile_image, 'http') ? $craftsman->profile_image : url($craftsman->profile_image) }}" class="w-full h-48 object-cover rounded-xl border border-gray-200 shadow-sm hover:opacity-95 transition">
                    </a>
                @else
                    <div class="w-full h-40 rounded-xl bg-white border border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400 text-xs">
                        <i class="fa-solid fa-user text-3xl mb-1 text-gray-300"></i>
                        <span>لا توجد صورة شخصية مسجلة</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Assigned Orders History -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
        <h4 class="font-bold text-gray-800 text-base flex items-center gap-2">
            <i class="fa-solid fa-clipboard-check text-emerald-600"></i>
            <span>سجل الطلبات والمهام المسندة للحرفي ({{ $craftsman->assignedRequests->count() }})</span>
        </h4>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs uppercase">
                    <tr>
                        <th class="p-3">رقم الطلب</th>
                        <th class="p-3">الخدمة</th>
                        <th class="p-3">حالة الإسناد</th>
                        <th class="p-3">تاريخ الإسناد</th>
                        <th class="p-3 text-center">عرض الطلب</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($craftsman->assignedRequests as $assign)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="p-3 font-mono font-bold text-xs text-emerald-700">
                                {{ $assign->request->request_number ?? ('#' . $assign->request_id) }}
                            </td>
                            <td class="p-3 text-xs font-semibold text-gray-800">
                                {{ $assign->request->service->name ?? '-' }}
                            </td>
                            <td class="p-3 text-xs">
                                <span class="px-2 py-0.5 rounded-full font-bold bg-emerald-50 text-emerald-700">{{ $assign->assignment_status }}</span>
                            </td>
                            <td class="p-3 text-xs text-gray-400 font-mono">
                                {{ $assign->assigned_at ? $assign->assigned_at->format('Y-m-d H:i') : $assign->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="p-3 text-center">
                                <a href="{{ route('admin.orders.show', $assign->request_id) }}" class="text-xs text-blue-600 hover:underline font-bold">
                                    تفاصيل
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">لا توجد طلبات مسندة لهذا الحرفي بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
