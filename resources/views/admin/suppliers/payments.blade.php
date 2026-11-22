@extends('admin.layout')

@section('title', 'مدفوعات وسداد الموردين')
@section('page_title', 'سندات صرف ومدفوعات الموردين (Supplier Payments & Disbursements)')

@section('content')
<div class="space-y-6" x-data="{
    showPaymentModal: false,
    showViewModal: false,
    selectedPayment: null,
    viewPayment(pay) {
        this.selectedPayment = pay;
        this.showViewModal = true;
    }
}">

    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">سجل مدفوعات وسندات صرف الموردين</h3>
            <p class="text-xs text-slate-400">سداد فواتير شراء المواد وقطع الغيار عبر التحويلات البنكية والمحافظ</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.suppliers.invoices') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-file-invoice"></i>
                <span>فواتير الشراء</span>
            </a>
            <button @click="showPaymentModal = true" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2 shadow-md">
                <i class="fa-solid fa-plus"></i>
                <span>تسجيل سند صرف لمورد</span>
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم السند</th>
                        <th class="py-3.5 px-4">المورد المستفيد</th>
                        <th class="py-3.5 px-4">المبلغ المسدد</th>
                        <th class="py-3.5 px-4">طريقة الدفع</th>
                        <th class="py-3.5 px-4">الحساب المصروف منه</th>
                        <th class="py-3.5 px-4">رقم المرجع / الحوالة</th>
                        <th class="py-3.5 px-4">تاريخ الصرف</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($payments as $pay)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-brand-700">#{{ $pay->payment_number }}</td>
                            <td class="py-3.5 px-4 font-sans font-bold text-slate-800">
                                {{ $pay->supplier?->name ?? 'مورد' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-700 bg-emerald-50/40">
                                {{ number_format($pay->amount, 2) }} {{ $pay->currency?->code ?? 'ر.ي' }}
                            </td>
                            <td class="py-3.5 px-4 font-sans text-slate-600">
                                {{ $pay->paymentMethod?->name ?? 'تحويل بنكي' }}
                            </td>
                            <td class="py-3.5 px-4 font-sans text-slate-700">
                                {{ $pay->account?->account_code ?? '' }} - {{ $pay->account?->account_name ?? 'الخزينة' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 font-mono">
                                {{ $pay->reference_number ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                                {{ $pay->payment_date }}
                            </td>
                            <td class="py-3.5 px-4 font-sans">
                                @if($pay->status === 'CONFIRMED')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">مصروف ومؤكد</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">قيد التنفيذ</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-sans">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click="viewPayment({{ json_encode($pay) }})" class="p-1.5 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1" title="عرض تفاصيل السند">
                                        <i class="fa-solid fa-receipt text-primary-600"></i>
                                        <span>تفاصيل السند</span>
                                    </button>
                                    @if($pay->status !== 'CONFIRMED')
                                        <form action="{{ route('admin.suppliers.payments.confirm', $pay->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                                تأكيد الصرف
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.suppliers.payments.destroy', $pay->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف سند الصرف؟')">
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
                            <td colspan="9" class="py-12 text-center text-slate-400 font-sans">
                                لا توجد سندات صرف أو مدفوعات موردين مسجلة بعد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    <!-- Add Payment Modal -->
    <div x-show="showPaymentModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تسجيل سند صرف وسداد لمورد</h4>
                <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.suppliers.payments.store') }}" method="POST" class="space-y-4">
                @csrf
                {{-- حقل اختيار المورد مع ميزة البحث الذكي الفوري بمجرد الكتابة --}}
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: '',
                    suppliers: [
                        @foreach($suppliers as $s)
                            { id: '{{ $s->id }}', name: '{{ addslashes($s->name) }}', phone: '{{ $s->phone ?? '' }}' },
                        @endforeach
                    ],
                    get filtered() {
                        if (!this.search) return this.suppliers;
                        const q = this.search.toLowerCase();
                        return this.suppliers.filter(s => s.name.toLowerCase().includes(q) || s.phone.includes(q));
                    },
                    select(s) {
                        this.selectedId = s.id;
                        this.search = s.name + (s.phone ? ' (' + s.phone + ')' : '');
                        this.open = false;
                    }
                }" class="relative">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">المورد المستفيد * (اكتب للاقتراح المباشر)</label>
                    <input type="hidden" name="supplier_id" :value="selectedId" required>
                    <div class="relative">
                        <input type="text"
                               x-model="search"
                               @focus="open = true"
                               @click.outside="open = false"
                               placeholder="اكتب اسم المورد أو هاتفه..."
                               class="w-full pl-8 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                        <button type="button" x-show="selectedId" @click="selectedId = ''; search = ''; open = true" class="absolute left-3 top-2.5 text-slate-400 hover:text-red-500 text-xs">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div x-show="open" x-cloak class="absolute z-50 mt-1 max-h-48 w-full overflow-y-auto rounded-xl bg-white p-1.5 shadow-xl border border-slate-100 text-xs space-y-1">
                        <template x-for="s in filtered" :key="s.id">
                            <div @click="select(s)" class="flex items-center justify-between p-2 rounded-lg cursor-pointer hover:bg-primary-50 hover:text-primary-900 transition">
                                <span class="font-bold text-slate-800" x-text="s.name"></span>
                                <span class="text-[11px] font-mono text-slate-400" x-text="s.phone"></span>
                            </div>
                        </template>
                        <div x-show="filtered.length === 0" class="p-3 text-center text-slate-400 text-xs">
                            لا يوجد مورد مطابق للاسم المكتوب
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">المبلغ المسدد</label>
                        <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ الصرف</label>
                        <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">طريقة الدفع</label>
                        <select name="payment_method_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
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
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الحساب المالي المصروف منه</label>
                    <select name="account_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رقم المرجع / الحوالة البنكية</label>
                    <input type="text" name="reference_number" placeholder="مثال: REF-384910" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ملاحظات وبيان الصرف</label>
                    <textarea name="notes" rows="2" placeholder="ملاحظات وسندات الاستلام..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showPaymentModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs shadow-md">تسجيل وتأكيد الصرف</button>
                </div>
            </form>
        </div>
    </div>

    {{-- نافذة تفاصيل وطباعة سند صرف المورد --}}
    <div x-show="showViewModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4" @click.outside="showViewModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-primary-600 text-lg"></i>
                    <h4 class="font-bold text-slate-800 text-sm">سند صرف وسداد لمورد</h4>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-print text-primary-600"></i>
                        <span>طباعة السند</span>
                    </button>
                    <button @click="showViewModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <template x-if="selectedPayment">
                <div class="space-y-4 text-xs">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-slate-500">رقم سند الصرف:</span>
                            <span class="font-mono font-bold text-primary-700 text-sm" x-text="'#' + selectedPayment.payment_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">المورد المستفيد:</span>
                            <span class="font-bold text-slate-800" x-text="selectedPayment.supplier ? selectedPayment.supplier.name : 'مورد'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">طريقة الدفع:</span>
                            <span class="font-semibold text-slate-700" x-text="selectedPayment.payment_method ? selectedPayment.payment_method.name : 'تحويل بنكي / صرافة'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">الحساب المصروف منه:</span>
                            <span class="font-mono font-semibold text-slate-700" x-text="selectedPayment.account ? (selectedPayment.account.account_code + ' - ' + selectedPayment.account.account_name) : 'الخزينة'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">رقم المرجع / الحوالة:</span>
                            <span class="font-mono font-bold text-slate-800" x-text="selectedPayment.reference_number || '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">تاريخ الصرف:</span>
                            <span class="font-mono text-slate-700" x-text="selectedPayment.payment_date || '-'"></span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 font-mono">
                            <span class="text-emerald-700 font-bold">المبلغ المسدد:</span>
                            <span class="font-bold text-emerald-700 text-base" x-text="parseFloat(selectedPayment.amount).toFixed(2) + ' ر.ي'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">حالة السند:</span>
                            <span class="font-bold" :class="selectedPayment.status === 'CONFIRMED' ? 'text-emerald-700' : 'text-amber-600'" x-text="selectedPayment.status === 'CONFIRMED' ? 'مصروف ومؤكد' : 'قيد التنفيذ'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-100 text-center text-[11px] text-slate-500">
                        <div class="space-y-1">
                            <span>توقيع المدير المالي</span>
                            <div class="h-10 border-b border-dashed border-slate-300"></div>
                        </div>
                        <div class="space-y-1">
                            <span>توقيع وختم المورد المستلم</span>
                            <div class="h-10 border-b border-dashed border-slate-300"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
