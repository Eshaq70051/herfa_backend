@extends('admin.layout')

@section('title', 'وسائل وطرق الدفع')
@section('page_title', 'إدارة وسائل الدفع الإلكتروني والنقدي (Payment Methods)')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">طرق الدفع المتاحة في التطبيق</h3>
            <p class="text-xs text-slate-400">إدارة بوابات الدفع والمحافظ الإلكترونية (الكريمي، جوالي، ون كاش، جيب، نقداً، المحفظة)</p>
        </div>
        <button onclick="document.getElementById('addMethodModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة وسيلة دفع جديدة</span>
        </button>
    </div>

    <!-- Payment Methods Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($methods as $m)
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between space-y-4 hover:border-brand-300 transition">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-xl font-bold">
                        @if(str_contains(strtolower($m->code), 'cash'))
                            <i class="fa-solid fa-money-bill-wave"></i>
                        @elseif(str_contains(strtolower($m->code), 'wallet'))
                            <i class="fa-solid fa-wallet"></i>
                        @elseif(str_contains(strtolower($m->code), 'card'))
                            <i class="fa-solid fa-credit-card"></i>
                        @else
                            <i class="fa-solid fa-building-columns"></i>
                        @endif
                    </div>
                    <form action="{{ route('admin.settings.payment_methods.toggle', $m->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 rounded-full text-[11px] font-bold transition {{ $m->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}" title="انقر للتبديل بين التفعيل والتعطيل">
                            {{ $m->is_active ? 'نشط في التطبيق' : 'معطل' }}
                        </button>
                    </form>
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="font-bold text-slate-800 text-base">{{ $m->name }}</h4>
                    </div>
                    <p class="text-xs font-mono font-bold text-brand-700 mt-0.5">رمز الكود: {{ $m->code }}</p>
                    <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">{{ $m->description ?? 'لا يوجد وصف مضاف' }}</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-mono text-[11px]">
                        {{ $m->created_at ? $m->created_at->format('Y-m-d') : '' }}
                    </span>
                    <div class="flex items-center gap-1.5">
                        <!-- تعديل -->
                        <button onclick="editMethod({{ json_encode($m) }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>

                        <!-- حذف -->
                        <form action="{{ route('admin.settings.payment_methods.destroy', $m->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف وسيلة الدفع هذه؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white p-12 rounded-2xl border border-slate-200/80 text-center text-slate-400">
                <i class="fa-solid fa-credit-card text-4xl mb-3 text-slate-300 block"></i>
                <span>لم يتم العثور على وسائل دفع مسجلة</span>
            </div>
        @endforelse
    </div>

    <!-- Add Payment Method Modal -->
    <div id="addMethodModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">إضافة وسيلة دفع جديدة</h4>
                <button onclick="document.getElementById('addMethodModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.settings.payment_methods.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم وسيلة الدفع <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="مثال: الكريمي إكسبرس (Kuraimi)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الكود البرمجي (Code) <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required placeholder="مثال: kuraimi, jawwali, cash, wallet" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:outline-none focus:border-brand-500">
                    <p class="text-[10px] text-slate-400 mt-1">يجب أن يكون بالإنجليزية وبحروف صغيرة وبدون مسافات، لأنه يربط التطبيق بالباك إند.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">حالة التفعيل</label>
                    <select name="is_active" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="1">مفعلة وتظهر في تطبيق فلاتر</option>
                        <option value="0">معطلة ومخفية</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">وصف وتعليمات الدفع</label>
                    <textarea name="description" rows="3" placeholder="اكتب تعليمات للعميل عند اختيار هذه الطريقة (مثل رقم حساب الإيداع)..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('addMethodModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition">حفظ الوسيلة</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Payment Method Modal -->
    <div id="editMethodModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تعديل بيانات وسيلة الدفع</h4>
                <button onclick="document.getElementById('editMethodModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="editMethodForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم وسيلة الدفع <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_pm_name" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الكود البرمجي (Code) <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_pm_code" name="code" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">حالة التفعيل</label>
                    <select id="edit_pm_active" name="is_active" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="1">مفعلة وتظهر في تطبيق فلاتر</option>
                        <option value="0">معطلة ومخفية</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">وصف وتعليمات الدفع</label>
                    <textarea id="edit_pm_desc" name="description" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('editMethodModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function editMethod(m) {
        document.getElementById('editMethodForm').action = '/admin/settings/payment-methods/' + m.id;
        document.getElementById('edit_pm_name').value = m.name;
        document.getElementById('edit_pm_code').value = m.code;
        document.getElementById('edit_pm_active').value = m.is_active ? '1' : '0';
        document.getElementById('edit_pm_desc').value = m.description || '';
        document.getElementById('editMethodModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
