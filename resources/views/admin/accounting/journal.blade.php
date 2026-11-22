@extends('admin.layout')

@section('title', 'دفتر قيود اليومية')
@section('page_title', 'دفتر قيود اليومية العامة (General Journal Entries)')

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showViewModal: false,
    selectedEntry: null,
    lines: [
        { account_id: '', description: '', debit: '', credit: '' },
        { account_id: '', description: '', debit: '', credit: '' }
    ],
    addLine() {
        this.lines.push({ account_id: '', description: '', debit: '', credit: '' });
    },
    removeLine(index) {
        if (this.lines.length > 2) {
            this.lines.splice(index, 1);
        } else {
            alert('يجب أن يحتوي القيد على سطرين على الأقل (طرف مدين وطرف دائن)');
        }
    },
    get totalDebit() {
        return this.lines.reduce((sum, line) => sum + (parseFloat(line.debit) || 0), 0);
    },
    get totalCredit() {
        return this.lines.reduce((sum, line) => sum + (parseFloat(line.credit) || 0), 0);
    },
    get isBalanced() {
        const d = Math.round(this.totalDebit * 100);
        const c = Math.round(this.totalCredit * 100);
        return d > 0 && d === c;
    },
    get balanceDiff() {
        return Math.abs(this.totalDebit - this.totalCredit);
    },
    viewEntry(entry) {
        this.selectedEntry = entry;
        this.showViewModal = true;
    }
}">

    {{-- بطاقة الترويسة وأزرار الإجراءات والتقارير --}}
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">سجل القيود المحاسبية العامة</h3>
            <p class="text-xs text-slate-400">إدارة وترحيل قيود اليومية المزدوجة ومطابقة الأطراف المدينة والدائنة بالريال اليمني</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.accounting.reports', ['active_tab' => 'trial_balance']) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-scale-balanced text-primary-600"></i>
                <span>ميزان المراجعة</span>
            </a>
            <a href="{{ route('admin.accounting.reports', ['active_tab' => 'ledger']) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-book-bookmark text-amber-600"></i>
                <span>دفتر الأستاذ العام</span>
            </a>
            <button @click="showAddModal = true" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2 shadow-md shadow-primary-600/20">
                <i class="fa-solid fa-plus"></i>
                <span>تسجيل قيد محاسبي جديد</span>
            </button>
        </div>
    </div>

    {{-- شريط البحث والفلترة السريعة --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.accounting.journal') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث برقم القيد أو البيان..." class="w-full pl-8 pr-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
            </div>

            <div>
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    <option value="">جميع الحالات</option>
                    <option value="POSTED" {{ request('status') == 'POSTED' ? 'selected' : '' }}>مرحل (POSTED)</option>
                    <option value="REVERSED" {{ request('status') == 'REVERSED' ? 'selected' : '' }}>معكوس (REVERSED)</option>
                    <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>مسودة (DRAFT)</option>
                </select>
            </div>

            <div>
                <select name="period_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    <option value="">جميع الفترات المالية</option>
                    @foreach($periods as $per)
                        <option value="{{ $per->id }}" {{ request('period_id') == $per->id ? 'selected' : '' }}>
                            {{ $per->period_code }} - {{ $per->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <input type="date" name="from_date" value="{{ request('from_date') }}" title="من تاريخ" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
            </div>

            <div class="flex items-center gap-2">
                <input type="date" name="to_date" value="{{ request('to_date') }}" title="إلى تاريخ" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition">
                    فلترة
                </button>
                @if(request()->anyFilled(['search', 'status', 'period_id', 'from_date', 'to_date']))
                    <a href="{{ route('admin.accounting.journal') }}" class="p-2 text-slate-400 hover:text-red-500 rounded-xl transition" title="إلغاء الفلترة">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- قائمة قيود اليومية المحاسبية --}}
    <div class="space-y-4">
        @forelse($entries as $entry)
            @php
                $entryDebit = $entry->journalEntryLines->sum('debit_amount');
                $entryCredit = $entry->journalEntryLines->sum('credit_amount');
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden hover:border-primary-200 transition">
                <div class="p-4 bg-slate-50/80 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-primary-700 bg-primary-50 px-2.5 py-1 rounded-lg border border-primary-200">
                            #{{ $entry->entry_number }}
                        </span>
                        <span class="font-bold text-slate-800 text-sm">{{ $entry->description }}</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-slate-500">
                        <span>التاريخ: <strong class="font-mono text-slate-700">{{ $entry->entry_date }}</strong></span>
                        <span class="font-mono font-bold bg-white px-2 py-0.5 rounded border border-slate-200 text-slate-700">
                            {{ number_format($entryDebit, 2) }} ر.ي
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold 
                            {{ $entry->status === 'POSTED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                            {{ $entry->status === 'REVERSED' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}
                            {{ $entry->status === 'DRAFT' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}">
                            {{ $entry->status === 'POSTED' ? 'مرحل ومطابق' : ($entry->status === 'REVERSED' ? 'قيد معكوس' : 'مسودة') }}
                        </span>

                        {{-- أزرار الإجراءات --}}
                        <div class="inline-flex items-center gap-1.5 mr-2">
                            <button @click="viewEntry({{ json_encode($entry) }})" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1" title="عرض سند القيد والطباعة">
                                <i class="fa-solid fa-eye text-primary-600"></i>
                                <span>تفاصيل القيد</span>
                            </button>

                            @if($entry->status === 'POSTED')
                                <form action="{{ route('admin.accounting.journal.reverse', $entry->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من عكس هذا القيد المحاسبي؟ سيقوم النظام بإنشاء قيد عكسي تلقائياً لتصفير أثره المالي.')">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold transition flex items-center gap-1" title="عكس القيد">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        <span>عكس</span>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.accounting.journal.destroy', $entry->id) }}" method="POST" onsubmit="return confirm('تنبيه: هل أنت متأكد من حذف قيد اليومية بالكامل؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف القيد">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- جدول سطور القيد --}}
                <div class="p-4 overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="text-slate-400 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="py-2 px-3 w-12">#</th>
                                <th class="py-2 px-3">الحساب المالي</th>
                                <th class="py-2 px-3">البيان الخاص بالسطر</th>
                                <th class="py-2 px-3 font-mono text-left w-32">مدين (Debit)</th>
                                <th class="py-2 px-3 font-mono text-left w-32">دائن (Credit)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono">
                            @foreach($entry->journalEntryLines as $line)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2 px-3 text-slate-400">{{ $line->line_number }}</td>
                                    <td class="py-2 px-3 font-sans font-bold text-slate-700">
                                        <span class="text-primary-700 font-mono ml-1">{{ $line->account->account_code ?? '' }}</span>
                                        {{ $line->account->account_name ?? 'حساب مالي' }}
                                    </td>
                                    <td class="py-2 px-3 font-sans text-slate-500">{{ $line->description ?? '-' }}</td>
                                    <td class="py-2 px-3 text-left font-bold {{ $line->debit_amount > 0 ? 'text-slate-900 bg-slate-50/50' : 'text-slate-300' }}">
                                        {{ $line->debit_amount > 0 ? number_format($line->debit_amount, 2) . ' ر.ي' : '-' }}
                                    </td>
                                    <td class="py-2 px-3 text-left font-bold {{ $line->credit_amount > 0 ? 'text-slate-900 bg-slate-50/50' : 'text-slate-300' }}">
                                        {{ $line->credit_amount > 0 ? number_format($line->credit_amount, 2) . ' ر.ي' : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-slate-200 bg-slate-50/60 font-mono font-bold">
                            <tr>
                                <td colspan="3" class="py-2.5 px-3 text-left font-sans text-slate-600">الإجمالي المتزن:</td>
                                <td class="py-2.5 px-3 text-left text-primary-700">{{ number_format($entryDebit, 2) }} ر.ي</td>
                                <td class="py-2.5 px-3 text-left text-primary-700">{{ number_format($entryCredit, 2) }} ر.ي</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white p-12 rounded-2xl border border-slate-200/80 text-center text-slate-400 space-y-3">
                <i class="fa-solid fa-book-journal-whills text-5xl text-slate-300 block"></i>
                <h4 class="font-bold text-slate-700 text-sm">لم يتم تسجيل أو ترحيل أي قيود محاسبية بعد</h4>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">يمكنك البدء بتسجيل القيود المحاسبية اليومية المتزنة أو قيود التسويات بسهولة عبر زر الإضافة بالأعلى</p>
                <button @click="showAddModal = true" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs transition inline-flex items-center gap-2 shadow-md">
                    <i class="fa-solid fa-plus"></i>
                    <span>تسجيل أول قيد محاسبي</span>
                </button>
            </div>
        @endforelse
    </div>

    @if($entries->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
            {{ $entries->links() }}
        </div>
    @endif

    {{-- نافذة تسجيل قيد محاسبي جديد متزن مع سطور ديناميكية ومؤشر توازن حي --}}
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-3xl shadow-2xl border border-slate-100 space-y-4 max-h-[92vh] overflow-y-auto custom-scrollbar" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">تسجيل وترحيل قيد محاسبي جديد (متزن)</h4>
                    <p class="text-[11px] text-slate-400">إضافة أطراف القيد المدين والدائن والتأكد التلقائي من توازن السند</p>
                </div>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.accounting.journal.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">رقم القيد المحاسبي *</label>
                        <input type="text" name="entry_number" required value="JV-{{ date('Ymd') }}-{{ rand(100, 999) }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-primary-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">تاريخ القيد *</label>
                        <input type="date" name="entry_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">الفترة المالية</label>
                        <select name="accounting_period_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium">
                            <option value="">تلقائي (حسب تاريخ القيد)</option>
                            @foreach($periods as $per)
                                <option value="{{ $per->id }}">{{ $per->period_code }} — {{ $per->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">البيان والشرح العام للقيد *</label>
                    <input type="text" name="description" required placeholder="مثال: إثبات إيرادات خدمات أو سداد مصروفات تشغيلية أو رأس مال افتتاحي..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>

                {{-- جدول أسطر القيد التفاعلي --}}
                <div class="space-y-2 pt-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700">أطراف وسطور القيد (مدين / دائن):</label>
                        <button type="button" @click="addLine()" class="px-3 py-1 bg-primary-50 hover:bg-primary-100 text-primary-700 rounded-lg text-xs font-bold transition flex items-center gap-1.5 border border-primary-200">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>إضافة سطر محاسبي</span>
                        </button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(line, index) in lines" :key="index">
                            <div class="grid grid-cols-12 gap-2 bg-slate-50/80 p-2.5 rounded-xl border border-slate-200/80 items-center">
                                <div class="col-span-5">
                                    <label class="block text-[10px] text-slate-500 mb-0.5">الحساب المالي *</label>
                                    <select :name="'lines[' + index + '][account_id]'" x-model="line.account_id" required class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium">
                                        <option value="">اختر الحساب...</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }} ({{ $acc->normal_balance }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-span-3">
                                    <label class="block text-[10px] text-slate-500 mb-0.5">البيان المخصص</label>
                                    <input type="text" :name="'lines[' + index + '][description]'" x-model="line.description" placeholder="بيان السطر..." class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs">
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-[10px] text-slate-500 mb-0.5">مدين (Debit)</label>
                                    <input type="number" step="0.01" min="0" :name="'lines[' + index + '][debit]'" x-model="line.debit" placeholder="0.00" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-mono text-left font-bold text-slate-800">
                                </div>

                                <div class="col-span-2 flex items-center gap-1">
                                    <div class="flex-1">
                                        <label class="block text-[10px] text-slate-500 mb-0.5">دائن (Credit)</label>
                                        <input type="number" step="0.01" min="0" :name="'lines[' + index + '][credit]'" x-model="line.credit" placeholder="0.00" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-mono text-left font-bold text-slate-800">
                                    </div>
                                    <button type="button" @click="removeLine(index)" class="mt-4 p-1.5 text-slate-400 hover:text-red-500 transition" title="حذف السطر">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- شريط فحص وتأكيد توازن القيد الحي --}}
                <div class="p-3.5 rounded-xl border flex flex-col sm:flex-row items-center justify-between gap-3 text-xs"
                     :class="isBalanced ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800'">
                    <div class="flex items-center gap-2">
                        <i :class="isBalanced ? 'fa-solid fa-circle-check text-emerald-600 text-base' : 'fa-solid fa-triangle-exclamation text-amber-600 text-base'"></i>
                        <span class="font-bold" x-text="isBalanced ? 'القيد متزن تماماً وجاهز للترحيل' : 'القيد غير متزن! الفرق الحالي: ' + balanceDiff.toFixed(2) + ' ر.ي'"></span>
                    </div>
                    <div class="font-mono font-bold flex items-center gap-4">
                        <span>إجمالي المدين: <strong x-text="totalDebit.toFixed(2)"></strong> ر.ي</span>
                        <span>إجمالي الدائن: <strong x-text="totalCredit.toFixed(2)"></strong> ر.ي</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" :disabled="!isBalanced" :class="!isBalanced ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-primary-600 hover:bg-primary-700'" class="px-6 py-2.5 text-white rounded-xl text-xs font-bold transition shadow-md">
                        ترحيل القيد المحاسبي
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- نافذة عرض سند القيد والطباعة (Voucher View & Print) --}}
    <div x-show="showViewModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-2xl shadow-2xl border border-slate-100 space-y-4 max-h-[92vh] overflow-y-auto custom-scrollbar" @click.outside="showViewModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-primary-600 text-lg"></i>
                    <h4 class="font-bold text-slate-800 text-sm">سند قيد محاسبي رسمي</h4>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1">
                        <i class="fa-solid fa-print"></i>
                        <span>طباعة السند</span>
                    </button>
                    <button @click="showViewModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <template x-if="selectedEntry">
                <div class="space-y-4">
                    {{-- معلومات السند --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200/80 text-xs">
                        <div>
                            <span class="text-slate-400 block mb-0.5">رقم السند:</span>
                            <span class="font-mono font-bold text-primary-700" x-text="'#' + selectedEntry.entry_number"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">تاريخ القيد:</span>
                            <span class="font-mono font-bold text-slate-800" x-text="selectedEntry.entry_date"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">الحالة:</span>
                            <span class="font-bold text-emerald-700" x-text="selectedEntry.status === 'POSTED' ? 'مرحل ومطابق' : selectedEntry.status"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">المُرحل:</span>
                            <span class="font-bold text-slate-700" x-text="selectedEntry.created_by_user ? selectedEntry.created_by_user.full_name : 'المدير'"></span>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-semibold text-slate-500 block mb-1">البيان والشرح العام:</span>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80 text-xs font-bold text-slate-800" x-text="selectedEntry.description"></div>
                    </div>

                    {{-- جدول أسطر السند --}}
                    <div class="border border-slate-200 rounded-2xl overflow-hidden">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-slate-100/70 text-slate-500 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-3">#</th>
                                    <th class="py-2.5 px-3">الحساب المالي</th>
                                    <th class="py-2.5 px-3">البيان المخصص</th>
                                    <th class="py-2.5 px-3 text-left font-mono">مدين</th>
                                    <th class="py-2.5 px-3 text-left font-mono">دائن</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-mono">
                                <template x-for="line in selectedEntry.journal_entry_lines" :key="line.id">
                                    <tr>
                                        <td class="py-2.5 px-3 text-slate-400" x-text="line.line_number"></td>
                                        <td class="py-2.5 px-3 font-sans font-bold text-slate-800">
                                            <span class="text-primary-700 font-mono ml-1" x-text="line.account ? line.account.account_code : ''"></span>
                                            <span x-text="line.account ? line.account.account_name : 'حساب'"></span>
                                        </td>
                                        <td class="py-2.5 px-3 font-sans text-slate-500" x-text="line.description || '-'"></td>
                                        <td class="py-2.5 px-3 text-left font-bold" x-text="line.debit_amount > 0 ? parseFloat(line.debit_amount).toFixed(2) + ' ر.ي' : '-'"></td>
                                        <td class="py-2.5 px-3 text-left font-bold" x-text="line.credit_amount > 0 ? parseFloat(line.credit_amount).toFixed(2) + ' ر.ي' : '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- توقيعات السند الرسمي --}}
                    <div class="grid grid-cols-2 gap-4 pt-4 text-center text-xs text-slate-500 border-t border-slate-100">
                        <div>
                            <span class="block mb-6 font-semibold">توقيع المحاسب / مُعد السند:</span>
                            <span class="inline-block border-b border-dashed border-slate-400 w-36"></span>
                        </div>
                        <div>
                            <span class="block mb-6 font-semibold">توقيع المدير المالي / الاعتماد:</span>
                            <span class="inline-block border-b border-dashed border-slate-400 w-36"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
