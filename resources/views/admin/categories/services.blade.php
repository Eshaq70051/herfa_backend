@extends('admin.layout')

@section('title', 'خدمات قسم ' . $category->name)
@section('page_title', 'خدمات قسم: ' . $category->name)

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false, editModalOpen: false, deleteModalOpen: false, editData: {}, deleteUrl: '', svcNameToDelete: '' }">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.categories.index') }}" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center transition" title="رجوع للأقسام">
                <i class="fa-solid fa-arrow-right"></i>
            </a>
            <div>
                <h3 class="font-bold text-gray-800 text-base">قائمة الخدمات التابعة لقسم ({{ $category->name }})</h3>
                <span class="text-xs text-gray-500">إجمالي الخدمات: {{ $category->services->count() }}</span>
            </div>
        </div>

        <button @click="createModalOpen = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة خدمة فرعية جديدة</span>
        </button>
    </div>

    <!-- Services Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs font-bold border-b border-gray-200 uppercase">
                        <th class="py-3 px-4">صورة الخدمة</th>
                        <th class="py-3 px-4">اسم الخدمة</th>
                        <th class="py-3 px-4">الوصف</th>
                        <th class="py-3 px-4">السعر الأساسي</th>
                        <th class="py-3 px-4">فترة الضمان</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($category->services as $service)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="py-3 px-4">
                            @if($service->image_url)
                                <img src="{{ $service->image_url }}" alt="{{ $service->name }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 shadow-sm">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-sm border border-gray-200">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td class="py-4 px-4 font-bold text-gray-900">
                            {{ $service->name }}
                        </td>
                        <td class="py-4 px-4 text-xs text-gray-500 max-w-xs truncate">
                            {{ $service->description ?? 'لا يوجد وصف' }}
                        </td>
                        <td class="py-4 px-4 font-mono font-bold text-emerald-600">
                            {{ number_format($service->base_price, 2) }} ر.س
                        </td>
                        <td class="py-4 px-4 text-xs font-bold text-gray-700">
                            <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 px-2 py-0.5 rounded-lg border border-amber-200">
                                <i class="fa-solid fa-shield text-[10px]"></i> {{ $service->warranty_days }} يوم
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            @if($service->is_active)
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-emerald-50 text-emerald-700 border-emerald-200 inline-block">نشطة</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-gray-100 text-gray-600 border-gray-200 inline-block">معطلة</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <button type="button" @click="editModalOpen = true; editData = {
                                    id: '{{ $service->id }}',
                                    name: '{{ addslashes($service->name) }}',
                                    description: '{{ addslashes($service->description ?? '') }}',
                                    base_price: '{{ $service->base_price }}',
                                    warranty_days: '{{ $service->warranty_days }}',
                                    image_url: '{{ $service->image_url ?? '' }}',
                                    is_active: {{ $service->is_active ? 'true' : 'false' }}
                                }" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل الخدمة">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>

                                <button type="button" @click="deleteModalOpen = true; deleteUrl = '{{ route('admin.categories.services.destroy', $service->id) }}'; svcNameToDelete = '{{ addslashes($service->name) }}'" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف الخدمة">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-400">
                            <i class="fa-solid fa-tools text-4xl text-gray-300 mb-2"></i>
                            <p>لا توجد خدمات مضافة في هذا القسم بعد</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Service Modal -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="createModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-emerald-600"></i>
                    <span>إضافة خدمة إلى ({{ $category->name }})</span>
                </h3>
                <button @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.categories.services.store', $category->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">اسم الخدمة *</label>
                    <input type="text" name="name" required placeholder="مثال: فحص تسريبات المياه، تركيب مغسلة" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">صورة الخدمة (تظهر في التطبيق)</label>
                    <input type="file" name="image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-gray-50 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">السعر الأساسي (ر.س) *</label>
                        <input type="number" step="0.5" name="base_price" required placeholder="80.00" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">فترة الضمان (بالأيام) *</label>
                        <input type="number" name="warranty_days" required value="30" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">وصف تفصيلي للخدمة</label>
                    <textarea name="description" rows="3" placeholder="ماذا تشمل هذه الخدمة؟ الشروط والملاحظات..." class="w-full p-2.5 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20">حفظ الخدمة</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="editModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                    <span>تعديل الخدمة</span>
                </h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form :action="'{{ url('/admin/categories/services') }}/' + editData.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">اسم الخدمة *</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">تحديث صورة الخدمة</label>
                    <input type="file" name="image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-gray-50 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <template x-if="editData.image_url">
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-[11px] text-gray-500">الصورة الحالية:</span>
                            <img :src="editData.image_url" class="w-10 h-10 rounded-lg object-cover border">
                        </div>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">السعر الأساسي (ر.س) *</label>
                        <input type="number" step="0.5" name="base_price" x-model="editData.base_price" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">فترة الضمان (بالأيام) *</label>
                        <input type="number" name="warranty_days" x-model="editData.warranty_days" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">الوصف</label>
                    <textarea name="description" x-model="editData.description" rows="3" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="svc_is_active" :checked="editData.is_active" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <label for="svc_is_active" class="text-xs font-semibold text-gray-700">تفعيل هذه الخدمة في التطبيق</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">تحديث الخدمة</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Service Modal -->
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="deleteModalOpen = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto text-xl">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-900 text-base">تأكيد حذف الخدمة</h4>
                <p class="text-xs text-gray-500 mt-1">
                    هل أنت متأكد من رغبتك في حذف الخدمة (<span class="font-bold text-red-600" x-text="svcNameToDelete"></span>)؟
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
