@extends('admin.layout')

@section('title', 'تسويات وحوالات الحرفيين')
@section('page_title', 'تسويات وحوالات توريد الحرفيين (Craftsmen Settlements & Remittances)')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'settlements',
    showRemittanceModal: false,
    showSettlementModal: false,
    selectedSettlement: null,
    viewSettlement(set) {
        this.selectedSettlement = set;
        this.showSettlementModal = true;
    }
}">

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold block">إجمالي تحصيلات الكاش الميدانية</span>
                <span class="text-lg font-bold font-mono text-slate-800">{{ number_format($totalCollected, 2) }}</span>
                <span class="text-[11px] text-slate-500 mr-1">ر.ي</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold block">مستحقات أرباح الحرفيين المعتمدة</span>
                <span class="text-lg font-bold font-mono text-emerald-600">{{ number_format($totalEarningsApproved, 2) }}</span>
                <span class="text-[11px] text-slate-500 mr-1">ر.ي</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold block">إجمالي عمولة المنصة المستحقة</span>
                <span class="text-lg font-bold font-mono text-brand-600">{{ number_format($totalCompanyCommission, 2) }}</span>
                <span class="text-[11px] text-slate-500 mr-1">ر.ي</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-percent"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold block">إجمالي الحوالات الموردة للخزينة</span>
                <span class="text-lg font-bold font-mono text-purple-600">{{ number_format($totalRemitted, 2) }}</span>
                <span class="text-[11px] text-slate-500 mr-1">ر.ي</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-building-columns"></i>
            </div>
        </div>
    </div>

    <!-- Action Bar & Tabs -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-2 p-1.5 bg-slate-100 rounded-xl">
            <button @click="activeTab = 'settlements'" :class="activeTab === 'settlements' ? 'bg-white text-primary-700 shadow-sm font-bold' : 'text-slate-600 font-semibold'" class="px-5 py-2 rounded-lg text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-scale-balanced"></i>
                <span>تسويات ذمم الحرفيين ({{ $settlements->total() }})</span>
            </button>
            <button @click="activeTab = 'remittances'" :class="activeTab === 'remittances' ? 'bg-white text-primary-700 shadow-sm font-bold' : 'text-slate-600 font-semibold'" class="px-5 py-2 rounded-lg text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <span>حوالات التوريد والسداد ({{ $remittances->total() }})</span>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <button @click="showRemittanceModal = true" class="px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>تسجيل حوالة سداد من حرفي</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: Settlements Table -->
    <div x-show="activeTab === 'settlements'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-800 text-xs">جدول تسوية الذمم والمقاصة بين التحصيلات والأرباح</h4>
                <p class="text-[11px] text-slate-400">حساب صافي المبالغ المستحقة للطرفين وتأكيد التسوية المالية</p>
            </div>
            <form method="GET" action="{{ route('admin.accounting.settlements') }}">
                <select name="settlement_status" onchange="this.form.submit()" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs">
                    <option value="">جميع الحالات</option>
                    <option value="PENDING" {{ request('settlement_status') === 'PENDING' ? 'selected' : '' }}>بانتظار التسوية</option>
                    <option value="REMITTED" {{ request('settlement_status') === 'REMITTED' ? 'selected' : '' }}>تمت التسوية والتوريد</option>
                    <option value="PARTIALLY_REMITTED" {{ request('settlement_status') === 'PARTIALLY_REMITTED' ? 'selected' : '' }}>توريد جزئي</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم الطلب</th>
                        <th class="py-3.5 px-4">الحرفي</th>
                        <th class="py-3.5 px-4">التحصيل النقدي (كاش)</th>
                        <th class="py-3.5 px-4">أرباح الحرفي</th>
                        <th class="py-3.5 px-4">المستحق للمنصة</th>
                        <th class="py-3.5 px-4">تاريخ الاستحقاق</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4">المراجع</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($settlements as $set)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-800 font-sans">
                                #{{ $set->earning?->serviceRequest?->request_number ?? ('REQ-' . $set->id) }}
                            </td>
                            <td class="py-3.5 px-4 font-sans font-bold text-slate-700">
                                {{ $set->earning?->craftsmanUser?->full_name ?? 'حرفي' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-700">
                                {{ number_format($set->collected_amount, 2) }} ر.ي
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-600 bg-emerald-50/30">
                                {{ number_format($set->craftsman_earning_amount, 2) }} ر.ي
                            </td>
                            <td class="py-3.5 px-4 font-bold text-brand-700 bg-brand-50/40">
                                {{ number_format($set->company_due_amount, 2) }} ر.ي
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                                {{ $set->due_at ?? $set->created_at->format('Y-m-d') }}
                            </td>
                            <td class="py-3.5 px-4 font-sans">
                                @if($set->status === 'REMITTED' || $set->status === 'PARTIALLY_REMITTED')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $set->status === 'REMITTED' ? 'تمت التسوية والتوريد' : 'توريد جزئي' }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        بانتظار التسوية
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-sans text-slate-500 text-[11px]">
                                {{ $set->reviewedByUser?->full_name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-sans">
                                <div class="inline-flex items-center gap-1.5">
                                    <button @click="viewSettlement({{ json_encode($set) }})" class="p-1.5 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1" title="عرض تفاصيل التسوية">
                                        <i class="fa-solid fa-eye text-primary-600"></i>
                                        <span>تفاصيل</span>
                                    </button>

                                    @if($set->status !== 'REMITTED')
                                        <form action="{{ route('admin.accounting.settlements.approve', $set->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                                اعتماد
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 text-xs">✓ تمت التسوية</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 font-sans">
                                لا توجد سجلات تسوية مسجلة حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($settlements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $settlements->links() }}
            </div>
        @endif
    </div>

    <!-- TAB 2: Remittances Table -->
    <div x-show="activeTab === 'remittances'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-800 text-xs">سجل حوالات السداد والتوريد من الحرفيين</h4>
                <p class="text-[11px] text-slate-400">الحوالات البنكية والنقدية المودعة لحسابات المنصة لتصفية الذمم</p>
            </div>
            <form method="GET" action="{{ route('admin.accounting.settlements') }}">
                <select name="remittance_status" onchange="this.form.submit()" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs">
                    <option value="">جميع الحالات</option>
                    <option value="CONFIRMED" {{ request('remittance_status') === 'CONFIRMED' ? 'selected' : '' }}>مؤكدة ومودعة</option>
                    <option value="PENDING" {{ request('remittance_status') === 'PENDING' ? 'selected' : '' }}>بانتظار التأكيد</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رقم الحوالة</th>
                        <th class="py-3.5 px-4">الحرفي المورد</th>
                        <th class="py-3.5 px-4">المبلغ المورد</th>
                        <th class="py-3.5 px-4">طريقة التحويل</th>
                        <th class="py-3.5 px-4">الحساب المودع فيه</th>
                        <th class="py-3.5 px-4">رقم المرجع / الإشعار</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4">تاريخ التوريد</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($remittances as $rem)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-brand-700">#{{ $rem->remittance_number }}</td>
                            <td class="py-3.5 px-4 font-sans font-bold text-slate-700">
                                {{ $rem->craftsmanUser?->full_name ?? 'حرفي' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-purple-700 bg-purple-50/50">
                                {{ number_format($rem->amount, 2) }} {{ $rem->currency?->code ?? 'ر.ي' }}
                            </td>
                            <td class="py-3.5 px-4 font-sans text-slate-600">
                                {{ $rem->paymentMethod?->name ?? 'تحويل بنكي' }}
                            </td>
                            <td class="py-3.5 px-4 font-sans text-slate-700">
                                {{ $rem->account?->account_code ?? '' }} - {{ $rem->account?->account_name ?? 'الخزينة' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 font-mono">
                                {{ $rem->reference_number ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-sans">
                                @if($rem->status === 'CONFIRMED')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">مؤكد ومودع</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">بانتظار التأكيد</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                                {{ $rem->remitted_at ?? $rem->created_at->format('Y-m-d') }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-sans">
                                @if($rem->status !== 'CONFIRMED')
                                    <form action="{{ route('admin.accounting.remittances.confirm', $rem->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                            تأكيد الاستلام
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-400 text-xs">✓ مؤكد</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 font-sans">
                                لا توجد حوالات سداد مسجلة حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($remittances->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $remittances->links() }}
            </div>
        @endif
    </div>

    {{-- نافذة تسجيل حوالة سداد من حرفي مع ميزة البحث الذكي المباشر --}}
    <div x-show="showRemittanceModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4" @click.outside="showRemittanceModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تسجيل حوالة سداد نقدية من حرفي</h4>
                <button @click="showRemittanceModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.accounting.remittances.store') }}" method="POST" class="space-y-4">
                @csrf

                {{-- حقل اختيار الحرفي المورد مع البحث الذكي الفوري بمجرد الكتابة --}}
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: '',
                    craftsmen: [
                        @foreach($craftsmen as $cr)
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
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الحرفي المورد * (اكتب للاقتراح المباشر)</label>
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
                        <label class="block text-xs font-semibold text-slate-600 mb-1">المبلغ المورد *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">العملة *</label>
                        <select name="currency_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            @foreach($currencies as $curr)
                                <option value="{{ $curr->id }}" {{ $curr->is_default ? 'selected' : '' }}>{{ $curr->name }} ({{ $curr->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">طريقة السداد / التحويل *</label>
                        <select name="payment_method_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- حقل حساب الإيداع مع البحث الذكي الفوري --}}
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
                        <label class="block text-xs font-semibold text-slate-600 mb-1">حساب الإيداع المالي *</label>
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
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رقم الإشعار / الحوالة البنكية</label>
                    <input type="text" name="reference_number" placeholder="مثال: REF-9948210" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ملاحظات التوريد</label>
                    <textarea name="notes" rows="2" placeholder="ملاحظات التسوية..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showRemittanceModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs shadow-md">تسجيل وتأكيد</button>
                </div>
            </form>
        </div>
    </div>

    {{-- نافذة عرض تفاصيل ومطابقة التسوية --}}
    <div x-show="showSettlementModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4" @click.outside="showSettlementModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-contract text-primary-600 text-lg"></i>
                    <h4 class="font-bold text-slate-800 text-sm">بيانات ومطابقة تسوية الحرفي</h4>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1">
                        <i class="fa-solid fa-print"></i>
                        <span>طباعة</span>
                    </button>
                    <button @click="showSettlementModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <template x-if="selectedSettlement">
                <div class="space-y-4 text-xs">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-slate-500">رقم التسوية:</span>
                            <span class="font-mono font-bold text-primary-700" x-text="'#' + selectedSettlement.settlement_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">الحرفي المستفيد:</span>
                            <span class="font-bold text-slate-800" x-text="selectedSettlement.earning && selectedSettlement.earning.craftsman_user ? selectedSettlement.earning.craftsman_user.full_name : '-'"></span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 font-mono">
                            <span class="text-slate-600">المبلغ المحصل من العميل:</span>
                            <span class="font-bold text-slate-900" x-text="parseFloat(selectedSettlement.collected_amount).toFixed(2) + ' ر.ي'"></span>
                        </div>
                        <div class="flex justify-between font-mono">
                            <span class="text-emerald-700 font-bold">حصة الحرفي المعتمدة:</span>
                            <span class="font-bold text-emerald-700" x-text="parseFloat(selectedSettlement.craftsman_earning_amount).toFixed(2) + ' ر.ي'"></span>
                        </div>
                        <div class="flex justify-between font-mono border-t border-slate-200 pt-2">
                            <span class="text-primary-700 font-bold">المبلغ المستحق للتوريد للشركة:</span>
                            <span class="font-bold text-primary-700 text-sm" x-text="parseFloat(selectedSettlement.company_due_amount).toFixed(2) + ' ر.ي'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">حالة التسوية:</span>
                            <span class="font-bold text-emerald-700" x-text="selectedSettlement.status === 'REMITTED' ? 'تمت التسوية والتوريد' : selectedSettlement.status"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">تاريخ الاستحقاق:</span>
                            <span class="font-mono text-slate-700" x-text="selectedSettlement.due_at || '-'"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
