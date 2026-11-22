@extends('admin.layout')

@section('title', 'إدارة العملاء المسجلين')
@section('page_title', 'سجل عملاء منصة حِرفة (Clients & Customers)')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false, editModalOpen: false, deleteModalOpen: false, editData: {}, deleteUrl: '', clientNameToDelete: '' }">

    <!-- Top Action Bar & Search -->
    <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-gray-800 text-base">قائمة العملاء والمستخدمين</h3>
            <span class="text-xs text-gray-500">إجمالي العملاء: {{ $clients->total() }}</span>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <form action="{{ route('admin.clients.index') }}" method="GET" class="w-full md:w-72 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالاسم، رقم الجوال..." class="w-full pr-10 pl-4 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 outline-none transition">
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
            </form>

            <button @click="createModalOpen = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-user-plus"></i>
                <span>إضافة عميل جديد</span>
            </button>
        </div>
    </div>

    <!-- Clients Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs font-bold border-b border-gray-200 uppercase">
                        <th class="py-3 px-4">اسم العميل</th>
                        <th class="py-3 px-4">رقم الجوال</th>
                        <th class="py-3 px-4">البريد الإلكتروني</th>
                        <th class="py-3 px-4">عدد الطلبات</th>
                        <th class="py-3 px-4">رصيد المحفظة</th>
                        <th class="py-3 px-4">حالة الحساب</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($clients as $client)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="py-4 px-4 font-bold text-gray-900">
                            <a href="{{ route('admin.clients.show', $client->id) }}" class="flex items-center gap-2 group hover:text-emerald-600 transition">
                                @if($client->profile_image)
                                    <img src="{{ str_starts_with($client->profile_image, 'http') ? $client->profile_image : url($client->profile_image) }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-sm group-hover:border-emerald-500 transition">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs border border-blue-100 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition">
                                        {{ mb_substr($client->full_name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="group-hover:underline">{{ $client->full_name }}</span>
                                    <div class="text-[11px] text-gray-400 font-mono font-normal">#{{ $client->id }}</div>
                                </div>
                            </a>
                        </td>
                        <td class="py-4 px-4 font-mono text-xs text-gray-600">
                            <a href="{{ route('admin.clients.show', $client->id) }}" class="hover:text-emerald-600 transition">
                                {{ $client->phone }}
                            </a>
                        </td>
                        <td class="py-4 px-4 text-xs text-gray-500">
                            {{ $client->email ?? 'لا يوجد' }}
                        </td>
                        <td class="py-4 px-4 font-bold text-emerald-600">
                            <a href="{{ route('admin.clients.show', $client->id) }}#orders" class="hover:underline inline-flex items-center gap-1">
                                <span>{{ $client->service_requests_count }} طلب</span>
                                <i class="fa-solid fa-angle-left text-[10px] text-emerald-400"></i>
                            </a>
                        </td>
                        <td class="py-4 px-4 font-mono font-bold text-gray-900">
                            {{ number_format($client->wallet_balance, 2) }} ر.ي
                        </td>
                        <td class="py-4 px-4">
                            @if($client->account_status === 'active')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-emerald-50 text-emerald-700 border-emerald-200 inline-block">
                                    نشط
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-red-50 text-red-700 border-red-200 inline-block">
                                    موقوف / معطل
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.clients.show', $client->id) }}" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="عرض تفاصيل العميل والطلبات">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>

                                <button type="button" @click="editModalOpen = true; editData = {
                                    id: '{{ $client->id }}',
                                    full_name: '{{ addslashes($client->full_name) }}',
                                    phone: '{{ $client->phone }}',
                                    email: '{{ $client->email }}',
                                    wallet_balance: '{{ $client->wallet_balance }}',
                                    account_status: '{{ $client->account_status }}'
                                }" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل العميل">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>

                                <button type="button" @click="deleteModalOpen = true; deleteUrl = '{{ route('admin.clients.destroy', $client->id) }}'; clientNameToDelete = '{{ addslashes($client->full_name) }}'" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف العميل">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-400">
                            <i class="fa-solid fa-users text-4xl text-gray-300 mb-2"></i>
                            <p>لا يوجد عملاء مسجلين حالياً</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clients->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $clients->links() }}
        </div>
        @endif
    </div>

    <!-- Create Client Modal -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="createModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-emerald-600"></i>
                    <span>إضافة عميل جديد للمنظومة</span>
                </h3>
                <button @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.clients.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">الاسم الكامل *</label>
                    <input type="text" name="full_name" required placeholder="مثال: تركي الشمري" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">الصورة الشخصية (اختياري)</label>
                    <input type="file" name="profile_image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs bg-gray-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:bg-emerald-50 file:text-emerald-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">رقم الجوال *</label>
                    <input type="text" name="phone" required placeholder="05XXXXXXXX" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none" dir="ltr">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">البريد الإلكتروني (اختياري)</label>
                    <input type="email" name="email" placeholder="client@example.com" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none" dir="ltr">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">كلمة المرور *</label>
                    <input type="password" name="password" required value="password123" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 outline-none" dir="ltr">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">رصيد المحفظة الافتتاحي (ر.ي)</label>
                    <input type="number" step="0.5" name="wallet_balance" value="0.00" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20">حفظ العميل</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Client Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="editModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-blue-600"></i>
                    <span>تعديل بيانات العميل</span>
                </h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form :action="'{{ url('/admin/clients') }}/' + editData.id" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                    <label class="block text-xs font-bold text-gray-600 mb-1">رصيد المحفظة (ر.ي)</label>
                    <input type="number" step="0.5" name="wallet_balance" x-model="editData.wallet_balance" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">حالة الحساب *</label>
                    <select name="account_status" x-model="editData.account_status" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="active">نشط (Active)</option>
                        <option value="inactive">غير مفعل (Inactive)</option>
                        <option value="suspended">موقوف / محظور (Suspended)</option>
                    </select>
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
                <h4 class="font-bold text-gray-900 text-base">تأكيد حذف العميل</h4>
                <p class="text-xs text-gray-500 mt-1">
                    هل أنت متأكد من رغبتك في حذف العميل (<span class="font-bold text-red-600" x-text="clientNameToDelete"></span>)؟ سيتم حذف جميع طلباته وعناوينه.
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
