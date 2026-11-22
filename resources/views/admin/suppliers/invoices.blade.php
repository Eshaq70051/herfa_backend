@extends('admin.layout')

@section('title', 'فواتير الشراء')
@section('page_title', 'فواتير الشراء والمشتريات (Purchase Invoices)')

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showStatusModal: false,
    showViewModal: false,
    selectedInvoice: {},
    detailedInvoice: null,
    statusUrl: '',
    viewInvoice(inv) {
        this.detailedInvoice = inv;
        this.showViewModal = true;
    }
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

    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">سجل فواتير المشتريات والمواد</h3>
            <p class="text-xs text-slate-400">الفواتير الصادرة من الموردين المعتمدين لقطع الغيار والمواد الأولية</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.suppliers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-truck-field"></i>
                <span>دليل الموردين</span>
            </a>
            <button @click="showAddModal = true" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>تسجيل فاتورة شراء جديدة</span>
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم الفاتورة</th>
                        <th class="py-3.5 px-4">المورد المعتمد</th>
                        <th class="py-3.5 px-4">طلب الصيانة المرتبط</th>
                        <th class="py-3.5 px-4">تاريخ الفاتورة</th>
                        <th class="py-3.5 px-4">المبلغ الإجمالي</th>
                        <th class="py-3.5 px-4">حالة الفاتورة</th>
                        <th class="py-3.5 px-4">حالة السداد</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-primary-700 font-mono">#{{ $inv->invoice_number }}</td>
                            <td class="py-3.5 px-4 font-sans font-bold text-slate-800">{{ $inv->supplier->name ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-sans">
                                @if($inv->serviceRequest)
                                    <a href="{{ route('admin.orders.show', $inv->service_request_id) }}" class="text-blue-600 font-bold hover:underline font-mono">
                                        {{ $inv->serviceRequest->request_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400 font-mono">#{{ $inv->service_request_id }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">{{ $inv->invoice_date }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ number_format($inv->total_amount, 2) }} {{ $inv->currency->code ?? 'ر.ي' }}</td>
                            <td class="py-3.5 px-4 font-sans">
                                @if($inv->status === 'PAID')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">مدفوعة (PAID)</span>
                                @elseif($inv->status === 'APPROVED')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">معتمدة (APPROVED)</span>
                                @elseif($inv->status === 'CANCELLED')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-200">ملغاة (CANCELLED)</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">مسودة (DRAFT)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-sans">
                                @if($inv->payment_status === 'PAID')
                                    <span class="text-emerald-600 font-bold text-xs"><i class="fa-solid fa-check-circle"></i> مسدد</span>
                                @elseif($inv->payment_status === 'PARTIAL')
                                    <span class="text-amber-600 font-bold text-xs">سداد جزئي</span>
                                @else
                                    <span class="text-slate-400 font-bold text-xs">غير مسدد</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-sans">
                                <div class="inline-flex items-center gap-1.5">
                                    <button @click="viewInvoice({{ json_encode($inv->load('invoiceItems')) }})" class="p-1.5 px-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold transition flex items-center gap-1" title="عرض تفاصيل الفاتورة">
                                        <i class="fa-solid fa-eye text-primary-600 text-[11px]"></i>
                                        <span>تفاصيل</span>
                                    </button>

                                    <button @click="selectedInvoice = {
                                        id: {{ $inv->id }},
                                        number: '{{ $inv->invoice_number }}',
                                        status: '{{ $inv->status }}'
                                    }; statusUrl = '{{ route('admin.suppliers.invoices.status', $inv->id) }}'; showStatusModal = true" class="p-1.5 px-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 text-xs font-bold transition flex items-center gap-1" title="تعديل الحالة">
                                        <i class="fa-solid fa-arrows-rotate text-[11px]"></i>
                                        <span>الحالة</span>
                                    </button>

                                    @if($inv->status !== 'POSTED')
                                        <form action="{{ route('admin.suppliers.invoices.destroy', $inv->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف الفاتورة">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 font-sans">لا توجد فواتير شراء مسجلة بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="border-t border-slate-100 p-4">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: Add Purchase Invoice -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showAddModal = false" class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-primary-600"></i>
                    <span>تسجيل فاتورة شراء جديدة</span>
                </h4>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.suppliers.invoices.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">المورد المعتمد</label>
                    <select name="supplier_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-primary-500">
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">طلب الصيانة المرتبط</label>
                    <select name="service_request_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-primary-500">
                        @foreach($serviceRequests as $sr)
                            <option value="{{ $sr->id }}">طلب: {{ $sr->request_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">رقم الفاتورة</label>
                        <input type="text" name="invoice_number" required value="PINV-{{ time() }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:outline-none focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ الفاتورة</label>
                        <input type="date" name="invoice_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-primary-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">المبلغ الإجمالي</label>
                        <input type="number" step="0.01" name="total_amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:outline-none focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">العملة</label>
                        <select name="currency_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-primary-500">
                            @foreach($currencies as $c)
                                <option value="{{ $c->id }}" {{ $c->is_default ? 'selected' : '' }}>{{ $c->code }} - {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-primary-600/20">
                    حفظ فاتورة الشراء
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL: Update Invoice Status -->
    <div x-show="showStatusModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showStatusModal = false" class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تحديث حالة الفاتورة #<span x-text="selectedInvoice.number"></span></h4>
                <button @click="showStatusModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="statusUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">حالة الفاتورة *</label>
                    <select name="status" x-model="selectedInvoice.status" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <option value="DRAFT">مسودة (DRAFT)</option>
                        <option value="POSTED">مرحلة ومعتمدة (POSTED)</option>
                        <option value="CANCELLED">ملغاة (CANCELLED)</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs transition shadow-md">
                    حفظ التغييرات
                </button>
            </form>
        </div>
    </div>

    {{-- نافذة عرض تفاصيل فاتورة الشراء والطباعة --}}
    <div x-show="showViewModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-xl shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto custom-scrollbar" @click.outside="showViewModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-primary-600 text-lg"></i>
                    <h4 class="font-bold text-slate-800 text-sm">تفاصيل فاتورة الشراء</h4>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1">
                        <i class="fa-solid fa-print"></i>
                        <span>طباعة</span>
                    </button>
                    <button @click="showViewModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <template x-if="detailedInvoice">
                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                        <div>
                            <span class="text-slate-400 block mb-0.5">رقم الفاتورة:</span>
                            <span class="font-mono font-bold text-primary-700 text-sm" x-text="'#' + detailedInvoice.invoice_number"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">تاريخ الفاتورة:</span>
                            <span class="font-mono font-bold text-slate-800" x-text="detailedInvoice.invoice_date"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">المورد المعتمد:</span>
                            <span class="font-bold text-slate-800" x-text="detailedInvoice.supplier ? detailedInvoice.supplier.name : '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">طلب الصيانة:</span>
                            <span class="font-mono font-bold text-blue-600" x-text="detailedInvoice.service_request ? '#' + detailedInvoice.service_request.request_number : '#' + detailedInvoice.service_request_id"></span>
                        </div>
                    </div>

                    {{-- بنود الفاتورة --}}
                    <div class="border border-slate-200 rounded-2xl overflow-hidden">
                        <div class="bg-slate-100/70 p-2.5 font-bold text-slate-700 border-b border-slate-200">
                            بنود المواد وقطع الغيار
                        </div>
                        <table class="w-full text-right text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-2 px-3">البند / المادة</th>
                                    <th class="py-2 px-3 text-center">الكمية</th>
                                    <th class="py-2 px-3 text-left">سعر الوحدة</th>
                                    <th class="py-2 px-3 text-left">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-mono">
                                <template x-for="item in (detailedInvoice.invoice_items || [])" :key="item.id">
                                    <tr>
                                        <td class="py-2 px-3 font-sans font-bold text-slate-800" x-text="item.item_name"></td>
                                        <td class="py-2 px-3 text-center" x-text="item.quantity"></td>
                                        <td class="py-2 px-3 text-left" x-text="parseFloat(item.unit_price).toFixed(2) + ' ر.ي'"></td>
                                        <td class="py-2 px-3 text-left font-bold text-slate-900" x-text="parseFloat(item.line_total).toFixed(2) + ' ر.ي'"></td>
                                    </tr>
                                </template>
                                <tr x-show="!detailedInvoice.invoice_items || detailedInvoice.invoice_items.length === 0">
                                    <td colspan="4" class="py-4 text-center font-sans text-slate-400">
                                        فاتورة إجمالية بمبلغ مقطوع
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-50 border-t border-slate-200 font-bold font-mono">
                                <tr>
                                    <td colspan="3" class="py-2.5 px-3 text-left font-sans text-slate-700">المبلغ الإجمالي للفاتورة:</td>
                                    <td class="py-2.5 px-3 text-left text-primary-700 text-sm" x-text="parseFloat(detailedInvoice.total_amount).toFixed(2) + ' ر.ي'"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </template>
        </div>
    </div>
        </div>
    </div>
</div>
@endsection
