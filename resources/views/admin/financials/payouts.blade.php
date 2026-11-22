@extends('admin.layout')

@section('title', 'المالية ووسائل الدفع')
@section('page_title', 'العمليات المالية وطرق الدفع (Financial Transactions & Payment Methods)')

@section('content')
<div class="space-y-8" x-data="{
    showAddPaymentModal: false,
    showEditPaymentModal: false,
    showDeletePaymentModal: false,
    selectedPayment: {},

    showAddTransactionModal: false,
    showProcessModal: false,
    showDeleteTransactionModal: false,
    selectedTransaction: {},

    showImageModal: false,
    modalImageUrl: '',
    modalImageTitle: ''
}">

    <!-- Top Action Banner -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-800">إدارة العمليات المالية وبوابات وسندات الدفع</h3>
            <p class="text-xs text-gray-500 mt-1">متابعة الفواتير، سندات وإيصالات التحويل البنكي، وشعارات بوابات الدفع الإلكترونية المعتمدة</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showAddTransactionModal = true" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-receipt"></i>
                <span>تسجيل معاملة وسند مالي</span>
            </button>
            <button @click="showAddPaymentModal = true" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>إضافة وسيلة دفع جديدة</span>
            </button>
        </div>
    </div>

    <!-- SECTION 1: Payment Methods Grid -->
    <div class="space-y-3">
        <h4 class="font-bold text-gray-800 text-base flex items-center gap-2">
            <i class="fa-solid fa-credit-card text-emerald-600"></i>
            <span>وسائل وبوابات الدفع المفعلة ({{ $paymentMethods->count() }})</span>
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($paymentMethods as $pm)
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between hover:border-emerald-300 hover:shadow-md transition">
                    <div>
                        <div class="flex items-start gap-3">
                            @if($pm->image)
                                <img src="{{ $pm->image_url }}" alt="{{ $pm->name }}" class="w-12 h-12 object-contain rounded-xl border border-gray-200 p-1 bg-gray-50 flex-shrink-0 shadow-sm">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg flex-shrink-0 border border-emerald-100">
                                    <i class="fa-solid fa-credit-card"></i>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-mono text-[11px] font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $pm->code }}</span>
                                    @if($pm->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">مفعلة</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-200">معطلة</span>
                                    @endif
                                </div>
                                <h5 class="font-bold text-gray-800 text-sm truncate">{{ $pm->name }}</h5>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">{{ $pm->description ?? 'وسيلة دفع رقمية في التطبيق' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-100">
                        <span class="text-[11px] text-gray-400">
                            @if($pm->image)
                                <i class="fa-solid fa-image text-emerald-500"></i> يوجد شعار
                            @else
                                <i class="fa-regular fa-image text-gray-300"></i> بدون شعار
                            @endif
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button @click="selectedPayment = {
                                id: {{ $pm->id }},
                                name: '{{ addslashes($pm->name) }}',
                                description: '{{ addslashes($pm->description ?? '') }}',
                                image_url: '{{ $pm->image_url ?? '' }}',
                                is_active: {{ $pm->is_active ? 'true' : 'false' }}
                            }; showEditPaymentModal = true" class="p-1.5 px-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold transition flex items-center gap-1" title="تعديل">
                                <i class="fa-solid fa-pen text-[10px]"></i>
                                <span>تعديل</span>
                            </button>
                            <button @click="selectedPayment = { id: {{ $pm->id }}, name: '{{ addslashes($pm->name) }}' }; showDeletePaymentModal = true" class="p-1.5 px-2.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold transition flex items-center gap-1" title="حذف">
                                <i class="fa-solid fa-trash text-[10px]"></i>
                                <span>حذف</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white p-8 text-center rounded-2xl border border-gray-200 text-gray-400">
                    لا توجد وسائل دفع مضافة بعد
                </div>
            @endforelse
        </div>
    </div>

    <!-- SECTION 2: Transactions Table -->
    <div class="space-y-3">
        <h4 class="font-bold text-gray-800 text-base flex items-center gap-2">
            <i class="fa-solid fa-receipt text-emerald-600"></i>
            <span>سجل المعاملات وسندات السداد ({{ $transactions->total() }})</span>
        </h4>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs uppercase">
                        <tr>
                            <th class="p-4">رقم المعاملة</th>
                            <th class="p-4">المستخدم / العميل</th>
                            <th class="p-4">الخدمة / الطلب</th>
                            <th class="p-4">المبلغ</th>
                            <th class="p-4">وسيلة الدفع</th>
                            <th class="p-4 text-center">سند / إيصال الدفع</th>
                            <th class="p-4">حالة الدفع</th>
                            <th class="p-4">التاريخ</th>
                            <th class="p-4 text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($transactions as $trx)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="p-4 font-mono font-bold text-xs text-gray-700">
                                    {{ $trx->transaction_number ?? ('TRX-' . $trx->id) }}
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-gray-800">{{ $trx->user->full_name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $trx->user->phone ?? '' }}</div>
                                </td>
                                <td class="p-4 text-xs font-semibold text-emerald-700">
                                    {{ $trx->request->service->name ?? ('طلب #' . $trx->request_id) }}
                                </td>
                                <td class="p-4 font-mono font-bold text-gray-900">
                                    {{ number_format($trx->amount, 2) }} ر.ي
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        @if($trx->paymentMethod && $trx->paymentMethod->image)
                                            <img src="{{ $trx->paymentMethod->image_url }}" class="w-6 h-6 object-contain rounded border border-gray-100 p-0.5 bg-white">
                                        @endif
                                        <span class="text-xs font-medium text-gray-700">{{ $trx->paymentMethod->name ?? ($trx->payment_method ?? 'محفظة / نقدي') }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    @if($trx->receipt_image)
                                        <button type="button" @click="modalImageUrl = '{{ $trx->receipt_image_url }}'; modalImageTitle = 'سند السداد - {{ $trx->transaction_number }}'; showImageModal = true" class="relative group inline-block">
                                            <img src="{{ $trx->receipt_image_url }}" alt="سند الدفع" class="w-10 h-10 object-cover rounded-lg border border-gray-200 shadow-xs group-hover:scale-105 group-hover:ring-2 group-hover:ring-emerald-500 transition cursor-zoom-in">
                                            <span class="absolute -bottom-1 -right-1 bg-emerald-600 text-white rounded-full w-4 h-4 text-[9px] flex items-center justify-center shadow-xs"><i class="fa-solid fa-magnifying-glass"></i></span>
                                        </button>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-400">لا يوجد سند</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($trx->payment_status === 'paid')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">مدفوع</span>
                                    @elseif($trx->payment_status === 'failed')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-200">فشل الدفع</span>
                                    @elseif($trx->payment_status === 'refunded')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">مسترجع</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">قيد الانتظار</span>
                                    @endif
                                </td>
                                <td class="p-4 text-xs text-gray-400 font-mono">
                                    {{ $trx->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button @click="selectedTransaction = {
                                            id: {{ $trx->id }},
                                            number: '{{ $trx->transaction_number ?? ('TRX-' . $trx->id) }}',
                                            amount: '{{ number_format($trx->amount, 2) }}',
                                            status: '{{ $trx->payment_status }}',
                                            receipt_url: '{{ $trx->receipt_image_url ?? '' }}'
                                        }; showProcessModal = true" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 text-gray-700 text-xs font-semibold transition flex items-center gap-1" title="تحديث وسند الدفع">
                                            <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                            <span>معالجة</span>
                                        </button>
                                        <button @click="selectedTransaction = { id: {{ $trx->id }}, number: '{{ $trx->transaction_number ?? ('TRX-' . $trx->id) }}' }; showDeleteTransactionModal = true" class="p-1 px-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs transition" title="حذف">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-12 text-center text-gray-400">
                                    <i class="fa-solid fa-receipt text-4xl text-gray-300 mb-2"></i>
                                    <p>لا توجد معاملات مالية مسجلة بعد</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL: Add Payment Method -->
    <div x-show="showAddPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showAddPaymentModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-credit-card text-emerald-600"></i>
                <span>إضافة وسيلة دفع جديدة</span>
            </h3>
            <form action="{{ route('admin.payment-methods.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">اسم وسيلة الدفع *</label>
                    <input type="text" name="name" required placeholder="مثال: الكريمي، ون كاش، مدى، Apple Pay" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">الرمز التعريفي (Code) *</label>
                    <input type="text" name="code" required placeholder="مثال: kuraimi, onecash, mada, cash" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">شعار وسيلة الدفع / الأيقونة (صورة)</label>
                    <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <p class="text-[10px] text-gray-400 mt-1">يُفضل استخدام صورة شفافة PNG أو SVG لشعار البنك أو المحفظة</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">الوصف</label>
                    <textarea name="description" rows="2" placeholder="وصف مقتضب يظهر للمستخدم في التطبيق..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showAddPaymentModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">حفظ الوسيلة</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Payment Method -->
    <div x-show="showEditPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showEditPaymentModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-emerald-600"></i>
                <span>تعديل وسيلة الدفع</span>
            </h3>
            <form :action="'{{ url('/admin/financials/payment-methods') }}/' + selectedPayment.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">اسم وسيلة الدفع *</label>
                    <input type="text" name="name" x-model="selectedPayment.name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">تحديث شعار وسيلة الدفع (صورة)</label>
                    <template x-if="selectedPayment.image_url">
                        <div class="mb-2 flex items-center gap-3 p-2 bg-gray-50 rounded-xl border border-gray-200">
                            <img :src="selectedPayment.image_url" class="w-12 h-12 object-contain rounded-lg border bg-white p-1">
                            <span class="text-xs text-gray-500">الشعار الحالي</span>
                        </div>
                    </template>
                    <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">الوصف</label>
                    <textarea name="description" x-model="selectedPayment.description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="pm_is_active" :checked="selectedPayment.is_active" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <label for="pm_is_active" class="text-xs font-semibold text-gray-700">تفعيل وسيلة الدفع في المنظومة</label>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showEditPaymentModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">تحديث الوسيلة</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Delete Payment Method -->
    <div x-show="showDeletePaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showDeletePaymentModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash-can text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">حذف وسيلة الدفع</h3>
            <p class="text-xs text-gray-500 mt-1">هل أنت متأكد من حذف وسيلة الدفع (<span class="font-bold text-red-600" x-text="selectedPayment.name"></span>)؟</p>
            <form :action="'{{ url('/admin/financials/payment-methods') }}/' + selectedPayment.id" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeletePaymentModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 shadow-md shadow-red-600/20">تأكيد الحذف</button>
            </form>
        </div>
    </div>

    <!-- MODAL: Add Transaction with Receipt -->
    <div x-show="showAddTransactionModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showAddTransactionModal = false" class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl border border-gray-100">
            <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-receipt text-blue-600"></i>
                <span>تسجيل معاملة مالية وسند دفع جديد</span>
            </h3>
            <form action="{{ route('admin.financials.transactions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">طلب الصيانة المتعلق بالمعاملة *</label>
                    <select name="request_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- اختر طلب الخدمة --</option>
                        @foreach($serviceRequests as $req)
                            <option value="{{ $req->id }}">طلب #{{ $req->id }} - {{ $req->service->name ?? 'خدمة عامة' }} (العميل: {{ $req->customer->full_name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">العميل / المستخدم الدافع *</label>
                    <select name="user_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- اختر المستخدم --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->full_name }} ({{ $u->phone }}) - [{{ $u->role }}]</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">المبلغ (ر.ي) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="150.00" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">وسيلة الدفع *</label>
                        <select name="payment_method_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }} ({{ $pm->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">حالة الدفع *</label>
                    <select name="payment_status" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="paid">تم السداد بنجاح (Paid)</option>
                        <option value="pending">قيد الانتظار والتحقق (Pending)</option>
                        <option value="failed">فشلت العملية (Failed)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">صورة إشعار / سند التحويل المالي (Receipt Image)</label>
                    <input type="file" name="receipt_image" accept="image/*" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[10px] text-gray-400 mt-1">صورة إشعار البنك، سكرين شوت الحوالة أو سند القبض</p>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showAddTransactionModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-md shadow-blue-600/20">حفظ المعاملة والسند</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Process Transaction & Upload Receipt -->
    <div x-show="showProcessModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showProcessModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100">
            <h3 class="text-base font-bold text-gray-800 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i>
                <span>معالجة المعاملة وتحديث سند الدفع</span>
            </h3>
            <p class="text-xs text-gray-500 mb-4">رقم: <span class="font-mono font-bold text-emerald-700" x-text="selectedTransaction.number"></span> (المبلغ: <span class="font-bold text-gray-900" x-text="selectedTransaction.amount"></span> ر.ي)</p>
            <form :action="'{{ url('/admin/financials/payouts') }}/' + selectedTransaction.id + '/process'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">الحالة المالية للمعاملة *</label>
                    <select name="payment_status" x-model="selectedTransaction.status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="paid">تم الدفع بنجاح (Paid)</option>
                        <option value="pending">قيد الانتظار والتدقيق (Pending)</option>
                        <option value="failed">فشلت المعاملة (Failed)</option>
                        <option value="refunded">تم استرداد المبلغ (Refunded)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">سند / إشعار السداد الحالي</label>
                    <template x-if="selectedTransaction.receipt_url">
                        <div class="mb-2 p-2 bg-gray-50 rounded-xl border border-gray-200 flex items-center gap-3">
                            <img :src="selectedTransaction.receipt_url" class="w-14 h-14 object-cover rounded-lg border bg-white shadow-xs">
                            <div>
                                <span class="text-xs font-bold text-gray-700 block">يوجد سند مرفق</span>
                                <a :href="selectedTransaction.receipt_url" target="_blank" class="text-[11px] text-blue-600 hover:underline">عرض بالحجم الكامل <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i></a>
                            </div>
                        </div>
                    </template>
                    <label class="block text-xs font-bold text-gray-700 mb-1">رفع أو استبدال سند السداد (صورة)</label>
                    <input type="file" name="receipt_image" accept="image/*" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showProcessModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">حفظ التحديث والسند</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Delete Transaction -->
    <div x-show="showDeleteTransactionModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showDeleteTransactionModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash-can text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">حذف سجل المعاملة المالية</h3>
            <p class="text-xs text-gray-500 mt-1">هل أنت متأكد من حذف المعاملة (<span class="font-mono font-bold text-red-600" x-text="selectedTransaction.number"></span>)؟</p>
            <form :action="'{{ url('/admin/financials/transactions') }}/' + selectedTransaction.id" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteTransactionModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 shadow-md shadow-red-600/20">تأكيد الحذف</button>
            </form>
        </div>
    </div>

    <!-- MODAL: Lightbox Image Viewer -->
    <div x-show="showImageModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
        <div @click.away="showImageModal = false" class="bg-white rounded-2xl overflow-hidden max-w-2xl w-full shadow-2xl border border-gray-700">
            <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50">
                <h4 class="font-bold text-gray-800 text-sm" x-text="modalImageTitle"></h4>
                <button @click="showImageModal = false" class="text-gray-400 hover:text-gray-700 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-4 flex items-center justify-center bg-gray-900/5 min-h-[300px]">
                <img :src="modalImageUrl" alt="معاينة السند" class="max-h-[70vh] max-w-full object-contain rounded-xl shadow-lg">
            </div>
            <div class="p-3 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <a :href="modalImageUrl" target="_blank" download class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-download"></i>
                    <span>تحميل السند</span>
                </a>
                <button @click="showImageModal = false" class="px-4 py-2 rounded-xl bg-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-300">
                    إغلاق
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
