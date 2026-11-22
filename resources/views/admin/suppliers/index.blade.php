@extends('admin.layout')

@section('title', 'دليل الموردين')
@section('page_title', 'دليل الموردين والمشتريات (Suppliers)')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">قائمة الموردين المعتمدين</h3>
            <p class="text-xs text-slate-400">موردي المواد الخام وقطع الغيار لخدمات الصيانة</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.suppliers.invoices') }}" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-file-invoice"></i>
                <span>فواتير الشراء</span>
            </a>
            <button onclick="document.getElementById('addSupplierModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>إضافة مورد جديد</span>
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">اسم المورد / الشركة</th>
                        <th class="py-3.5 px-4">مسؤول التواصل</th>
                        <th class="py-3.5 px-4">رقم الهاتف</th>
                        <th class="py-3.5 px-4">البريد</th>
                        <th class="py-3.5 px-4">فواتير الشراء</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $sup)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $sup->name }}</td>
                            <td class="py-3.5 px-4 text-slate-600 font-medium">{{ $sup->contact_person ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">{{ $sup->phone ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $sup->email ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-bold text-brand-700">{{ $sup->purchase_invoices_count }} فواتير</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $sup->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $sup->is_active ? 'نشط' : 'معطل' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <button onclick="viewSupplier({{ json_encode($sup) }})" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="عرض تفاصيل المورد">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    @if($sup->account_id)
                                        <a href="{{ route('admin.accounting.reports', ['active_tab' => 'ledger', 'account_id' => $sup->account_id]) }}" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition" title="كشف حساب دفتر الأستاذ">
                                            <i class="fa-solid fa-book-bookmark"></i>
                                        </a>
                                    @endif
                                    <button onclick="editSupplier({{ json_encode($sup) }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل المورد">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('admin.suppliers.destroy', $sup->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المورد؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف المورد">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">لا يوجد موردين مسجلين بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addSupplierModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">إضافة مورد جديد</h4>
                <button onclick="document.getElementById('addSupplierModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.suppliers.store') }}" method="POST" class="space-y-4 max-h-[75vh] overflow-y-auto">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رمز المورد</label>
                    <input type="text" name="supplier_code" required maxlength="30" placeholder="SUP-0001" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم المورد / المؤسسة</label>
                    <input type="text" name="name" required maxlength="150" placeholder="مثال: مؤسسة المواد الهندسية" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">مسؤول الاتصال</label>
                    <input type="text" name="contact_person" placeholder="اسم مندوب أو مسؤول المورد" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رقم الهاتف</label>
                    <input type="text" name="phone" required maxlength="20" placeholder="770000000" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" placeholder="supplier@example.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">حساب المورد</label>
                    <select name="account_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">العملة الافتراضية</label>
                    <select name="default_currency_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('addSupplierModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition">حفظ المورد</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editSupplierModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm">تعديل بيانات المورد</h4>
                <button onclick="document.getElementById('editSupplierModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="editSupplierForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم المورد</label>
                    <input type="text" id="edit_sup_name" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">مسؤول الاتصال</label>
                    <input type="text" id="edit_sup_contact" name="contact_person" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رقم الهاتف</label>
                    <input type="text" id="edit_sup_phone" name="phone" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">البريد الإلكتروني</label>
                    <input type="email" id="edit_sup_email" name="email" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الحالة</label>
                    <select id="edit_sup_active" name="is_active" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <option value="1">نشط</option>
                        <option value="0">معطل</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('editSupplierModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Supplier Modal -->
    <div id="viewSupplierModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-building-user text-primary-600 text-lg"></i>
                    <h4 class="font-bold text-slate-800 text-sm">تفاصيل ملف المورد</h4>
                </div>
                <button onclick="document.getElementById('viewSupplierModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">رمز المورد:</span>
                        <span id="view_sup_code" class="font-mono font-bold text-primary-700"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">اسم المورد / الشركة:</span>
                        <span id="view_sup_name" class="font-bold text-slate-800 text-sm"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">مسؤول الاتصال:</span>
                        <span id="view_sup_contact" class="font-semibold text-slate-700"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">رقم الهاتف:</span>
                        <span id="view_sup_phone" class="font-mono font-bold text-slate-800"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">البريد الإلكتروني:</span>
                        <span id="view_sup_email" class="font-mono text-slate-600"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">العنوان:</span>
                        <span id="view_sup_address" class="text-slate-700"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">الحالة:</span>
                        <span id="view_sup_status" class="font-bold"></span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a id="view_sup_ledger_link" href="#" class="px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold rounded-xl text-xs transition flex items-center gap-2 border border-amber-200">
                        <i class="fa-solid fa-book-bookmark"></i>
                        <span>كشف حساب الأستاذ العام</span>
                    </a>
                    <button type="button" onclick="document.getElementById('viewSupplierModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function editSupplier(sup) {
        document.getElementById('editSupplierForm').action = '/admin/suppliers/' + sup.id;
        document.getElementById('edit_sup_name').value = sup.name;
        document.getElementById('edit_sup_contact').value = sup.contact_person || '';
        document.getElementById('edit_sup_phone').value = sup.phone || '';
        document.getElementById('edit_sup_email').value = sup.email || '';
        document.getElementById('edit_sup_active').value = sup.is_active ? '1' : '0';
        document.getElementById('editSupplierModal').classList.remove('hidden');
    }

    function viewSupplier(sup) {
        document.getElementById('view_sup_code').textContent = sup.supplier_code || '-';
        document.getElementById('view_sup_name').textContent = sup.name || '-';
        document.getElementById('view_sup_contact').textContent = sup.contact_person || '-';
        document.getElementById('view_sup_phone').textContent = sup.phone || '-';
        document.getElementById('view_sup_email').textContent = sup.email || '-';
        document.getElementById('view_sup_address').textContent = sup.address || 'اليمن';
        document.getElementById('view_sup_status').textContent = sup.is_active ? 'نشط' : 'معطل';
        document.getElementById('view_sup_status').className = sup.is_active ? 'text-emerald-700 font-bold' : 'text-red-600 font-bold';
        
        const ledgerLink = document.getElementById('view_sup_ledger_link');
        if (sup.account_id) {
            ledgerLink.href = '/admin/accounting/reports?active_tab=ledger&account_id=' + sup.account_id;
            ledgerLink.style.display = 'inline-flex';
        } else {
            ledgerLink.style.display = 'none';
        }

        document.getElementById('viewSupplierModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
