@extends('admin.layout')

@section('title', 'العملات')
@section('page_title', 'إدارة العملات وأسعار الصرف الأساسية (Currencies)')

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    editCurrency: {},
    editUrl: ''
}">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">دليل العملات والوحدات النقدية</h3>
            <p class="text-xs text-slate-400">العملات المعتمدة في تسعير الخدمات وفواتير المشتريات ومستحقات الحرفيين</p>
        </div>
        <button @click="showAddModal = true" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2 shadow-md shadow-primary-600/20">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة عملة جديدة</span>
        </button>
    </div>

    <!-- Currencies Table -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5">الرمز الدولي (ISO)</th>
                        <th class="px-4 py-3.5">اسم العملة</th>
                        <th class="px-4 py-3.5">الرمز المحلي</th>
                        <th class="px-4 py-3.5">المنازل العشرية</th>
                        <th class="px-4 py-3.5">الحالة</th>
                        <th class="px-4 py-3.5">الافتراضية للنظام</th>
                        <th class="px-4 py-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($currencies as $currency)
                        <tr class="hover:bg-slate-50/60 transition {{ $currency->is_default ? 'bg-primary-50/20' : '' }}">
                            <td class="px-4 py-3.5 font-mono font-bold text-primary-700 text-sm">{{ $currency->code }}</td>
                            <td class="px-4 py-3.5 font-bold text-slate-800">{{ $currency->name }}</td>
                            <td class="px-4 py-3.5 font-semibold text-slate-600">{{ $currency->symbol }}</td>
                            <td class="px-4 py-3.5 font-mono">{{ $currency->decimal_places }}</td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $currency->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $currency->is_active ? 'نشطة' : 'معطلة' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($currency->is_default)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fa-solid fa-star text-[9px]"></i>
                                        <span>أساسية (Default)</span>
                                    </span>
                                @else
                                    <form action="{{ route('admin.accounting.currencies.default', $currency->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-slate-400 hover:text-amber-600 transition font-semibold" title="تعيين كافتراضية">
                                            تعيين كأساسية
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button @click="editCurrency = {
                                        id: {{ $currency->id }},
                                        code: '{{ $currency->code }}',
                                        name: '{{ addslashes($currency->name) }}',
                                        symbol: '{{ addslashes($currency->symbol) }}',
                                        decimal_places: {{ $currency->decimal_places }},
                                        is_active: {{ $currency->is_active ? '1' : '0' }},
                                        is_default: {{ $currency->is_default ? '1' : '0' }}
                                    }; editUrl = '{{ route('admin.accounting.currencies.update', $currency->id) }}'; showEditModal = true" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل العملة">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    @if(!$currency->is_default)
                                        <form action="{{ route('admin.accounting.currencies.destroy', $currency->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه العملة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف العملة">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">لم تتم إضافة عملات بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($currencies->hasPages())
            <div class="border-t border-slate-100 p-4">{{ $currencies->links() }}</div>
        @endif
    </div>

    <!-- MODAL: Add Currency -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="showAddModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-primary-600"></i>
                    <span>إضافة عملة جديدة</span>
                </h4>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.accounting.currencies.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رمز ISO (3 أحرف إنجليزية)</label>
                    <input name="code" maxlength="3" minlength="3" required placeholder="YER / SAR / USD" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 uppercase font-mono text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم العملة بالعربية</label>
                    <input name="name" maxlength="100" required placeholder="مثال: ريال يمني" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الرمز المختصر المحلي</label>
                    <input name="symbol" maxlength="10" required placeholder="مثال: ر.ي أو SAR" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">عدد المنازل العشرية</label>
                    <input type="number" name="decimal_places" min="0" max="4" value="2" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-mono">
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold text-slate-700 pt-1">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded text-primary-600">
                        <span>نشطة في النظام</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" class="rounded text-primary-600">
                        <span>العملة الأساسية للنظام</span>
                    </label>
                </div>
                <button type="submit" class="w-full rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-primary-700">
                    حفظ العملة
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Currency -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="showEditModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                    <span>تعديل العملة: <span class="font-mono text-primary-600" x-text="editCurrency.code"></span></span>
                </h4>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form :action="editUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم العملة</label>
                    <input name="name" x-model="editCurrency.name" maxlength="100" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الرمز المحلي</label>
                    <input name="symbol" x-model="editCurrency.symbol" maxlength="10" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">عدد المنازل العشرية</label>
                    <input type="number" name="decimal_places" x-model="editCurrency.decimal_places" min="0" max="4" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الحالة</label>
                    <select name="is_active" x-model="editCurrency.is_active" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs">
                        <option value="1">نشطة</option>
                        <option value="0">معطلة</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-700 pt-1">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" :checked="editCurrency.is_default == 1" class="rounded text-primary-600">
                        <span>العملة الافتراضية للنظام</span>
                    </label>
                </div>
                <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700">
                    تحديث بيانات العملة
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
