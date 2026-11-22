@extends('admin.layout')

@section('title', 'ملف الموظف: ' . ($employee->user->full_name ?? $employee->employee_number))
@section('page_title', 'الملف الوظيفي والمالي للموظف: ' . ($employee->user->full_name ?? $employee->employee_number))

@section('content')
<div class="space-y-6" x-data="{
    activeTab: window.location.hash ? window.location.hash.substring(1) : 'financial',
    showVoucherModal: false,
    voucherType: 'debit',
    voucherCategory: 'custody',
    showEditModal: false,
    showDeleteModal: false,
    previewImageUrl: ''
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

    <!-- Top Action Nav -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.employees.index') }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="العودة لقائمة الموظفين">
                <i class="fa-solid fa-arrow-right text-sm"></i>
            </a>
            <div>
                <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <span>{{ $employee->user->full_name ?? 'موظف غير معرف' }}</span>
                    <span class="text-xs font-mono font-bold text-primary-700 bg-primary-50 px-2.5 py-0.5 rounded-lg border border-primary-200">#{{ $employee->employee_number }}</span>
                </h2>
                <div class="text-xs text-slate-400 mt-0.5 flex flex-wrap items-center gap-3">
                    <span><i class="fa-solid fa-briefcase text-slate-400 ml-1"></i> {{ $employee->job_title ?? 'موظف بالمنظومة' }}</span>
                    <span><i class="fa-solid fa-calendar-check text-slate-400 ml-1"></i> تاريخ التعيين: {{ $employee->hire_date }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Add Voucher / Custody Trigger -->
            <button type="button" @click="showVoucherModal = true; voucherType = 'debit'; voucherCategory = 'custody'" class="px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <span>إصدار سند مالي / عهدة</span>
            </button>

            <!-- Edit Employee Trigger -->
            <button type="button" @click="showEditModal = true" class="px-4 py-2.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>تعديل البيانات</span>
            </button>

            <!-- Delete Employee Trigger -->
            <button type="button" @click="showDeleteModal = true" class="px-4 py-2.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-trash"></i>
                <span>حذف الموظف</span>
            </button>
        </div>
    </div>

    <!-- Employee Profile Info Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            <!-- Left: Avatar & Personal Data -->
            <div class="flex items-start sm:items-center gap-4">
                @if($employee->user && $employee->user->profile_image)
                    <img src="{{ str_starts_with($employee->user->profile_image, 'http') ? $employee->user->profile_image : url($employee->user->profile_image) }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-primary-200 shadow-md flex-shrink-0">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-primary-700 font-black text-3xl flex items-center justify-center border border-slate-200 shadow-sm flex-shrink-0">
                        {{ mb_substr($employee->user->full_name ?? $employee->employee_number, 0, 1) }}
                    </div>
                @endif

                <div>
                    <div class="flex flex-wrap items-center gap-2.5 mb-2">
                        <h3 class="text-xl font-bold text-slate-900">{{ $employee->user->full_name ?? '-' }}</h3>

                        @if($employee->employment_status === 'ACTIVE')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>على رأس العمل</span>
                            </span>
                        @elseif($employee->employment_status === 'ON_LEAVE')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                في إجازة
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                إنهاء خدمة
                            </span>
                        @endif

                        @php
                            $primaryDept = $employee->employeeDepartments->firstWhere('is_primary', true) ?? $employee->employeeDepartments->first();
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $primaryDept?->department?->name ?? 'بدون قسم إداري' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-1.5 text-xs text-slate-600 mt-2">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-phone text-emerald-600 w-4 text-center"></i>
                            <span class="text-slate-400">الجوال:</span>
                            <span class="font-mono font-bold text-slate-800" dir="ltr">{{ $employee->user->phone ?? '-' }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-blue-500 w-4 text-center"></i>
                            <span class="text-slate-400">البريد:</span>
                            <span class="font-medium text-slate-800">{{ $employee->user->email ?? 'لا يوجد' }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-user-tie text-purple-500 w-4 text-center"></i>
                            <span class="text-slate-400">المسؤول المباشر:</span>
                            <span class="font-semibold text-slate-800">{{ $employee->reportsToEmployee->user->full_name ?? 'الإدارة العليا' }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-amber-500 w-4 text-center"></i>
                            <span class="text-slate-400">الدور بالنظام:</span>
                            @forelse($employee->user->roles ?? [] as $r)
                                <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 font-bold text-[11px]">{{ $r->name }}</span>
                            @empty
                                <span class="text-slate-400">موظف قياسي</span>
                            @endforelse
                        </div>

                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-business-time text-slate-400 w-4 text-center"></i>
                            <span class="text-slate-400">نوع العقد:</span>
                            <span class="font-bold text-slate-700">
                                @if($employee->employment_type === 'FULL_TIME') دوام كامل
                                @elseif($employee->employment_type === 'PART_TIME') دوام جزئي
                                @else عقد محدد
                                @endif
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-id-badge text-slate-400 w-4 text-center"></i>
                            <span class="text-slate-400">الرقم الوظيفي:</span>
                            <span class="font-mono font-bold text-primary-700">#{{ $employee->employee_number }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Highlights (كم مدين كم دائن) -->
            <div class="flex flex-wrap items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <!-- Total Debit (كم مدين) -->
                <div class="text-center px-3">
                    <span class="text-xs text-slate-500 block mb-1 font-semibold flex items-center justify-center gap-1">
                        <i class="fa-solid fa-arrow-down text-red-500 text-[10px]"></i>
                        <span>إجمالي المدين (عليه)</span>
                    </span>
                    <span class="text-xl font-mono font-black text-red-600">
                        {{ number_format($totalDebit, 2) }}
                        <span class="text-xs font-normal text-slate-400">ر.ي</span>
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">سلف وعهد نقدية مستلمة</span>
                </div>

                <div class="h-10 w-[1px] bg-slate-200 hidden sm:block"></div>

                <!-- Total Credit (كم دائن) -->
                <div class="text-center px-3">
                    <span class="text-xs text-slate-500 block mb-1 font-semibold flex items-center justify-center gap-1">
                        <i class="fa-solid fa-arrow-up text-emerald-500 text-[10px]"></i>
                        <span>إجمالي الدائن (له)</span>
                    </span>
                    <span class="text-xl font-mono font-black text-emerald-600">
                        {{ number_format($totalCredit, 2) }}
                        <span class="text-xs font-normal text-slate-400">ر.ي</span>
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">تسويات مصروفات ورواتب</span>
                </div>

                <div class="h-10 w-[1px] bg-slate-200 hidden sm:block"></div>

                <!-- Net Financial Balance -->
                <div class="text-center px-3">
                    <span class="text-xs text-slate-500 block mb-1 font-semibold">رصيد الحساب المتبقي</span>
                    @if($balance > 0)
                        <span class="text-xl font-mono font-black text-red-700 bg-red-100/70 px-2.5 py-0.5 rounded-lg inline-block">
                            {{ number_format($balance, 2) }} ر.ي
                        </span>
                        <span class="text-[10px] text-red-600 font-bold block mt-0.5">متبقي بعهدة الموظف للمنظومة</span>
                    @elseif($balance < 0)
                        <span class="text-xl font-mono font-black text-emerald-700 bg-emerald-100/70 px-2.5 py-0.5 rounded-lg inline-block">
                            {{ number_format(abs($balance), 2) }} ر.ي
                        </span>
                        <span class="text-[10px] text-emerald-600 font-bold block mt-0.5">مستحق للموظف طرف المنظومة</span>
                    @else
                        <span class="text-xl font-mono font-black text-slate-800 bg-slate-200/70 px-2.5 py-0.5 rounded-lg inline-block">
                            0.00 ر.ي
                        </span>
                        <span class="text-[10px] text-emerald-600 font-bold block mt-0.5">✓ الحساب مسوى بالكامل</span>
                    @endif
                </div>
            </div>
        </div>

        @if(!empty($employee->notes))
            <div class="mt-4 p-3 bg-amber-50/60 rounded-xl border border-amber-200/60 text-xs text-amber-900 flex items-start gap-2">
                <i class="fa-solid fa-circle-info text-amber-600 mt-0.5"></i>
                <div>
                    <strong class="font-bold">ملاحظات ووثائق ملف الموظف:</strong>
                    <span>{{ $employee->notes }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex border-b border-slate-200 bg-slate-50 overflow-x-auto text-xs font-bold">
            <button type="button" @click="activeTab = 'financial'; window.location.hash = 'financial'" :class="activeTab === 'financial' ? 'border-primary-600 text-primary-700 bg-white border-b-2 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span>كشف الحساب المالي والعهد (كم مدين كم دائن)</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'financial' ? 'bg-primary-100 text-primary-800' : 'bg-slate-200 text-slate-700'">{{ $employee->financialTransactions->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'permissions'; window.location.hash = 'permissions'" :class="activeTab === 'permissions' ? 'border-primary-600 text-primary-700 bg-white border-b-2 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-shield-halved"></i>
                <span>تقرير الصلاحيات الممنوحة ومصفوفة الواجهات</span>
            </button>

            <button type="button" @click="activeTab = 'activity'; window.location.hash = 'activity'" :class="activeTab === 'activity' ? 'border-primary-600 text-primary-700 bg-white border-b-2 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100'" class="px-5 py-3.5 flex items-center gap-2 transition whitespace-nowrap">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>سجل عمليات الموظف (الطلبات المسندة والاعتمادات)</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono" :class="activeTab === 'activity' ? 'bg-primary-100 text-primary-800' : 'bg-slate-200 text-slate-700'">{{ $assignedOrders->count() + $statusChanges->count() }}</span>
            </button>
        </div>

        <div class="p-6">
            <!-- TAB 1: Financial Ledger (كشف الحساب المالي والعهد) -->
            <div x-show="activeTab === 'financial'" class="space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-primary-600"></i>
                            <span>سجل القيود وسندات الصرف والقبض والعهد المالية للموظف</span>
                        </h4>
                        <p class="text-xs text-slate-400 mt-0.5">يوضح كافة المبالغ المستلمة (مدين) والمصروفات المسواة (دائن) ورصيد العهدة</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="showVoucherModal = true; voucherType = 'debit'; voucherCategory = 'custody'" class="px-3.5 py-2 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 text-xs font-bold transition flex items-center gap-1.5">
                            <i class="fa-solid fa-plus-circle"></i>
                            <span>صرف عهدة / سلفة (مدين +)</span>
                        </button>
                        <button type="button" @click="showVoucherModal = true; voucherType = 'credit'; voucherCategory = 'expense_settlement'" class="px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold transition flex items-center gap-1.5">
                            <i class="fa-solid fa-check-double"></i>
                            <span>تسوية مصاريف / سداد (دائن -)</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                            <tr>
                                <th class="p-3">رقم السند</th>
                                <th class="p-3">التاريخ</th>
                                <th class="p-3">نوع الحركة</th>
                                <th class="p-3">التصنيف</th>
                                <th class="p-3 text-red-700">مدين (عليه للمؤسسة)</th>
                                <th class="p-3 text-emerald-700">دائن (له عند المؤسسة)</th>
                                <th class="p-3">طريقة الدفع</th>
                                <th class="p-3">البيان والتفاصيل</th>
                                <th class="p-3">محرر السند</th>
                                <th class="p-3 text-center">المرفق</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($employee->financialTransactions->sortByDesc('transaction_date') as $txn)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-3 font-mono font-bold text-slate-800">
                                        {{ $txn->reference_number }}
                                    </td>
                                    <td class="p-3 text-slate-500 font-mono">
                                        {{ $txn->transaction_date->format('Y-m-d') }}
                                    </td>
                                    <td class="p-3">
                                        @if($txn->transaction_type === 'debit')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 inline-block">
                                                مدين (استلام عهدة/سلفة)
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block">
                                                دائن (تسوية مصاريف/سداد)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 font-semibold text-slate-700">
                                        {{ $txn->category_name }}
                                    </td>
                                    <td class="p-3 font-mono font-bold text-red-600">
                                        {{ $txn->transaction_type === 'debit' ? number_format($txn->amount, 2) . ' ر.ي' : '-' }}
                                    </td>
                                    <td class="p-3 font-mono font-bold text-emerald-600">
                                        {{ $txn->transaction_type === 'credit' ? number_format($txn->amount, 2) . ' ر.ي' : '-' }}
                                    </td>
                                    <td class="p-3 text-slate-600">
                                        {{ $txn->payment_method }}
                                    </td>
                                    <td class="p-3 text-slate-700 max-w-xs truncate" title="{{ $txn->description }}">
                                        {{ $txn->description ?: 'بدون بيان' }}
                                    </td>
                                    <td class="p-3 text-[11px] text-slate-500">
                                        <div>{{ $txn->createdBy->full_name ?? 'الإدارة' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $txn->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="p-3 text-center">
                                        @if($txn->receipt_attachment)
                                            <a href="{{ asset($txn->receipt_attachment) }}" target="_blank" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg inline-flex items-center gap-1 font-bold text-[11px]" title="معاينة سند القبض/الصرف">
                                                <i class="fa-solid fa-paperclip"></i>
                                                <span>السند</span>
                                            </a>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="p-12 text-center text-slate-400">
                                        <i class="fa-solid fa-file-invoice-dollar text-4xl text-slate-300 mb-2"></i>
                                        <p class="font-bold text-sm text-slate-600">لا توجد أي سندات مالية أو عهد مسجلة لهذا الموظف حتى الآن</p>
                                        <p class="text-xs text-slate-400 mt-1">يمكنك صرف عهدة نقدية أو تسجيل سلفة أو تسوية مصاريف مباشرة من الأزرار بالأعلى</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($employee->financialTransactions->isNotEmpty())
                            <tfoot class="bg-slate-100 text-xs font-bold border-t-2 border-slate-300">
                                <tr>
                                    <td colspan="4" class="p-3 text-slate-800 font-black">إجمالي الحركة المالية والرصيد الصافي:</td>
                                    <td class="p-3 font-mono font-black text-red-700">{{ number_format($totalDebit, 2) }} ر.ي</td>
                                    <td class="p-3 font-mono font-black text-emerald-700">{{ number_format($totalCredit, 2) }} ر.ي</td>
                                    <td colspan="4" class="p-3">
                                        <span class="font-bold text-slate-700">صافي رصيد العهدة: </span>
                                        <span class="font-mono font-black {{ $balance > 0 ? 'text-red-700' : ($balance < 0 ? 'text-emerald-700' : 'text-slate-800') }}">
                                            {{ number_format(abs($balance), 2) }} ر.ي
                                            ({{ $balance > 0 ? 'متبقي بعهدة الموظف' : ($balance < 0 ? 'مستحق للموظف' : 'مسوى') }})
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <!-- TAB 2: Roles & Permissions Matrix (الصلاحيات والواجهات) -->
            <div x-show="activeTab === 'permissions'" class="space-y-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-primary-600"></i>
                            <span>مصفوفة الصلاحيات وتحديد الواجهات المتاحة للموظف</span>
                        </h4>
                        <p class="text-xs text-slate-400 mt-0.5">تحديد الدور الوظيفي للموظف والواجهات والشاشات التي تظهر له في لوحة التحكم الإدارية</p>
                    </div>

                    @if(auth()->user()?->isAdmin())
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                            صلاحية الإدارة العليا (Admin)
                        </span>
                    @endif
                </div>

                <!-- Update Roles Form (Controlled by Admin) -->
                @if(auth()->user()?->isAdmin() || auth()->user()?->can('admin.users.manage'))
                    <form action="{{ route('admin.employees.permissions', $employee->id) }}" method="POST" class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                        @csrf
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800">تعيين دور الموظف في النظام (Roles Assignment):</span>
                            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold shadow-md shadow-primary-600/20 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-save"></i>
                                <span>حفظ الصلاحيات والأدوار</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($roles as $role)
                                @php
                                    $hasRole = $employee->user && $employee->user->roles->contains('id', $role->id);
                                @endphp
                                <label class="p-3 rounded-xl border {{ $hasRole ? 'border-primary-500 bg-primary-50/50' : 'border-slate-200 bg-white' }} flex items-start gap-3 cursor-pointer hover:border-primary-300 transition">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" {{ $hasRole ? 'checked' : '' }} class="mt-1 rounded text-primary-600 focus:ring-primary-500">
                                    <div>
                                        <div class="font-bold text-xs text-slate-900">{{ $role->name }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $role->description ?? 'دور وظيفي بالمنظومة' }}</div>
                                        <div class="text-[10px] text-primary-600 font-mono mt-1 font-bold">{{ $role->permissions->count() }} صلاحية مرتبطة</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </form>
                @endif

                <!-- Visual Breakdown of Visible Interfaces for this Employee -->
                <div>
                    <h5 class="font-bold text-slate-800 text-xs mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-desktop text-slate-500"></i>
                        <span>الواجهات والشاشات التي تظهر وتتاح لهذا الموظف حالياً في القائمة الجانبية:</span>
                    </h5>

                    @php
                        $userPermissions = $employee->user ? $employee->user->roles->flatMap->permissions->pluck('code')->unique()->toArray() : [];
                        $isSuperAdmin = $employee->user && ($employee->user->isAdmin() || $employee->user->role === 'admin');

                        $screens = [
                            ['title' => 'لوحة المؤشرات والإحصائيات', 'icon' => 'fa-gauge-high', 'view' => 'admin.dashboard.view', 'manage' => 'admin.dashboard.view'],
                            ['title' => 'طلبات الصيانة والخدمات', 'icon' => 'fa-clipboard-check', 'view' => 'admin.orders.view', 'manage' => 'admin.orders.manage'],
                            ['title' => 'الحرفيين والمهنيين', 'icon' => 'fa-user-gear', 'view' => 'admin.craftsmen.view', 'manage' => 'admin.craftsmen.manage'],
                            ['title' => 'الأقسام والخدمات والكتالوج', 'icon' => 'fa-layer-group', 'view' => 'admin.catalog.view', 'manage' => 'admin.catalog.manage'],
                            ['title' => 'العملاء والمستخدمين', 'icon' => 'fa-users', 'view' => 'admin.clients.view', 'manage' => 'admin.clients.manage'],
                            ['title' => 'المتاجر والخدمات الشريكة', 'icon' => 'fa-store', 'view' => 'admin.stores.view', 'manage' => 'admin.stores.manage'],
                            ['title' => 'المواقع والمحافظات والمديريات', 'icon' => 'fa-location-dot', 'view' => 'admin.locations.view', 'manage' => 'admin.locations.manage'],
                            ['title' => 'الإعلانات والتسويق', 'icon' => 'fa-bullhorn', 'view' => 'admin.marketing.view', 'manage' => 'admin.marketing.manage'],
                            ['title' => 'الإشعارات وتنبيهات التطبيق', 'icon' => 'fa-bell', 'view' => 'admin.notifications.view', 'manage' => 'admin.notifications.manage'],
                            ['title' => 'المالية وسحب الأرباح', 'icon' => 'fa-wallet', 'view' => 'admin.financials.view', 'manage' => 'admin.financials.manage'],
                            ['title' => 'النظام المحاسبي والقيود اليومية', 'icon' => 'fa-calculator', 'view' => 'admin.accounting.view', 'manage' => 'admin.accounting.manage'],
                            ['title' => 'الموردين وفواتير الشراء', 'icon' => 'fa-truck-field', 'view' => 'admin.suppliers.view', 'manage' => 'admin.suppliers.manage'],
                            ['title' => 'الموارد البشرية والموظفين', 'icon' => 'fa-id-card-clip', 'view' => 'admin.employees.view', 'manage' => 'admin.employees.manage'],
                            ['title' => 'التقييمات والمراجعات', 'icon' => 'fa-star', 'view' => 'admin.reviews.view', 'manage' => 'admin.reviews.manage'],
                            ['title' => 'الدعم الفني والشكاوى', 'icon' => 'fa-headset', 'view' => 'admin.support.view', 'manage' => 'admin.support.manage'],
                            ['title' => 'إدارة الأدوار والمستخدمين', 'icon' => 'fa-users-gear', 'view' => 'admin.users.view', 'manage' => 'admin.users.manage'],
                            ['title' => 'إعدادات المنظومة', 'icon' => 'fa-sliders', 'view' => 'admin.settings.view', 'manage' => 'admin.settings.manage'],
                        ];
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach($screens as $screen)
                            @php
                                $canView = $isSuperAdmin || in_array($screen['view'], $userPermissions);
                                $canManage = $isSuperAdmin || in_array($screen['manage'], $userPermissions);
                            @endphp
                            <div class="p-3.5 rounded-xl border {{ $canView ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50/60 opacity-60' }} flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg {{ $canView ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-sm flex-shrink-0">
                                    <i class="fa-solid {{ $screen['icon'] }}"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-xs {{ $canView ? 'text-slate-900' : 'text-slate-500' }}">{{ $screen['title'] }}</div>
                                    <div class="mt-1 flex items-center gap-1.5">
                                        @if($canView)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <i class="fa-solid fa-eye ml-0.5"></i> تظهر بالقائمة
                                            </span>
                                            @if($canManage)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                                    إدارة كاملة
                                                </span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    عرض فقط
                                                </span>
                                            @endif
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600">
                                                <i class="fa-solid fa-ban ml-0.5"></i> محجوبة
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- TAB 3: Activity & Audit Log (من أسند / من اعتمد) -->
            <div x-show="activeTab === 'activity'" class="space-y-6">
                <!-- 1. Orders Assigned by This Employee -->
                <div>
                    <h4 class="font-bold text-slate-800 text-sm mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-user text-primary-600"></i>
                        <span>الطلبات التي أسندها هذا الموظف للفنيين ({{ $assignedOrders->count() }})</span>
                    </h4>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3">رقم الطلب</th>
                                    <th class="p-3">الخدمة</th>
                                    <th class="p-3">الحرفي الذي تم الإسناد إليه</th>
                                    <th class="p-3">تاريخ ووقت الإسناد</th>
                                    <th class="p-3 text-center">عرض الطلب</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($assignedOrders as $assign)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="p-3 font-mono font-bold text-emerald-700">
                                            #{{ $assign->request->clean_number ?? $assign->request_id }}
                                        </td>
                                        <td class="p-3 font-semibold text-slate-800">
                                            {{ $assign->request->service->name ?? '-' }}
                                        </td>
                                        <td class="p-3">
                                            <div class="font-bold text-slate-800">{{ $assign->craftsman->full_name ?? '-' }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $assign->craftsman->phone ?? '' }}</div>
                                        </td>
                                        <td class="p-3 font-mono text-slate-500">
                                            {{ $assign->assigned_at ? $assign->assigned_at->format('Y-m-d H:i') : $assign->created_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <a href="{{ route('admin.orders.show', $assign->request_id) }}" class="text-blue-600 hover:underline font-bold">
                                                تفاصيل الطلب
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-slate-400">لم يسند هذا الموظف أي طلبات بعد</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Status Updates & Approvals by This Employee -->
                <div>
                    <h4 class="font-bold text-slate-800 text-sm mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-check-to-slot text-emerald-600"></i>
                        <span>تحديثات الحالات واعتمادات السندات التي نفذها الموظف ({{ $statusChanges->count() }})</span>
                    </h4>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3">الطلب المرتبط</th>
                                    <th class="p-3">البيان وملاحظة العملية</th>
                                    <th class="p-3">التاريخ والوقت</th>
                                    <th class="p-3 text-center">عرض الطلب</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($statusChanges as $sc)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="p-3 font-mono font-bold text-emerald-700">
                                            #{{ $sc->request->clean_number ?? $sc->request_id }}
                                        </td>
                                        <td class="p-3 font-semibold text-slate-800">
                                            {{ $sc->notes ?? 'تعديل حالة الطلب' }}
                                        </td>
                                        <td class="p-3 font-mono text-slate-500">
                                            {{ $sc->created_at ? $sc->created_at->format('Y-m-d H:i') : '-' }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <a href="{{ route('admin.orders.show', $sc->request_id) }}" class="text-blue-600 hover:underline font-bold">
                                                معاينة
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-8 text-center text-slate-400">لا توجد عمليات تغيير حالة مسجلة باسم هذا الموظف بعد</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. System Activity Log -->
                @if($activityLogs->isNotEmpty())
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-list-ol text-slate-600"></i>
                            <span>سجل العمليات الإدارية الموثقة (Audit Trail)</span>
                        </h4>

                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-right text-xs">
                                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="p-3">العملية</th>
                                        <th class="p-3">الوصف</th>
                                        <th class="p-3">عنوان IP</th>
                                        <th class="p-3">الوقت</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($activityLogs as $log)
                                        <tr class="hover:bg-slate-50/70 transition">
                                            <td class="p-3 font-mono font-bold text-slate-800">{{ $log->action }}</td>
                                            <td class="p-3 text-slate-700">{{ $log->description }}</td>
                                            <td class="p-3 font-mono text-slate-400">{{ $log->ip_address }}</td>
                                            <td class="p-3 font-mono text-slate-500">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL 1: Add Financial Voucher (إصدار سند صرف عهدة / سلفة أو تسوية مصاريف) -->
    <div x-show="showVoucherModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div @click.away="showVoucherModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave text-primary-600"></i>
                    <span>إصدار سند مالي للموظف (عهدة / سلفة / تسوية)</span>
                </h3>
                <button @click="showVoucherModal = false" class="text-slate-400 hover:text-slate-600 text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.employees.financial', $employee->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <!-- Voucher Type: Debit or Credit -->
                <div class="grid grid-cols-2 gap-3">
                    <label class="p-3 rounded-xl border cursor-pointer text-center font-bold transition" :class="voucherType === 'debit' ? 'border-red-500 bg-red-50 text-red-700' : 'border-slate-200 text-slate-600'">
                        <input type="radio" name="transaction_type" value="debit" x-model="voucherType" class="hidden">
                        <i class="fa-solid fa-arrow-down mb-1 block text-base"></i>
                        <span>سند صرف عهدة / سلفة (مدين +)</span>
                        <span class="block text-[10px] font-normal text-slate-400 mt-0.5">يزيد ما على الموظف للمنظومة</span>
                    </label>

                    <label class="p-3 rounded-xl border cursor-pointer text-center font-bold transition" :class="voucherType === 'credit' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-600'">
                        <input type="radio" name="transaction_type" value="credit" x-model="voucherType" class="hidden">
                        <i class="fa-solid fa-arrow-up mb-1 block text-base"></i>
                        <span>سند تسوية / سداد (دائن -)</span>
                        <span class="block text-[10px] font-normal text-slate-400 mt-0.5">يخفض العهدة أو يصرف مستحقات</span>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">تصنيف السند *</label>
                        <select name="category" x-model="voucherCategory" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                            <template x-if="voucherType === 'debit'">
                                <option value="custody">عهدة نقدية للمشتريات والمصروفات</option>
                            </template>
                            <template x-if="voucherType === 'debit'">
                                <option value="advance">سلفة شخصية على الراتب</option>
                            </template>
                            <template x-if="voucherType === 'credit'">
                                <option value="expense_settlement">تسوية مصاريف وفواتير رسمية</option>
                            </template>
                            <template x-if="voucherType === 'credit'">
                                <option value="salary">صرف راتب / أجر مستحق</option>
                            </template>
                            <template x-if="voucherType === 'credit'">
                                <option value="repayment">سداد وتوريد نقدي للصندوق</option>
                            </template>
                            <option value="other">أخرى / تسوية قيد</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">المبلغ (بالريال اليمني) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="0.00" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold focus:ring-2 focus:ring-primary-500 outline-none text-left" dir="ltr">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">تاريخ المعاملة *</label>
                        <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">طريقة الدفع / الصرف *</label>
                        <select name="payment_method" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="نقداً من الخزينة">نقداً من الخزينة (كاش)</option>
                            <option value="تحويل بنكي / بنك الكريمي">تحويل بنكي / بنك الكريمي</option>
                            <option value="محفظة جوالي">محفظة جوالي</option>
                            <option value="محفظة جيب">محفظة جيب</option>
                            <option value="محفظة ون كاش">محفظة ون كاش</option>
                            <option value="قيد محاسبي دفتري">قيد محاسبي دفتري</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">رقم مرجع السند / رقم الشيك (اختياري)</label>
                    <input type="text" name="reference_number" placeholder="مثال: VCH-{{ time() }}" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">البيان والشرح التفصيلي للعملية *</label>
                    <textarea name="description" rows="2" required placeholder="اكتب تفاصيل العهدة أو المصاريف المسواة ورقم الفواتير..." class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">إرفاق صورة سند الصرف / الفاتورة (اختياري)</label>
                    <input type="file" name="receipt_attachment" accept="image/*" class="w-full p-2 rounded-xl border border-slate-200 text-xs bg-slate-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:bg-primary-50 file:text-primary-700">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showVoucherModal = false" class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-bold shadow-md shadow-primary-600/20 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>اعتماد وحفظ السند</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Edit Employee Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div @click.away="showEditModal = false" class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-blue-600"></i>
                    <span>تعديل بيانات الموظف</span>
                </h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">اسم الموظف الكامل *</label>
                        <input type="text" name="full_name" value="{{ $employee->user->full_name ?? '' }}" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">رقم الهاتف *</label>
                        <input type="text" name="phone" value="{{ $employee->user->phone ?? '' }}" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ $employee->user->email ?? '' }}" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none" dir="ltr">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">الرقم الوظيفي *</label>
                        <input type="text" name="employee_number" value="{{ $employee->employee_number }}" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-mono font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">المسمى الوظيفي</label>
                        <input type="text" name="job_title" value="{{ $employee->job_title }}" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">القسم الإداري</label>
                        <select name="department_id" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="">بدون قسم</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ ($primaryDept?->department_id == $dept->id) ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">نوع التوظيف</label>
                        <select name="employment_type" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="FULL_TIME" {{ $employee->employment_type === 'FULL_TIME' ? 'selected' : '' }}>دوام كامل</option>
                            <option value="PART_TIME" {{ $employee->employment_type === 'PART_TIME' ? 'selected' : '' }}>دوام جزئي</option>
                            <option value="CONTRACT" {{ $employee->employment_type === 'CONTRACT' ? 'selected' : '' }}>عقد</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">حالة الموظف</label>
                        <select name="employment_status" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="ACTIVE" {{ $employee->employment_status === 'ACTIVE' ? 'selected' : '' }}>على رأس العمل (ACTIVE)</option>
                            <option value="ON_LEAVE" {{ $employee->employment_status === 'ON_LEAVE' ? 'selected' : '' }}>إجازة (ON_LEAVE)</option>
                            <option value="TERMINATED" {{ $employee->employment_status === 'TERMINATED' ? 'selected' : '' }}>إنهاء خدمة (TERMINATED)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">تاريخ التعيين</label>
                        <input type="date" name="hire_date" value="{{ $employee->hire_date }}" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">المسؤول المباشر</label>
                        <select name="reports_to_employee_id" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="">بدون (الإدارة العليا)</option>
                            @foreach($managers as $m)
                                <option value="{{ $m->id }}" {{ $employee->reports_to_employee_id == $m->id ? 'selected' : '' }}>{{ $m->user->full_name ?? $m->employee_number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">ملاحظات ووثائق</label>
                    <textarea name="notes" rows="2" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-primary-500 outline-none">{{ $employee->notes }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md shadow-blue-600/20 transition">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Delete Confirmation -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div @click.away="showDeleteModal = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4 text-center">
            <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto text-2xl shadow-inner">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            
            <h3 class="font-bold text-slate-900 text-base">تأكيد حذف الموظف</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                هل أنت متأكد من رغبتك في حذف الموظف <strong class="text-slate-800">{{ $employee->user->full_name ?? $employee->employee_number }}</strong>؟
            </p>

            <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" class="flex items-center justify-center gap-3 pt-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition w-full">إلغاء</button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-md shadow-red-600/20 transition w-full">تأكيد الحذف</button>
            </form>
        </div>
    </div>

</div>
@endsection
