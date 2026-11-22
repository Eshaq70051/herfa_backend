@extends('admin.layout')

@section('title', 'شجرة الحسابات العامة')
@section('page_title', 'دليل الحسابات وشجرة المحاسبة العامة (Chart of Accounts)')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">دليل الحسابات (شجرة الحسابات)</h3>
            <p class="text-xs text-slate-400">هيكل الحسابات المالية: أصول، خصوم، حقوق ملكية، إيرادات، ومصروفات</p>
        </div>
        <button onclick="document.getElementById('addAccountModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة حساب جديد</span>
        </button>
    </div>

    <!-- Accounts Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">رمز الحساب</th>
                        <th class="py-3.5 px-4">اسم الحساب</th>
                        <th class="py-3.5 px-4">نوع الحساب</th>
                        <th class="py-3.5 px-4">طبيعة الرصيد</th>
                        <th class="py-3.5 px-4">رئيسي / فرعي</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($accounts as $acc)
                        <tr class="hover:bg-slate-50/60 transition {{ $acc->is_control_account ? 'bg-slate-50/40 font-bold' : '' }}">
                            <td class="py-3.5 px-4 font-mono font-bold text-brand-700">{{ $acc->account_code }}</td>
                            <td class="py-3.5 px-4 text-slate-800">
                                @if(!$acc->is_control_account)
                                    <span class="inline-block w-4 mr-2 border-r-2 border-slate-300 h-2"></span>
                                @endif
                                {{ $acc->account_name }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold 
                                    {{ $acc->account_type === 'ASSET' ? 'bg-blue-50 text-blue-700' : '' }}
                                    {{ $acc->account_type === 'LIABILITY' ? 'bg-amber-50 text-amber-700' : '' }}
                                    {{ $acc->account_type === 'EQUITY' ? 'bg-purple-50 text-purple-700' : '' }}
                                    {{ $acc->account_type === 'REVENUE' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $acc->account_type === 'EXPENSE' ? 'bg-red-50 text-red-700' : '' }}">
                                    {{ $acc->account_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold">{{ $acc->normal_balance }}</td>
                            <td class="py-3.5 px-4">
                                @if($acc->is_control_account)
                                    <span class="text-indigo-600 font-bold">حساب رئيسي</span>
                                @else
                                    <span class="text-slate-500">حساب فرعي</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $acc->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $acc->is_active ? 'نشط' : 'معطل' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.accounting.reports', ['account_id' => $acc->id, 'active_tab' => 'ledger']) }}" class="p-1.5 text-primary-600 hover:bg-primary-50 rounded-lg transition" title="كشف حساب دفتر الأستاذ">
                                        <i class="fa-solid fa-book-bookmark"></i>
                                    </a>
                                    <button onclick="editAccount({{ json_encode($acc) }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل الحساب">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('admin.accounting.accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الحساب المالي؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف الحساب">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">لا توجد حسابات مسجلة حالياً</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Account Modal -->
    <div id="addAccountModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">إضافة حساب مالي جديد</h4>
                <button onclick="document.getElementById('addAccountModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.accounting.accounts.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">رمز الحساب (Code)</label>
                        <input type="text" name="account_code" required placeholder="مثال: 1011" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">نوع الحساب</label>
                        <select name="account_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                            <option value="ASSET">أصول (ASSET)</option>
                            <option value="LIABILITY">خصوم (LIABILITY)</option>
                            <option value="EQUITY">حقوق ملكية (EQUITY)</option>
                            <option value="REVENUE">إيرادات (REVENUE)</option>
                            <option value="EXPENSE">مصروفات (EXPENSE)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم الحساب بالعربية</label>
                    <input type="text" name="account_name" required placeholder="مثال: البنك الأهلي - حساب جاري" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">الحساب الرئيسي (الأب)</label>
                        <select name="parent_account_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                            <option value="">بدون (حساب رئيسي)</option>
                            @foreach($accounts->where('is_control_account', 1) as $p)
                                <option value="{{ $p->id }}">{{ $p->account_code }} - {{ $p->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">طبيعة الرصيد</label>
                        <select name="normal_balance" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                            <option value="DEBIT">مدين (DEBIT)</option>
                            <option value="CREDIT">دائن (CREDIT)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_control_account" value="1" class="rounded text-brand-600">
                        <span>حساب رئيسي (تجميعي)</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_postable" value="1" checked class="rounded text-brand-600">
                        <span>يقبل ترحيل القيود</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('addAccountModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition">حفظ الحساب</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div id="editAccountModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تعديل بيانات الحساب المالي</h4>
                <button onclick="document.getElementById('editAccountModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="editAccountForm" method="POST" action="" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">رمز الحساب (Code)</label>
                        <input type="text" id="edit_acc_code" name="account_code" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">نوع الحساب</label>
                        <select id="edit_acc_type" name="account_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="ASSET">أصول (ASSET)</option>
                            <option value="LIABILITY">خصوم (LIABILITY)</option>
                            <option value="EQUITY">حقوق ملكية (EQUITY)</option>
                            <option value="REVENUE">إيرادات (REVENUE)</option>
                            <option value="EXPENSE">مصروفات (EXPENSE)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم الحساب</label>
                    <input type="text" id="edit_acc_name" name="account_name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">طبيعة الرصيد</label>
                        <select id="edit_acc_balance" name="normal_balance" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="DEBIT">مدين (DEBIT)</option>
                            <option value="CREDIT">دائن (CREDIT)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">الحالة</label>
                        <select id="edit_acc_active" name="is_active" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="1">نشط</option>
                            <option value="0">معطل</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" id="edit_acc_control" name="is_control_account" value="1" class="rounded text-brand-600">
                        <span>حساب رئيسي</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" id="edit_acc_postable" name="is_postable" value="1" class="rounded text-brand-600">
                        <span>يقبل ترحيل القيود</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('editAccountModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function editAccount(acc) {
        document.getElementById('editAccountForm').action = '/admin/accounting/accounts/' + acc.id;
        document.getElementById('edit_acc_code').value = acc.account_code;
        document.getElementById('edit_acc_name').value = acc.account_name;
        document.getElementById('edit_acc_type').value = acc.account_type;
        document.getElementById('edit_acc_balance').value = acc.normal_balance;
        document.getElementById('edit_acc_active').value = acc.is_active ? '1' : '0';
        document.getElementById('edit_acc_control').checked = acc.is_control_account ? true : false;
        document.getElementById('edit_acc_postable').checked = acc.is_postable ? true : false;
        document.getElementById('editAccountModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
