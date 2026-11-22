@extends('admin.layout')

@section('title', 'إدارة الحرفيين والفنيين')
@section('page_title', 'قائمة الحرفيين والتحقق من الهوية (Craftsmen & Technicians)')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false, editModalOpen: false, deleteModalOpen: false, editData: {}, deleteUrl: '', craftsmanNameToDelete: '' }">

    <!-- Top Action & Search Bar -->
    <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0">
            <a href="{{ route('admin.craftsmen.index', ['verification' => 'all']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request('verification', 'all') === 'all' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                الكل ({{ \App\Models\User::craftsmen()->count() }})
            </a>
            <a href="{{ route('admin.craftsmen.index', ['verification' => 'pending']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request('verification') === 'pending' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                بانتظار الاعتماد
            </a>
            <a href="{{ route('admin.craftsmen.index', ['verification' => 'approved']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request('verification') === 'approved' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                معتمد وموثق
            </a>
            <a href="{{ route('admin.craftsmen.index', ['verification' => 'rejected']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request('verification') === 'rejected' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                مرفوض
            </a>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <!-- Search input -->
            <form action="{{ route('admin.craftsmen.index') }}" method="GET" class="w-full md:w-72 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالاسم، الجوال، التخصص..." class="w-full pr-10 pl-4 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 outline-none transition">
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
            </form>

            <!-- Add Craftsman Button -->
            <button @click="createModalOpen = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-user-plus"></i>
                <span>إضافة حرفي جديد</span>
            </button>
        </div>
    </div>

    <!-- Craftsmen Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs font-bold border-b border-gray-200 uppercase">
                        <th class="py-3 px-4">الحرفي / الفني</th>
                        <th class="py-3 px-4">المهنة والمدينة</th>
                        <th class="py-3 px-4">حالة الاستقبال (مفتوح/مغلق)</th>
                        <th class="py-3 px-4">العمولة والرصيد</th>
                        <th class="py-3 px-4">حالة التوثيق</th>
                        <th class="py-3 px-4">حالة الحساب</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($craftsmen as $tech)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="py-4 px-4 font-bold text-gray-900">
                            <div class="flex items-center gap-3">
                                @if($tech->profile_image)
                                    <img src="{{ str_starts_with($tech->profile_image, 'http') ? $tech->profile_image : url($tech->profile_image) }}" class="w-10 h-10 rounded-full object-cover border border-gray-200 shadow-sm">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 font-bold flex items-center justify-center text-sm border border-emerald-100">
                                        {{ mb_substr($tech->full_name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.craftsmen.show', $tech->id) }}" class="hover:text-emerald-600 hover:underline">
                                        {{ $tech->full_name }}
                                    </a>
                                    <div class="text-xs text-gray-400 font-mono font-normal">{{ $tech->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-xs font-semibold text-gray-700">
                            <span class="block text-gray-900">{{ $tech->craftsmanProfile->profession ?? 'صيانة عامة' }}</span>
                            <span class="text-gray-400">{{ $tech->craftsmanProfile->city ?? 'صنعاء' }}</span>
                        </td>
                        <td class="py-4 px-4 text-xs">
                            @php
                                $isOpen = ($tech->craftsmanProfile->availability_status ?? false) || $tech->is_online;
                            @endphp
                            @if($isOpen)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>مفتوح (متاح للإسناد)</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                    <span>مغلق الحالة (غير متاح)</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-xs">
                            <span class="font-bold text-gray-800">{{ $tech->craftsmanProfile->commission_rate ?? 15 }}%</span>
                            <div class="font-mono text-emerald-600 font-bold">{{ number_format($tech->wallet_balance, 2) }} ر.ي</div>
                        </td>
                        <td class="py-4 px-4">
                            @php
                                $status = $tech->craftsmanProfile->approval_status ?? 'approved';
                            @endphp
                            @if($status === 'approved')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-emerald-50 text-emerald-700 border-emerald-200 inline-block">
                                    معتمد وموثق
                                </span>
                            @elseif($status === 'rejected')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-red-50 text-red-700 border-red-200 inline-block">
                                    مرفوض
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-amber-50 text-amber-700 border-amber-200 inline-block">
                                    بانتظار المراجعة
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            @if($tech->account_status === 'active')
                                <span class="text-xs font-bold text-emerald-600 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> نشط
                                </span>
                            @else
                                <span class="text-xs font-bold text-red-600 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span> محظور
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.craftsmen.show', $tech->id) }}" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="عرض الملف والوثائق">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>

                                <button type="button" @click="editModalOpen = true; editData = {
                                    id: '{{ $tech->id }}',
                                    full_name: '{{ addslashes($tech->full_name) }}',
                                    phone: '{{ $tech->phone }}',
                                    email: '{{ $tech->email }}',
                                    profession: '{{ addslashes($tech->craftsmanProfile->profession ?? '') }}',
                                    experience_years: '{{ $tech->craftsmanProfile->experience_years ?? 3 }}',
                                    city: '{{ addslashes($tech->craftsmanProfile->city ?? 'صنعاء') }}',
                                    commission_rate: '{{ $tech->craftsmanProfile->commission_rate ?? 15 }}',
                                    profile_image_url: '{{ $tech->profile_image ? (str_starts_with($tech->profile_image, 'http') ? $tech->profile_image : url($tech->profile_image)) : '' }}',
                                    wallet_balance: '{{ $tech->wallet_balance }}'
                                }" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل الحرفي">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>

                                <form action="{{ route('admin.craftsmen.block', $tech->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition" title="{{ $tech->account_status === 'active' ? 'حظر الحساب' : 'إلغاء الحظر' }}">
                                        <i class="fa-solid fa-ban text-xs"></i>
                                    </button>
                                </form>

                                <button type="button" @click="deleteModalOpen = true; deleteUrl = '{{ route('admin.craftsmen.destroy', $tech->id) }}'; craftsmanNameToDelete = '{{ addslashes($tech->full_name) }}'" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف الحرفي نهائياً">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-400">
                            <i class="fa-solid fa-user-slash text-4xl text-gray-300 mb-2"></i>
                            <p>لا يوجد حرفيين مطابقين لمعايير البحث</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($craftsmen->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $craftsmen->links() }}
        </div>
        @endif
    </div>

    <!-- Create Craftsman Modal -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="createModalOpen = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-emerald-600"></i>
                    <span>إضافة حرفي جديد واعتماده</span>
                </h3>
                <button @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.craftsmen.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">الاسم الرباعي *</label>
                    <input type="text" name="full_name" required placeholder="مثال: أحمد منصور السالم" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">رقم الجوال *</label>
                        <input type="text" name="phone" required placeholder="77XXXXXXXX" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none" dir="ltr">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">البريد الإلكتروني (اختياري)</label>
                        <input type="email" name="email" placeholder="craftsman@example.com" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none" dir="ltr">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">الصورة الشخصية (Avatar)</label>
                        <input type="file" name="profile_image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs bg-gray-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:bg-emerald-50 file:text-emerald-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">صورة الهوية / الشهادة</label>
                        <input type="file" name="identity_image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs bg-gray-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:bg-emerald-50 file:text-emerald-700">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">المهنة / التخصص *</label>
                        <input type="text" name="profession" required placeholder="مثال: فني سباكة، فني تكييف" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">سنوات الخبرة *</label>
                        <input type="number" name="experience_years" required value="4" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">المدينة *</label>
                        <input type="text" name="city" value="صنعاء" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">نسبة عمولة المنصة (%)</label>
                        <input type="number" step="0.5" name="commission_rate" value="15" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">كلمة المرور *</label>
                    <input type="password" name="password" required value="password123" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 outline-none" dir="ltr">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20">حفظ واعتماد الحرفي</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Craftsman Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="editModalOpen = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                    <span>تعديل بيانات الحرفي</span>
                </h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form :action="'{{ url('/admin/craftsmen') }}/' + editData.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">الاسم الكامل *</label>
                    <input type="text" name="full_name" x-model="editData.full_name" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">رقم الجوال *</label>
                        <input type="text" name="phone" x-model="editData.phone" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none" dir="ltr">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">البريد الإلكتروني</label>
                        <input type="email" name="email" x-model="editData.email" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none" dir="ltr">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">تحديث الصورة الشخصية</label>
                        <input type="file" name="profile_image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs bg-gray-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:bg-blue-50 file:text-blue-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">تحديث صورة الهوية</label>
                        <input type="file" name="identity_image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs bg-gray-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:bg-blue-50 file:text-blue-700">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">المهنة / التخصص *</label>
                        <input type="text" name="profession" x-model="editData.profession" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">سنوات الخبرة *</label>
                        <input type="number" name="experience_years" x-model="editData.experience_years" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">المدينة *</label>
                        <input type="text" name="city" x-model="editData.city" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">نسبة العمولة (%)</label>
                        <input type="number" step="0.5" name="commission_rate" x-model="editData.commission_rate" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">رصيد المحفظة (ر.ي)</label>
                    <input type="number" step="0.5" name="wallet_balance" x-model="editData.wallet_balance" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">تحديث البيانات</button>
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
                <h4 class="font-bold text-gray-900 text-base">تأكيد حذف الحرفي</h4>
                <p class="text-xs text-gray-500 mt-1">
                    هل أنت متأكد من رغبتك في حذف الحرفي (<span class="font-bold text-red-600" x-text="craftsmanNameToDelete"></span>) نهائياً من المنظومة؟
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

</div>
@endsection
