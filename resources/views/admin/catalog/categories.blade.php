@extends('admin.layout')

@section('title', 'تصنيفات الخدمات')
@section('page_title', 'دليل تصنيفات الخدمات (Categories)')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">تصنيفات المنصة</h3>
            <p class="text-xs text-slate-400">إدارة المجالات والتخصصات الرئيسية للخدمات</p>
        </div>
        <button onclick="document.getElementById('addCategoryModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة تصنيف جديد</span>
        </button>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $cat)
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-xl font-bold overflow-hidden border border-brand-100">
                        @if($cat->image && (file_exists(public_path('storage/' . $cat->image)) || str_starts_with($cat->image, 'http')))
                            <img src="{{ str_starts_with($cat->image, 'http') ? $cat->image : asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                        @elseif($cat->icon && (str_contains($cat->icon, '/') || str_contains($cat->icon, '.')))
                            <img src="{{ asset('storage/' . $cat->icon) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid fa-{{ $cat->icon ?? 'layer-group' }}"></i>
                        @endif
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $cat->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                        {{ $cat->is_active ? 'نشط' : 'معطل' }}
                    </span>
                </div>

                <div>
                    <h4 class="font-bold text-slate-800 text-base">{{ $cat->name }}</h4>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $cat->description ?? 'لا يوجد وصف' }}</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">
                        <strong class="text-slate-800">{{ $cat->services_count }}</strong> خدمات تابعة
                    </span>
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('admin.catalog.services', ['category_id' => $cat->id]) }}" class="px-2.5 py-1 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold transition">الخدمات</a>
                        
                        <!-- زر التعديل -->
                        <button onclick="editCategory({{ json_encode($cat) }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل التصنيف">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>

                        <!-- زر الحذف -->
                        <form action="{{ route('admin.catalog.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا التصنيف بالكامل؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف التصنيف">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white p-12 rounded-2xl border border-slate-200/80 text-center text-slate-400">
                <i class="fa-solid fa-layer-group text-4xl mb-3 text-slate-300 block"></i>
                <span>لم يتم العثور على تصنيفات بعد</span>
            </div>
        @endforelse
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">إضافة تصنيف جديد</h4>
                <button onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.catalog.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم التصنيف <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="مثال: سباكة وصحي" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">صورة التصنيف / الأيقونة</label>
                    <input type="file" name="image" accept="image/*" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <p class="text-[10px] text-slate-400 mt-1">يمكنك رفع صورة (PNG, JPG, SVG, WebP) بحجم أقصى 2MB</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">أو رمز الأيقونة البديل (اختياري)</label>
                    <input type="text" name="icon" placeholder="مثال: wrench أو bolt" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ترتيب الظهور</label>
                    <input type="number" name="sort_order" value="0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الوصف</label>
                    <textarea name="description" rows="3" placeholder="وصف موجز للتصنيف..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition">حفظ التصنيف</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تعديل بيانات التصنيف</h4>
                <button onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="editCategoryForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم التصنيف <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_cat_name" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">تغيير صورة التصنيف</label>
                    <input type="file" name="image" accept="image/*" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <p class="text-[10px] text-slate-400 mt-1">اترك الحقل فارغاً إذا كنت لا ترغب بتغيير الصورة الحالية</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رمز الأيقونة (FontAwesome بديل)</label>
                    <input type="text" id="edit_cat_icon" name="icon" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ترتيب الظهور</label>
                    <input type="number" id="edit_cat_sort" name="sort_order" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الحالة</label>
                    <select id="edit_cat_active" name="is_active" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <option value="1">نشط</option>
                        <option value="0">معطل</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الوصف</label>
                    <textarea id="edit_cat_desc" name="description" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function editCategory(cat) {
        document.getElementById('editCategoryForm').action = '/admin/categories/' + cat.id;
        document.getElementById('edit_cat_name').value = cat.name;
        document.getElementById('edit_cat_icon').value = cat.icon || '';
        document.getElementById('edit_cat_sort').value = cat.sort_order || 0;
        document.getElementById('edit_cat_active').value = cat.is_active ? '1' : '0';
        document.getElementById('edit_cat_desc').value = cat.description || '';
        document.getElementById('editCategoryModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
