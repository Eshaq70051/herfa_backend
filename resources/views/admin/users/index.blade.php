@extends('admin.layout')

@section('title', 'المستخدمين والحسابات')
@section('page_title', 'إدارة المستخدمين والحسابات (users)')

@section('content')
<div class="space-y-6">

    <!-- Filters & Add button -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث بالاسم أو البريد أو الهاتف..." 
                class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500 w-64">

            <select name="account_status" class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                <option value="">جميع الحالات</option>
                <option value="active" {{ request('account_status') == 'active' ? 'selected' : '' }}>نشط (Active)</option>
                <option value="inactive" {{ request('account_status') == 'inactive' ? 'selected' : '' }}>معطل (Inactive)</option>
                <option value="suspended" {{ request('account_status') == 'suspended' ? 'selected' : '' }}>معلق (Suspended)</option>
            </select>

            <select name="role_id" class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                <option value="">جميع الأدوار</option>
                @foreach($roles as $r)
                    <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                بحث وتصفية
            </button>
        </form>

        <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>إضافة مستخدم جديد</span>
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-400 font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">الاسم الكامل</th>
                        <th class="py-3.5 px-4">رقم الهاتف</th>
                        <th class="py-3.5 px-4">البريد الإلكتروني</th>
                        <th class="py-3.5 px-4">الأدوار الممنوحة</th>
                        <th class="py-3.5 px-4">حالة الحساب</th>
                        <th class="py-3.5 px-4">تاريخ التسجيل</th>
                        <th class="py-3.5 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $u->full_name }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">{{ $u->phone_encrypted ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $u->email_encrypted ?? '-' }}</td>
                            <td class="py-3.5 px-4">
                                @forelse($u->roles as $ur)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $ur->name }}
                                    </span>
                                @empty
                                    <span class="text-slate-400">بدون دور</span>
                                @endforelse
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold 
                                    {{ $u->account_status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $u->account_status === 'inactive' ? 'bg-slate-100 text-slate-600' : '' }}
                                    {{ $u->account_status === 'suspended' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}">
                                    {{ $u->account_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-400">{{ $u->created_at ? $u->created_at->format('Y-m-d') : '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- زر التعديل -->
                                    <button onclick="editUser({{ json_encode($u) }}, '{{ $u->roles->first()->id ?? '' }}')" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل المستخدم">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- تبديل الحالة -->
                                    <form action="{{ route('admin.users.status', $u->id) }}" method="POST" class="inline">
                                        @csrf
                                        @if($u->account_status === 'active')
                                            <input type="hidden" name="account_status" value="suspended">
                                            <button type="submit" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition" title="تعليق الحساب"><i class="fa-solid fa-user-lock"></i></button>
                                        @else
                                            <input type="hidden" name="account_status" value="active">
                                            <button type="submit" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="تنشيط الحساب"><i class="fa-solid fa-user-check"></i></button>
                                        @endif
                                    </form>

                                    <!-- زر الحذف -->
                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائياً؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف المستخدم">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">لا يوجد مستخدمين مسجلين وفق معايير البحث</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Add Modal -->
    <div id="addUserModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
            <h4 class="font-bold text-slate-800 text-sm border-b pb-2">إضافة مستخدم جديد للنظام</h4>
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الاسم الكامل</label>
                    <input type="text" name="full_name" required placeholder="مثال: أحمد عبد الله" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رقم الهاتف</label>
                    <input type="text" name="phone" required placeholder="777123456" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">البريد الإلكتروني (اختياري)</label>
                    <input type="email" name="email" placeholder="user@enjiz.com" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">كلمة المرور</label>
                    <input type="password" name="password" required placeholder="" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الدور والصلاحية</label>
                    <select name="role_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">حالة الحساب</label>
                    <select name="account_status" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                        <option value="active">نشط (active)</option>
                        <option value="inactive">معطل (inactive)</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="px-3 py-1.5 bg-slate-100 rounded-lg text-xs">إلغاء</button>
                    <button type="submit" class="px-4 py-1.5 bg-brand-600 text-white rounded-lg text-xs font-bold">إنشاء المستخدم</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
            <h4 class="font-bold text-slate-800 text-sm border-b pb-2">تعديل بيانات المستخدم</h4>
            <form id="editUserForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الاسم الكامل</label>
                    <input type="text" id="edit_u_name" name="full_name" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">رقم الهاتف</label>
                    <input type="text" id="edit_u_phone" name="phone" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">البريد الإلكتروني</label>
                    <input type="email" id="edit_u_email" name="email" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">تغيير كلمة المرور (اتركه فارغاً إذا لم ترغب بالتغيير)</label>
                    <input type="password" name="password" placeholder="كلمة مرور جديدة..." class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الدور والصلاحية</label>
                    <select id="edit_u_role_id" name="role_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">حالة الحساب</label>
                    <select id="edit_u_status" name="account_status" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                        <option value="active">نشط (active)</option>
                        <option value="inactive">معطل (inactive)</option>
                        <option value="suspended">معلق (suspended)</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="px-3 py-1.5 bg-slate-100 rounded-lg text-xs">إلغاء</button>
                    <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function editUser(user, roleId) {
        document.getElementById('editUserForm').action = '/admin/users/' + user.id;
        document.getElementById('edit_u_name').value = user.full_name;
        document.getElementById('edit_u_phone').value = user.phone_encrypted || '';
        document.getElementById('edit_u_email').value = user.email_encrypted || '';
        document.getElementById('edit_u_status').value = user.account_status;
        if (roleId) {
            document.getElementById('edit_u_role_id').value = roleId;
        }
        document.getElementById('editUserModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
