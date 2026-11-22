@extends('admin.layout')

@section('title', 'صرف الدفعات والسحوبات')
@section('page_title', 'صرف مستحقات الحرفيين')

@section('content')
<div class="space-y-6" x-data="{ 
    showDirectModal: false, 
    showProcessModal: false, 
    showViewModal: false,
    selectedPayout: null,
    selectedPayoutId: '', 
    selectedPayoutNumber: '', 
    selectedPayoutAmount: '',
    viewPayout(pay) {
        this.selectedPayout = pay;
        this.showViewModal = true;
    }
}">

    {{-- رأس الصفحة وأزرار الإجراءات --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">سجل طلبات الصرف والدفعات</h3>
            <p class="text-xs text-slate-400">تحويل مستحقات الحرفيين عبر البنوك والمحافظ الإلكترونية بالريال اليمني</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showDirectModal = true" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-md">
                <i class="fa-solid fa-plus"></i>
                <span>تسجيل دفعة صرف جديدة</span>
            </button>
            <form method="GET" action="{{ route('admin.accounting.payouts') }}">
                <select name="status" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    <option value="">جميع الحالات</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>بانتظار الصرف</option>
                    <option value="CONFIRMED" {{ request('status') == 'CONFIRMED' ? 'selected' : '' }}>تم الصرف</option>
                </select>
            </form>
        </div>
    </div>

    {{-- جدول سندات الصرف --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم السحب</th>
                        <th class="py-3.5 px-4">الحرفي المستفيد</th>
                        <th class="py-3.5 px-4">المبلغ</th>
                        <th class="py-3.5 px-4">طريقة التحويل</th>
                        <th class="py-3.5 px-4">الحساب المصروف منه</th>
                        <th class="py-3.5 px-4">رقم المرجع / الحوالة</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payouts as $pay)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-primary-700">#{{ $pay->payout_number }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $pay->craftsmanUser->full_name ?? 'حرفي' }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800 bg-slate-50">
                                {{ number_format($pay->amount, 2) }} {{ $pay->currency->symbol ?? 'ر.ي' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">{{ $pay->paymentMethod->name ?? 'تحويل بنكي' }}</td>
                            <td class="py-3.5 px-4 text-slate-700 font-mono">{{ $pay->account?->account_code ?? '' }} - {{ $pay->account?->account_name ?? 'الخزينة' }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $pay->reference_number ?? '-' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $pay->status === 'CONFIRMED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $pay->status === 'CONFIRMED' ? 'مؤكد ومنفذ' : 'بانتظار الصرف' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button @click="viewPayout({{ json_encode($pay) }})" class="p-1.5 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1" title="عرض تفاصيل السند">
                                        <i class="fa-solid fa-receipt text-primary-600"></i>
                                        <span>تفاصيل السند</span>
                                    </button>
                                    @if($pay->status === 'PENDING')
                                        <button @click="selectedPayoutId = '{{ $pay->id }}'; selectedPayoutNumber = '{{ $pay->payout_number }}'; selectedPayoutAmount = '{{ $pay->amount }}'; showProcessModal = true" class="px-3 py-1 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-bold transition">
                                            تسجيل الصرف
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                لا توجد طلبات صرف مسجلة حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payouts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payouts->links() }}
            </div>
        @endif
    </div>

    {{-- نافذة تأكيد وتنفيذ الصرف --}}
    <div x-show="showProcessModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4" @click.outside="showProcessModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تأكيد تحويل وصرف الدفعة</h4>
                <button @click="showProcessModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/accounting/payouts') }}/' + selectedPayoutId + '/process'" method="POST" class="space-y-4">
                @csrf
                <div class="bg-primary-50 p-3 rounded-xl border border-primary-100 text-xs text-primary-900">
                    رقم السند: <span class="font-mono font-bold" x-text="'#' + selectedPayoutNumber"></span> - المبلغ: <span class="font-bold" x-text="selectedPayoutAmount"></span> ر.ي
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رقم الحوالة أو السند المالي *</label>
                    <input type="text" name="reference_number" required placeholder="مثال: TRX-8849302" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:outline-none focus:border-primary-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ملاحظات التحويل</label>
                    <textarea name="notes" rows="3" placeholder="ملاحظات الصرف والتحويل..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-primary-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showProcessModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition">تأكيد الصرف</button>
                </div>
            </form>
        </div>
    </div>

    {{-- نافذة تسجيل سند صرف مباشر مع حقول بحث فورية --}}
    <div x-show="showDirectModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4" @click.outside="showDirectModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تسجيل سند صرف وتحويل لحرفي</h4>
                <button @click="showDirectModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.accounting.payouts.store') }}" method="POST" class="space-y-4">
                @csrf

                {{-- حقل اختيار الحرفي مع ميزة الكتابة والبحث الفوري --}}
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: '',
                    craftsmen: [
                        @foreach($craftsmen ?? [] as $cr)
                            { id: '{{ $cr->id }}', name: '{{ addslashes($cr->full_name) }}', phone: '{{ $cr->phone ?? '' }}' },
                        @endforeach
                    ],
                    get filtered() {
                        if (!this.search) return this.craftsmen;
                        const q = this.search.toLowerCase();
                        return this.craftsmen.filter(c => c.name.toLowerCase().includes(q) || c.phone.includes(q));
                    },
                    select(c) {
                        this.selectedId = c.id;
                        this.search = c.name + (c.phone ? ' (' + c.phone + ')' : '');
                        this.open = false;
                    }
                }" class="relative">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الحرفي المستفيد * (اكتب للاقتراح المباشر)</label>
                    <input type="hidden" name="craftsman_user_id" :value="selectedId" required>
                    <div class="relative">
                        <input type="text"
                               x-model="search"
                               @focus="open = true"
                               @click.outside="open = false"
                               placeholder="اكتب اسم الحرفي أو رقم هاتفه..."
                               class="w-full pl-8 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                        <button type="button" x-show="selectedId" @click="selectedId = ''; search = ''; open = true" class="absolute left-3 top-2.5 text-slate-400 hover:text-red-500 text-xs">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div x-show="open" x-cloak class="absolute z-50 mt-1 max-h-48 w-full overflow-y-auto rounded-xl bg-white p-1.5 shadow-xl border border-slate-100 text-xs space-y-1">
                        <template x-for="c in filtered" :key="c.id">
                            <div @click="select(c)" class="flex items-center justify-between p-2 rounded-lg cursor-pointer hover:bg-primary-50 hover:text-primary-900 transition">
                                <span class="font-bold text-slate-800" x-text="c.name"></span>
                                <span class="text-[11px] font-mono text-slate-400" x-text="c.phone"></span>
                            </div>
                        </template>
                        <div x-show="filtered.length === 0" class="p-3 text-center text-slate-400 text-xs">
                            لا يوجد حرفي مطابق للاسم المكتوب
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">المبلغ المصروف *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">العملة *</label>
                        <select name="currency_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            @foreach($currencies ?? [] as $curr)
                                <option value="{{ $curr->id }}" {{ $curr->is_default ? 'selected' : '' }}>{{ $curr->name }} ({{ $curr->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">طريقة الصرف *</label>
                        <select name="payment_method_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- حقل اختيار الحساب مع ميزة البحث والكتابة --}}
                    <div x-data="{
                        open: false,
                        search: '',
                        selectedId: '',
                        accounts: [
                            @foreach($accounts as $acc)
                                { id: '{{ $acc->id }}', name: '{{ addslashes($acc->account_name) }}', code: '{{ $acc->account_code }}' },
                            @endforeach
                        ],
                        get filtered() {
                            if (!this.search) return this.accounts;
                            const q = this.search.toLowerCase();
                            return this.accounts.filter(a => a.name.toLowerCase().includes(q) || a.code.toLowerCase().includes(q));
                        },
                        select(a) {
                            this.selectedId = a.id;
                            this.search = a.code + ' - ' + a.name;
                            this.open = false;
                        }
                    }" class="relative">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">الحساب المالي المصروف منه *</label>
                        <input type="hidden" name="account_id" :value="selectedId" required>
                        <div class="relative">
                            <input type="text"
                                   x-model="search"
                                   @focus="open = true"
                                   @click.outside="open = false"
                                   placeholder="ابحث بالحساب أو الكود..."
                                   class="w-full pl-8 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                            <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                            <button type="button" x-show="selectedId" @click="selectedId = ''; search = ''; open = true" class="absolute left-3 top-2.5 text-slate-400 hover:text-red-500 text-xs">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div x-show="open" x-cloak class="absolute z-50 mt-1 max-h-48 w-full overflow-y-auto rounded-xl bg-white p-1.5 shadow-xl border border-slate-100 text-xs space-y-1">
                            <template x-for="a in filtered" :key="a.id">
                                <div @click="select(a)" class="flex items-center justify-between p-2 rounded-lg cursor-pointer hover:bg-primary-50 hover:text-primary-900 transition">
                                    <span class="font-bold text-slate-800" x-text="a.name"></span>
                                    <span class="text-[11px] font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-600" x-text="a.code"></span>
                                </div>
                            </template>
                            <div x-show="filtered.length === 0" class="p-3 text-center text-slate-400 text-xs">
                                لا يوجد حساب مطابق
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رقم الحوالة أو السند المالي</label>
                    <input type="text" name="reference_number" placeholder="مثال: TRX-8849302" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ملاحظات</label>
                    <textarea name="notes" rows="2" placeholder="ملاحظات الصرف والتحويل..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showDirectModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition">حفظ وتأكيد السند</button>
                </div>
            </form>
        </div>
    </div>

    {{-- نافذة عرض وطباعة سند صرف مستحقات الحرفي --}}
    <div x-show="showViewModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4" @click.outside="showViewModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-primary-600 text-lg"></i>
                    <h4 class="font-bold text-slate-800 text-sm">سند صرف مستحقات مهني / حرفي</h4>
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

            <template x-if="selectedPayout">
                <div class="space-y-4 text-xs">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-slate-500">رقم سند الصرف:</span>
                            <span class="font-mono font-bold text-primary-700 text-sm" x-text="'#' + selectedPayout.payout_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">الحرفي المستفيد:</span>
                            <span class="font-bold text-slate-800" x-text="selectedPayout.craftsman_user ? selectedPayout.craftsman_user.full_name : 'حرفي'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">طريقة التحويل / الصرف:</span>
                            <span class="font-semibold text-slate-700" x-text="selectedPayout.payment_method ? selectedPayout.payment_method.name : 'تحويل بنكي / صرافة'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">الحساب المخصوم منه:</span>
                            <span class="font-mono font-semibold text-slate-700" x-text="selectedPayout.account ? (selectedPayout.account.account_code + ' - ' + selectedPayout.account.account_name) : 'الخزينة العامة'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">رقم الحوالة / المرجع:</span>
                            <span class="font-mono font-bold text-slate-800" x-text="selectedPayout.reference_number || '-'"></span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 font-mono">
                            <span class="text-primary-700 font-bold">المبلغ المصروف:</span>
                            <span class="font-bold text-primary-700 text-base" x-text="parseFloat(selectedPayout.amount).toFixed(2) + ' ر.ي'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">حالة السند:</span>
                            <span class="font-bold" :class="selectedPayout.status === 'CONFIRMED' ? 'text-emerald-700' : 'text-amber-600'" x-text="selectedPayout.status === 'CONFIRMED' ? 'مؤكد ومنفذ' : 'بانتظار الصرف'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-100 text-center text-[11px] text-slate-500">
                        <div class="space-y-1">
                            <span>توقيع أمين الصندوق / المحاسب</span>
                            <div class="h-10 border-b border-dashed border-slate-300"></div>
                        </div>
                        <div class="space-y-1">
                            <span>توقيع المستلم / الحرفي</span>
                            <div class="h-10 border-b border-dashed border-slate-300"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
