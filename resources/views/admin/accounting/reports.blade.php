@extends('admin.layout')

@section('title', 'التقارير المالية وميزان المراجعة')
@section('page_title', 'التقارير المحاسبية والقوائم المالية (Financial Statements & Reports)')

@section('content')
<div class="space-y-6" x-data="{
    activeReport: '{{ request('active_tab', request('account_id') ? 'ledger' : 'trial_balance') }}'
}">

    <!-- Top Filters & Report Selector -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4 print:hidden">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">ميزان المراجعة ودفتر الأستاذ العام</h3>
                <p class="text-xs text-slate-400">التحقق من توازن الحسابات المحاسبية وكشوفات الحسابات التفصيلية بالريال اليمني</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.accounting.journal') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-book-journal-whills text-primary-600"></i>
                    <span>دفتر قيود اليومية</span>
                </a>
                <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-print text-primary-600"></i>
                    <span>طباعة التقرير / PDF</span>
                </button>
                <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl">
                    <button @click="activeReport = 'trial_balance'" :class="activeReport === 'trial_balance' ? 'bg-white text-primary-700 shadow-sm font-bold' : 'text-slate-600 font-semibold'" class="px-4 py-2 rounded-lg text-xs transition flex items-center gap-2">
                        <i class="fa-solid fa-scale-balanced"></i>
                        <span>ميزان المراجعة</span>
                    </button>
                    <button @click="activeReport = 'ledger'" :class="activeReport === 'ledger' ? 'bg-white text-primary-700 shadow-sm font-bold' : 'text-slate-600 font-semibold'" class="px-4 py-2 rounded-lg text-xs transition flex items-center gap-2">
                        <i class="fa-solid fa-book-bookmark"></i>
                        <span>دفتر الأستاذ العام</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.accounting.reports') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-3 border-t border-slate-100">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">الفترة المحاسبية</label>
                <select name="period_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    <option value="">جميع الفترات</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->period_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- حقل اختيار الحساب مع ميزة البحث الفوري المباشر بالكتابة --}}
            <div x-data="{
                open: false,
                search: '{{ $selectedAccount ? ($selectedAccount->account_code . ' - ' . addslashes($selectedAccount->account_name)) : '' }}',
                selectedId: '{{ request('account_id', '') }}',
                accounts: [
                    @foreach($accounts as $acc)
                        { id: '{{ $acc->id }}', name: '{{ addslashes($acc->account_name) }}', code: '{{ $acc->account_code }}', type: '{{ $acc->account_type }}' },
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
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">الحساب المحاسبي (اكتب للبحث الفوري)</label>
                <input type="hidden" name="account_id" :value="selectedId">
                <div class="relative">
                    <input type="text"
                           x-model="search"
                           @focus="open = true"
                           @click.outside="open = false"
                           placeholder="اكتب اسم الحساب أو الكود..."
                           class="w-full pl-7 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
                    <button type="button" x-show="selectedId" @click="selectedId = ''; search = ''; open = true" class="absolute left-2.5 top-2 text-slate-400 hover:text-red-500 text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div x-show="open" x-cloak class="absolute z-50 mt-1 max-h-56 w-full overflow-y-auto rounded-xl bg-white p-1.5 shadow-xl border border-slate-100 text-xs space-y-1">
                    <template x-for="a in filtered" :key="a.id">
                        <div @click="select(a)" class="flex items-center justify-between p-2 rounded-lg cursor-pointer hover:bg-primary-50 hover:text-primary-900 transition">
                            <span class="font-bold text-slate-800" x-text="a.name"></span>
                            <span class="text-[10px] font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-600" x-text="a.code"></span>
                        </div>
                    </template>
                    <div x-show="filtered.length === 0" class="p-3 text-center text-slate-400 text-xs">
                        لا يوجد حساب مطابق للبحث
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">من تاريخ</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
            </div>

            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">إلى تاريخ</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 h-[37px]">
                    <i class="fa-solid fa-filter"></i>
                    <span>تطبيق</span>
                </button>
            </div>
        </form>
    </div>

    <!-- REPORT 1: Trial Balance (ميزان المراجعة) -->
    <div x-show="activeReport === 'trial_balance'" class="space-y-4">
        <!-- Balance Status Bar -->
        <div class="p-4 rounded-2xl flex items-center justify-between border {{ $isTrialBalanced ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800' }}">
            <div class="flex items-center gap-3">
                <i class="{{ $isTrialBalanced ? 'fa-solid fa-circle-check text-emerald-600 text-xl' : 'fa-solid fa-triangle-exclamation text-red-600 text-xl' }}"></i>
                <div>
                    <h5 class="font-bold text-sm">{{ $isTrialBalanced ? 'ميزان المراجعة متزن تماماً (إجمالي المدين = إجمالي الدائن)' : 'تنبيه: ميزان المراجعة غير متزن!' }}</h5>
                    <p class="text-xs opacity-80">إجمالي الحركات المدينة: {{ number_format($totalTrialDebit, 2) }} ر.ي | إجمالي الحركات الدائنة: {{ number_format($totalTrialCredit, 2) }} ر.ي</p>
                </div>
            </div>
            <button onclick="window.print()" class="px-3.5 py-1.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl text-xs shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>طباعة الميزان</span>
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                        <tr>
                            <th rowspan="2" class="py-3 px-4 border-l border-slate-200">رمز الحساب</th>
                            <th rowspan="2" class="py-3 px-4 border-l border-slate-200">اسم الحساب المحاسبي</th>
                            <th rowspan="2" class="py-3 px-4 border-l border-slate-200">تصنيف الحساب</th>
                            <th colspan="2" class="py-2 px-4 text-center border-b border-l border-slate-200 bg-slate-50">مجموع الحركات</th>
                            <th colspan="2" class="py-2 px-4 text-center bg-slate-50">الأرصدة الصافية</th>
                        </tr>
                        <tr class="bg-slate-50 text-slate-500">
                            <th class="py-2 px-4 text-left border-l border-slate-200">مدين (Debit)</th>
                            <th class="py-2 px-4 text-left border-l border-slate-200">دائن (Credit)</th>
                            <th class="py-2 px-4 text-left border-l border-slate-200 font-bold text-slate-700">رصيد مدين</th>
                            <th class="py-2 px-4 text-left font-bold text-slate-700">رصيد دائن</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @foreach($accountsWithBalances as $acc)
                            <tr class="hover:bg-slate-50/70 transition {{ $acc->is_control_account ? 'bg-slate-50/30 font-bold' : '' }}">
                                <td class="py-3 px-4 border-l border-slate-100 font-bold text-brand-700">{{ $acc->account_code }}</td>
                                <td class="py-3 px-4 border-l border-slate-100 font-sans font-bold text-slate-800">{{ $acc->account_name }}</td>
                                <td class="py-3 px-4 border-l border-slate-100 font-sans">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ in_array($acc->account_type, ['ASSET', 'EXPENSE']) ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700' }}">
                                        {{ $acc->account_type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 border-l border-slate-100 text-left font-medium text-slate-700">
                                    {{ $acc->total_debit > 0 ? number_format($acc->total_debit, 2) : '-' }}
                                </td>
                                <td class="py-3 px-4 border-l border-slate-100 text-left font-medium text-slate-700">
                                    {{ $acc->total_credit > 0 ? number_format($acc->total_credit, 2) : '-' }}
                                </td>
                                <td class="py-3 px-4 border-l border-slate-100 text-left font-bold text-blue-700 bg-blue-50/20">
                                    {{ $acc->closing_debit > 0 ? number_format($acc->closing_debit, 2) : '-' }}
                                </td>
                                <td class="py-3 px-4 text-left font-bold text-emerald-700 bg-emerald-50/20">
                                    {{ $acc->closing_credit > 0 ? number_format($acc->closing_credit, 2) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-100 font-mono font-bold text-xs border-t-2 border-slate-300">
                        <tr>
                            <td colspan="3" class="py-3 px-4 font-sans text-right text-slate-800">الإجمالي العام لميزان المراجعة:</td>
                            <td class="py-3 px-4 text-left text-slate-800 border-l border-slate-200">{{ number_format($totalTrialDebit, 2) }}</td>
                            <td class="py-3 px-4 text-left text-slate-800 border-l border-slate-200">{{ number_format($totalTrialCredit, 2) }}</td>
                            <td class="py-3 px-4 text-left text-blue-800 border-l border-slate-200">{{ number_format($totalClosingDebit, 2) }}</td>
                            <td class="py-3 px-4 text-left text-emerald-800">{{ number_format($totalClosingCredit, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- REPORT 2: General Ledger (الأستاذ العام / كشف حساب) -->
    <div x-show="activeReport === 'ledger'" class="space-y-4">
        @if($selectedAccount)
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-400 font-semibold block">كشف حركة الحساب</span>
                    <h4 class="font-bold text-base text-slate-800">
                        {{ $selectedAccount->account_code }} - {{ $selectedAccount->account_name }}
                    </h4>
                    <span class="text-xs text-slate-500 font-sans">
                        طبيعة الحساب: <strong>{{ $selectedAccount->normal_balance === 'DEBIT' ? 'مدين (Debit)' : 'دائن (Credit)' }}</strong> | التصنيف: <strong>{{ $selectedAccount->account_type }}</strong>
                    </span>
                </div>
                <button onclick="window.print()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-print"></i>
                    <span>طباعة كشف الحساب</span>
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">تاريخ القيد</th>
                                <th class="py-3 px-4">رقم القيد اليومي</th>
                                <th class="py-3 px-4">البيان والشرح</th>
                                <th class="py-3 px-4 text-left font-mono">مدين (+)</th>
                                <th class="py-3 px-4 text-left font-mono">دائن (-)</th>
                                <th class="py-3 px-4 text-left font-mono">الرصيد التراكمي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono">
                            @forelse($ledgerLines as $line)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4 text-slate-500">{{ $line->journalEntry?->entry_date }}</td>
                                    <td class="py-3 px-4 font-bold text-brand-700">#{{ $line->journalEntry?->entry_number }}</td>
                                    <td class="py-3 px-4 font-sans text-slate-700 font-medium">{{ $line->description ?? $line->journalEntry?->description }}</td>
                                    <td class="py-3 px-4 text-left font-bold text-slate-800">
                                        {{ $line->debit_amount > 0 ? number_format($line->debit_amount, 2) : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-left font-bold text-slate-800">
                                        {{ $line->credit_amount > 0 ? number_format($line->credit_amount, 2) : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-left font-bold {{ $line->running_balance >= 0 ? 'text-blue-700 bg-blue-50/30' : 'text-red-700 bg-red-50/30' }}">
                                        {{ number_format($line->running_balance, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400 font-sans">
                                        لا توجد حركات قيود يومية مسجلة على هذا الحساب في الفترة المحددة
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white p-12 rounded-2xl border border-slate-200/80 text-center text-slate-400">
                <i class="fa-solid fa-book-bookmark text-4xl mb-3 text-slate-300 block"></i>
                <h4 class="font-bold text-slate-700 text-sm mb-1">اختر حساباً محاسبياً لعرض كشف الحركة ودفتر الأستاذ</h4>
                <p class="text-xs text-slate-400">يرجى تحديد الحساب من القائمة أعلاه والضغط على "تطبيق" لعرض السجل التفصيلي</p>
            </div>
        @endif
    </div>

</div>
@endsection
