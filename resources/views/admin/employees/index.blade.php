@extends('admin.layout')

@section('title', 'الموارد البشرية')
@section('page_title', 'إدارة الموظفين والأقسام الإدارية (HR & Departments)')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'employees',
    showAddEmployeeModal: false,
    addEmployeeUserMode: 'new',
    showEditEmployeeModal: false,
    editEmployeeData: {},
    editEmployeeUrl: '',
    showAddDeptModal: false,
    showEditDeptModal: false,
    editDeptData: {},
    editDeptUrl: ''
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

    <!-- Top Action Bar & Tabs -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-2 p-1.5 bg-slate-100 rounded-xl">
            <button @click="activeTab = 'employees'" :class="activeTab === 'employees' ? 'bg-white text-primary-700 shadow-sm font-bold' : 'text-slate-600 font-semibold'" class="px-5 py-2 rounded-lg text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-users"></i>
                <span>سجل الموظفين ({{ $employees->total() }})</span>
            </button>
            <button @click="activeTab = 'departments'" :class="activeTab === 'departments' ? 'bg-white text-primary-700 shadow-sm font-bold' : 'text-slate-600 font-semibold'" class="px-5 py-2 rounded-lg text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-sitemap"></i>
                <span>الهيكل والأقسام ({{ $departments->count() }})</span>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <button x-show="activeTab === 'employees'" @click="showAddEmployeeModal = true" class="px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>تسجيل موظف جديد</span>
            </button>
            <button x-show="activeTab === 'departments'" @click="showAddDeptModal = true" class="px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-folder-plus"></i>
                <span>إضافة قسم جديد</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: Employees Section -->
    <div x-show="activeTab === 'employees'" class="space-y-4">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3.5">الرقم الوظيفي</th>
                            <th class="px-4 py-3.5">اسم الموظف</th>
                            <th class="px-4 py-3.5">المسمى الوظيفي</th>
                            <th class="px-4 py-3.5">القسم</th>
                            <th class="px-4 py-3.5">الدور والصلاحيات</th>
                            <th class="px-4 py-3.5">رصيد العهدة (مدين/دائن)</th>
                            <th class="px-4 py-3.5">الحالة</th>
                            <th class="px-4 py-3.5">تاريخ التعيين</th>
                            <th class="px-4 py-3.5 text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $employee)
                            @php
                                $primaryDept = $employee->employeeDepartments->firstWhere('is_primary', true) ?? $employee->employeeDepartments->first();
                                $debit = $employee->total_debit;
                                $credit = $employee->total_credit;
                                $bal = $employee->balance;
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3.5 font-mono font-bold">
                                    <a href="{{ route('admin.employees.show', $employee->id) }}" class="text-primary-700 hover:text-primary-800 hover:underline">
                                        #{{ $employee->employee_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3.5 font-bold text-slate-800">
                                    <a href="{{ route('admin.employees.show', $employee->id) }}" class="flex items-center gap-2 group">
                                        <div class="w-7 h-7 rounded-lg bg-primary-50 text-primary-700 flex items-center justify-center font-bold text-xs border border-primary-200 group-hover:bg-primary-600 group-hover:text-white transition">
                                            {{ mb_substr($employee->user->full_name ?? $employee->employee_number, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="group-hover:text-primary-600 group-hover:underline transition">{{ $employee->user->full_name ?? '-' }}</div>
                                            <div class="text-[11px] text-slate-400 font-mono font-normal">{{ $employee->user->phone ?? '' }}</div>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-4 py-3.5 text-slate-700 font-medium">{{ $employee->job_title ?? '-' }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ $primaryDept?->department?->name ?? 'بدون قسم' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @forelse($employee->user->roles ?? [] as $r)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            {{ $r->name }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 text-[11px]">بدون دور</span>
                                    @endforelse
                                </td>
                                <td class="px-4 py-3.5 font-mono text-xs">
                                    @if($bal > 0)
                                        <span class="font-bold text-red-600 block">{{ number_format($bal, 2) }} ر.ي</span>
                                        <span class="text-[10px] text-slate-400 font-sans">بعهدته (مدين)</span>
                                    @elseif($bal < 0)
                                        <span class="font-bold text-emerald-600 block">{{ number_format(abs($bal), 2) }} ر.ي</span>
                                        <span class="text-[10px] text-slate-400 font-sans">مستحق له (دائن)</span>
                                    @else
                                        <span class="text-slate-500 font-bold">0.00 ر.ي</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($employee->employment_status === 'ACTIVE')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">على رأس العمل</span>
                                    @elseif($employee->employment_status === 'ON_LEAVE')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">إجازة</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-200">منتهي الخدمة</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-500">{{ $employee->hire_date }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- View Employee Details & Ledger Button -->
                                        <a href="{{ route('admin.employees.show', $employee->id) }}" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="عرض تفاصيل الموظف وكشف حسابه والصلاحيات">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <!-- Edit Button -->
                                        <button @click="editEmployeeData = {
                                            id: {{ $employee->id }},
                                            employee_number: '{{ addslashes($employee->employee_number) }}',
                                            job_title: '{{ addslashes($employee->job_title ?? '') }}',
                                            employment_type: '{{ $employee->employment_type }}',
                                            employment_status: '{{ $employee->employment_status }}',
                                            hire_date: '{{ $employee->hire_date }}',
                                            termination_date: '{{ $employee->termination_date ?? '' }}',
                                            department_id: '{{ $primaryDept?->department_id ?? '' }}',
                                            reports_to_employee_id: '{{ $employee->reports_to_employee_id ?? '' }}',
                                            notes: '{{ addslashes($employee->notes ?? '') }}',
                                            user_name: '{{ addslashes($employee->user->full_name ?? '') }}',
                                            phone: '{{ $employee->user->phone ?? '' }}',
                                            email: '{{ $employee->user->email ?? '' }}',
                                            role_id: '{{ $employee->user->roles->first()->id ?? '' }}'
                                        }; editEmployeeUrl = '{{ route('admin.employees.update', $employee->id) }}'; showEditEmployeeModal = true" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل الموظف">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا الموظف؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف الموظف">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-slate-400">لا يوجد موظفون مسجلون بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
                <div class="border-t border-slate-100 p-4">{{ $employees->links() }}</div>
            @endif
        </section>
    </div>

    <!-- TAB 2: Departments Section -->
    <div x-show="activeTab === 'departments'" class="space-y-4">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3.5">رمز القسم</th>
                            <th class="px-4 py-3.5">اسم القسم</th>
                            <th class="px-4 py-3.5">القسم الأعلى</th>
                            <th class="px-4 py-3.5">مدير القسم</th>
                            <th class="px-4 py-3.5">عدد الموظفين</th>
                            <th class="px-4 py-3.5">الحالة</th>
                            <th class="px-4 py-3.5 text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($departments as $dept)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3.5 font-mono font-bold text-primary-700">{{ $dept->code }}</td>
                                <td class="px-4 py-3.5 font-bold text-slate-800">{{ $dept->name }}</td>
                                <td class="px-4 py-3.5 text-slate-600">{{ $dept->parentDepartment->name ?? 'رئيسي' }}</td>
                                <td class="px-4 py-3.5 font-medium text-slate-700">{{ $dept->managerEmployee->user->full_name ?? '-' }}</td>
                                <td class="px-4 py-3.5 font-bold text-primary-700">{{ $dept->employee_departments_count }} موظفين</td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $dept->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $dept->is_active ? 'نشط' : 'معطل' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button @click="editDeptData = {
                                            id: {{ $dept->id }},
                                            name: '{{ addslashes($dept->name) }}',
                                            code: '{{ addslashes($dept->code) }}',
                                            parent_department_id: '{{ $dept->parent_department_id ?? '' }}',
                                            manager_employee_id: '{{ $dept->manager_employee_id ?? '' }}',
                                            description: '{{ addslashes($dept->description ?? '') }}',
                                            is_active: {{ $dept->is_active ? '1' : '0' }}
                                        }; editDeptUrl = '{{ route('admin.employees.departments.update', $dept->id) }}'; showEditDeptModal = true" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل القسم">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('admin.employees.departments.destroy', $dept->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا القسم؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف القسم">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400">لا توجد أقسام مسجلة بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- MODAL 1: Add Employee -->
    <div x-show="showAddEmployeeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="showAddEmployeeModal = false" class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-primary-600"></i>
                    <span>تسجيل وتعيين موظف جديد في المنظومة</span>
                </h3>
                <button @click="showAddEmployeeModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- Mode Selector: New User or Existing User -->
            <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-xl">
                <button type="button" @click="addEmployeeUserMode = 'new'" :class="addEmployeeUserMode === 'new' ? 'bg-white text-primary-700 shadow-sm font-bold' : 'text-slate-600 font-semibold'" class="flex-1 py-1.5 rounded-lg text-xs transition text-center">
                    <i class="fa-solid fa-user-plus ml-1"></i> حساب مستخدم جديد
                </button>
                <button type="button" @click="addEmployeeUserMode = 'existing'" :class="addEmployeeUserMode === 'existing' ? 'bg-white text-primary-700 shadow-sm font-bold' : 'text-slate-600 font-semibold'" class="flex-1 py-1.5 rounded-lg text-xs transition text-center">
                    <i class="fa-solid fa-users ml-1"></i> اختيار مستخدم مسجل
                </button>
            </div>

            <form action="{{ route('admin.employees.store') }}" method="POST" class="grid gap-4 sm:grid-cols-2">
                @csrf
                <input type="hidden" name="user_mode" :value="addEmployeeUserMode">

                <!-- New User Fields -->
                <template x-if="addEmployeeUserMode === 'new'">
                    <div class="sm:col-span-2 grid sm:grid-cols-2 gap-4 p-3 bg-slate-50/80 rounded-2xl border border-slate-200/70">
                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                            اسم الموظف الكامل *
                            <input type="text" name="full_name" required placeholder="مثال: م. أحمد صالح الزبيري" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold">
                        </label>

                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                            رقم الهاتف الجوال *
                            <input type="text" name="phone" required placeholder="مثال: 771234567" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-mono" dir="ltr">
                        </label>

                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                            البريد الإلكتروني (اختياري)
                            <input type="email" name="email" placeholder="employee@herfa.com" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs" dir="ltr">
                        </label>

                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                            كلمة المرور لتسجيل الدخول *
                            <input type="password" name="password" required placeholder="******" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-mono">
                        </label>
                    </div>
                </template>

                <!-- Existing User Selector -->
                <template x-if="addEmployeeUserMode === 'existing'">
                    <label class="grid gap-1 text-xs font-semibold text-slate-600 sm:col-span-2">
                        المستخدم المسجل في المنظومة *
                        <select name="user_id" required class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                            <option value="">اختر مستخدماً من النظام</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name }} — {{ $user->phone }}</option>
                            @endforeach
                        </select>
                    </label>
                </template>

                <!-- Employee Number & Job Title -->
                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    الرقم الوظيفي *
                    <input name="employee_number" maxlength="30" required value="EMP-{{ time() }}" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 font-mono text-xs font-bold text-primary-700">
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    المسمى الوظيفي
                    <input name="job_title" maxlength="150" placeholder="مثال: مشرف طلبات / محاسب" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                </label>

                <!-- Role / Permissions Assignment -->
                <label class="grid gap-1 text-xs font-semibold text-slate-600 sm:col-span-2">
                    الدور والصلاحيات بالنظام (الواجهات المتاحة للموظف)
                    <select name="role_id" class="rounded-xl border border-slate-200 bg-amber-50/50 px-3 py-2.5 text-xs font-bold text-slate-800">
                        <option value="">موظف بدون دور خاص (حسب الصلاحيات الافتراضية)</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->description ?? 'دور مخصص' }})</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    نوع التوظيف
                    <select name="employment_type" required class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="FULL_TIME">دوام كامل</option>
                        <option value="PART_TIME">دوام جزئي</option>
                        <option value="CONTRACT">عقد</option>
                    </select>
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    تاريخ التعيين *
                    <input type="date" name="hire_date" required value="{{ date('Y-m-d') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600 sm:col-span-2">
                    القسم الإداري
                    <select name="department_id" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="">بدون قسم</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600 sm:col-span-2">
                    المسؤول المباشر
                    <select name="reports_to_employee_id" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="">بدون (الإدارة العليا)</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->employee_number }} — {{ $manager->user->full_name ?? '' }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600 sm:col-span-2">
                    ملاحظات ووثائق
                    <textarea name="notes" rows="2" placeholder="ملاحظات العقد والبدلات..." class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs"></textarea>
                </label>

                <div class="sm:col-span-2 flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showAddEmployeeModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100">إلغاء</button>
                    <button type="submit" class="rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-primary-700 shadow-md shadow-primary-600/20 transition">
                        حفظ الموظف وتعيين الصلاحيات
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Edit Employee -->
    <div x-show="showEditEmployeeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="showEditEmployeeModal = false" class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-blue-600"></i>
                    <span>تعديل بيانات الموظف: <span class="text-primary-600" x-text="editEmployeeData.user_name"></span></span>
                </h3>
                <button @click="showEditEmployeeModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form :action="editEmployeeUrl" method="POST" class="grid gap-4 sm:grid-cols-2">
                @csrf
                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    اسم الموظف الكامل
                    <input name="full_name" x-model="editEmployeeData.user_name" maxlength="150" required class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-bold">
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    رقم الهاتف الجوال
                    <input name="phone" x-model="editEmployeeData.phone" maxlength="20" required class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 font-mono text-xs" dir="ltr">
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600 sm:col-span-2">
                    الدور والصلاحيات الوظيفية
                    <select name="role_id" x-model="editEmployeeData.role_id" class="rounded-xl border border-slate-200 bg-amber-50/50 px-3 py-2.5 text-xs font-bold text-slate-800">
                        <option value="">بدون دور خاص</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->description ?? 'دور مخصص' }})</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    الرقم الوظيفي
                    <input name="employee_number" x-model="editEmployeeData.employee_number" maxlength="30" required class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 font-mono text-xs">
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    المسمى الوظيفي
                    <input name="job_title" x-model="editEmployeeData.job_title" maxlength="150" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    نوع التوظيف
                    <select name="employment_type" x-model="editEmployeeData.employment_type" required class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="FULL_TIME">دوام كامل</option>
                        <option value="PART_TIME">دوام جزئي</option>
                        <option value="CONTRACT">عقد</option>
                    </select>
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    حالة الموظف
                    <select name="employment_status" x-model="editEmployeeData.employment_status" required class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="ACTIVE">على رأس العمل (ACTIVE)</option>
                        <option value="ON_LEAVE">إجازة (ON_LEAVE)</option>
                        <option value="TERMINATED">إنهاء خدمة (TERMINATED)</option>
                    </select>
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    تاريخ التعيين
                    <input type="date" name="hire_date" x-model="editEmployeeData.hire_date" required class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600">
                    تاريخ إنهاء الخدمة (إن وجد)
                    <input type="date" name="termination_date" x-model="editEmployeeData.termination_date" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600 sm:col-span-2">
                    القسم الإداري
                    <select name="department_id" x-model="editEmployeeData.department_id" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="">بدون قسم</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600 sm:col-span-2">
                    المسؤول المباشر
                    <select name="reports_to_employee_id" x-model="editEmployeeData.reports_to_employee_id" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="">بدون (الإدارة العليا)</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->employee_number }} — {{ $manager->user->full_name ?? '' }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-1 text-xs font-semibold text-slate-600 sm:col-span-2">
                    ملاحظات
                    <textarea name="notes" x-model="editEmployeeData.notes" rows="2" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs"></textarea>
                </label>

                <div class="sm:col-span-2 flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showEditEmployeeModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100">إلغاء</button>
                    <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-md shadow-blue-600/20 transition">
                        تحديث بيانات الموظف والصلاحيات
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Add Department -->
    <div x-show="showAddDeptModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="showAddDeptModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-folder-plus text-primary-600"></i>
                    <span>إضافة قسم جديد</span>
                </h3>
                <button @click="showAddDeptModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.employees.departments.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم القسم</label>
                    <input name="name" maxlength="150" required placeholder="مثال: قسم الجودة والرقابة" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رمز القسم (الكود)</label>
                    <input name="code" maxlength="30" required placeholder="مثال: QA" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 font-mono text-xs uppercase">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">القسم الأعلى (إن وجد)</label>
                    <select name="parent_department_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="">بدون (قسم رئيسي)</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">مدير القسم</label>
                    <select name="manager_employee_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="">بدون مدير حالياً</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->employee_number }} — {{ $manager->user->full_name ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">وصف القسم</label>
                    <textarea name="description" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs"></textarea>
                </div>
                <button type="submit" class="w-full rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-primary-700">
                    حفظ القسم
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL 4: Edit Department -->
    <div x-show="showEditDeptModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="showEditDeptModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                    <span>تعديل بيانات القسم</span>
                </h3>
                <button @click="showEditDeptModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form :action="editDeptUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم القسم</label>
                    <input name="name" x-model="editDeptData.name" maxlength="150" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رمز القسم (الكود)</label>
                    <input name="code" x-model="editDeptData.code" maxlength="30" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 font-mono text-xs uppercase">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">القسم الأعلى</label>
                    <select name="parent_department_id" x-model="editDeptData.parent_department_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="">بدون (قسم رئيسي)</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">مدير القسم</label>
                    <select name="manager_employee_id" x-model="editDeptData.manager_employee_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="">بدون</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->employee_number }} — {{ $manager->user->full_name ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">حالة القسم</label>
                    <select name="is_active" x-model="editDeptData.is_active" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs">
                        <option value="1">نشط</option>
                        <option value="0">معطل</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">وصف القسم</label>
                    <textarea name="description" x-model="editDeptData.description" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs"></textarea>
                </div>
                <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700">
                    تحديث بيانات القسم
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
