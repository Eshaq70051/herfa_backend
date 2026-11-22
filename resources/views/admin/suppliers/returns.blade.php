@extends('admin.layout')

@section('title', 'مردودات المشتريات')
@section('page_title', 'مردودات المشتريات والمواد التالفة (Purchase Returns & Credits)')

@section('content')
<div class="space-y-6" x-data="{
    showReturnModal: false,
    showViewModal: false,
    selectedReturn: null,
    viewReturn(ret) {
        this.selectedReturn = ret;
        this.showViewModal = true;
    }
}">

    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">سجل مردودات المشتريات للموردين</h3>
            <p class="text-xs text-slate-400">إرجاع قطع الغيار والمواد الأولية التالفة أو الزائدة واسترداد قيمتها</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.suppliers.invoices') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-file-invoice"></i>
                <span>فواتير الشراء</span>
            </a>
            <button @click="showReturnModal = true" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2 shadow-md">
                <i class="fa-solid fa-plus"></i>
                <span>تسجيل إشعار مردودات جديد</span>
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم إشعار المردود</th>
                        <th class="py-3.5 px-4">المورد</th>
                        <th class="py-3.5 px-4">فاتورة الشراء الأصلية</th>
                        <th class="py-3.5 px-4">إجمالي قيمة المردود</th>
                        <th class="py-3.5 px-4">سبب الإرجاع</th>
                        <th class="py-3.5 px-4">تاريخ المردود</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($returns as $ret)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-rose-600">#{{ $ret->return_number }}</td>
                            <td class="py-3.5 px-4 font-sans font-bold text-slate-800">
                                {{ $ret->supplier?->name ?? 'مورد' }}
                            </td>
                            <td class="py-3.5 px-4 font-sans font-bold text-brand-700">
                                #{{ $ret->purchaseInvoice?->invoice_number ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-rose-600 bg-rose-50/40">
                                {{ number_format($ret->total_amount, 2) }} {{ $ret->currency?->code ?? 'ر.ي' }}
                            </td>
                            <td class="py-3.5 px-4 font-sans text-slate-600">
                                {{ $ret->reason }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                                {{ $ret->return_date }}
                            </td>
                            <td class="py-3.5 px-4 font-sans">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $ret->status === 'POSTED' ? 'مرحل ومخصوم' : $ret->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-sans">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click="viewReturn({{ json_encode($ret) }})" class="p-1.5 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1" title="عرض تفاصيل الإشعار">
                                        <i class="fa-solid fa-file-lines text-rose-600"></i>
                                        <span>تفاصيل</span>
                                    </button>
                                    <form action="{{ route('admin.suppliers.returns.destroy', $ret->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف إشعار المردود؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 font-sans">
                                لا توجد إشعارات مردودات مشتريات مسجلة حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $returns->links() }}
            </div>
        @endif
    </div>

    <!-- Add Return Modal -->
    <div x-show="showReturnModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تسجيل إشعار مردودات مشتريات جديد</h4>
                <button @click="showReturnModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.suppliers.returns.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">المورد</label>
                    <select name="supplier_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <option value="">اختر المورد...</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- حقل اختيار فاتورة الشراء مع ميزة البحث الذكي المباشر --}}
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: '',
                    invoices: [
                        @foreach($invoices as $inv)
                            { id: '{{ $inv->id }}', number: '#{{ $inv->invoice_number }}', supplier: '{{ addslashes($inv->supplier?->name ?? '') }}', total: '{{ number_format($inv->total_amount, 2) }} ر.ي' },
                        @endforeach
                    ],
                    get filtered() {
                        if (!this.search) return this.invoices;
                        const q = this.search.toLowerCase();
                        return this.invoices.filter(i => i.number.toLowerCase().includes(q) || i.supplier.toLowerCase().includes(q));
                    },
                    select(i) {
                        this.selectedId = i.id;
                        this.search = i.number + ' - ' + i.supplier + ' (' + i.total + ')';
                        this.open = false;
                    }
                }" class="relative">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">فاتورة الشراء المرتبطة * (اكتب للاقتراح المباشر)</label>
                    <input type="hidden" name="purchase_invoice_id" :value="selectedId" required>
                    <div class="relative">
                        <input type="text"
                               x-model="search"
                               @focus="open = true"
                               @click.outside="open = false"
                               placeholder="اكتب رقم الفاتورة أو اسم المورد..."
                               class="w-full pl-8 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                        <button type="button" x-show="selectedId" @click="selectedId = ''; search = ''; open = true" class="absolute left-3 top-2.5 text-slate-400 hover:text-red-500 text-xs">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div x-show="open" x-cloak class="absolute z-50 mt-1 max-h-48 w-full overflow-y-auto rounded-xl bg-white p-1.5 shadow-xl border border-slate-100 text-xs space-y-1">
                        <template x-for="i in filtered" :key="i.id">
                            <div @click="select(i)" class="flex items-center justify-between p-2 rounded-lg cursor-pointer hover:bg-primary-50 hover:text-primary-900 transition">
                                <div>
                                    <span class="font-bold text-slate-800" x-text="i.number"></span>
                                    <span class="text-[11px] text-slate-500 block" x-text="i.supplier"></span>
                                </div>
                                <span class="text-[11px] font-mono font-bold text-slate-700" x-text="i.total"></span>
                            </div>
                        </template>
                        <div x-show="filtered.length === 0" class="p-3 text-center text-slate-400 text-xs">
                            لا توجد فاتورة مطابقة للاسم أو الرقم
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">قيمة المردود</label>
                        <input type="number" step="0.01" name="total_amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">العملة</label>
                        <select name="currency_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            @foreach($currencies as $curr)
                                <option value="{{ $curr->id }}" {{ $curr->is_default ? 'selected' : '' }}>{{ $curr->name }} ({{ $curr->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ الإرجاع</label>
                    <input type="date" name="return_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">سبب الإرجاع</label>
                    <input type="text" name="reason" required placeholder="مثال: قطع غيار غير مطابقة للمواصفات أو تالفة" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ملاحظات إضافية</label>
                    <textarea name="notes" rows="2" placeholder="ملاحظات المحاسبة..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showReturnModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs shadow-md">ترحيل إشعار المردود</button>
                </div>
            </form>
        </div>
    </div>

    {{-- نافذة عرض وطباعة إشعار مردودات المشتريات --}}
    <div x-show="showViewModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4" @click.outside="showViewModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-rose-600 text-lg"></i>
                    <h4 class="font-bold text-slate-800 text-sm">إشعار مردودات مشتريات (Debit Note)</h4>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-print text-rose-600"></i>
                        <span>طباعة الإشعار</span>
                    </button>
                    <button @click="showViewModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <template x-if="selectedReturn">
                <div class="space-y-4 text-xs">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-slate-500">رقم إشعار المردود:</span>
                            <span class="font-mono font-bold text-rose-600 text-sm" x-text="'#' + selectedReturn.return_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">المورد:</span>
                            <span class="font-bold text-slate-800" x-text="selectedReturn.supplier ? selectedReturn.supplier.name : 'مورد'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">فاتورة الشراء الأصلية:</span>
                            <span class="font-mono font-bold text-primary-700" x-text="selectedReturn.purchase_invoice ? '#' + selectedReturn.purchase_invoice.invoice_number : (selectedReturn.purchase_invoice_id ? '#' + selectedReturn.purchase_invoice_id : '-')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">سبب الإرجاع:</span>
                            <span class="font-semibold text-slate-700" x-text="selectedReturn.reason || 'مواد غير مطابقة'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">تاريخ الإرجاع:</span>
                            <span class="font-mono text-slate-700" x-text="selectedReturn.return_date || '-'"></span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 font-mono">
                            <span class="text-rose-600 font-bold">قيمة المردود الإجمالية:</span>
                            <span class="font-bold text-rose-600 text-base" x-text="parseFloat(selectedReturn.total_amount).toFixed(2) + ' ر.ي'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">حالة الترحيل:</span>
                            <span class="font-bold text-emerald-700" x-text="selectedReturn.status === 'POSTED' ? 'مرحل ومخصوم من حساب المورد' : selectedReturn.status"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-100 text-center text-[11px] text-slate-500">
                        <div class="space-y-1">
                            <span>توقيع مسؤول المستودع / المشتريات</span>
                            <div class="h-10 border-b border-dashed border-slate-300"></div>
                        </div>
                        <div class="space-y-1">
                            <span>استلام وختم مندوب المورد</span>
                            <div class="h-10 border-b border-dashed border-slate-300"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
