@extends('admin.layout')

@section('title', 'الأدوار والصلاحيات')
@section('page_title', 'الأدوار ومصفوفة الصلاحيات (RBAC)')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="font-bold text-slate-800 text-base">أدوار ومجموعات صلاحيات النظام</h3>
            <p class="text-xs text-slate-400">إدارة الأدوار (roles) وتوزيع الصلاحيات على المشرفين والمستخدمين</p>
        </div>
        @can('admin.users.manage')
            <button onclick="document.getElementById('addRoleModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>إضافة دور جديد</span>
            </button>
        @endcan
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between">
                    <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-slate-100 text-slate-700">
                        {{ $role->code }}
                    </span>
                </div>

                <div>
                    <h4 class="font-bold text-slate-800 text-base">{{ $role->name }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $role->description ?? 'لا يوجد وصف' }}</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">
                        <strong class="text-slate-800">{{ $role->users_count }}</strong> مستخدمين
                    </span>
                    @if($role->is_system)
                        <span class="text-amber-600 font-semibold bg-amber-50 px-2 py-0.5 rounded text-[11px]">دور نظام أساسي</span>
                    @elseif(auth()->user()->can('admin.users.manage'))
                        <div class="inline-flex items-center gap-1">
                            <button onclick="editRole({{ json_encode($role) }})" class="p-1 text-blue-600 hover:bg-blue-50 rounded" title="تعديل الدور">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <form action="{{ route('admin.users.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا الدور؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-red-500 hover:bg-red-50 rounded" title="حذف الدور">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                @if(!$role->is_system && auth()->user()->can('admin.users.manage'))
                    <form action="{{ route('admin.users.roles.permissions', $role->id) }}" method="POST" class="border-t border-slate-100 pt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        @foreach($permissionGroups as $group)
                            <fieldset>
                                <legend class="text-xs font-bold text-slate-700 mb-2">{{ $group->name }}</legend>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($group->permissions as $permission)
                                        <label class="flex items-center gap-2 text-[11px] text-slate-600">
                                            <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}"
                                                @checked($role->permissions->contains('id', $permission->id))
                                                class="rounded text-blue-600">
                                            <span>{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                        <button type="submit" class="w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold">
                            حفظ الصلاحيات
                        </button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Add Modal -->
    @can('admin.users.manage')
    <div id="addRoleModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
            <h4 class="font-bold text-slate-800 text-sm border-b pb-2">إضافة دور جديد</h4>
            <form action="{{ route('admin.users.roles.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم الدور بالعربية</label>
                    <input type="text" name="name" required placeholder="مثال: مشرف دعم فني" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الرمز البرمجي (Code)</label>
                    <input type="text" name="code" required placeholder="مثال: support_supervisor" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الوصف</label>
                    <textarea name="description" rows="2" placeholder="وصف مهام هذا الدور..." class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('addRoleModal').classList.add('hidden')" class="px-3 py-1.5 bg-slate-100 rounded-lg text-xs">إلغاء</button>
                    <button type="submit" class="px-4 py-1.5 bg-brand-600 text-white rounded-lg text-xs font-bold">حفظ الدور</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editRoleModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
            <h4 class="font-bold text-slate-800 text-sm border-b pb-2">تعديل الدور</h4>
            <form id="editRoleForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">اسم الدور بالعربية</label>
                    <input type="text" id="edit_role_name" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs">
                </div>
                @endcan
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">الوصف</label>
                    <textarea id="edit_role_desc" name="description" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl text-xs"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('editRoleModal').classList.add('hidden')" class="px-3 py-1.5 bg-slate-100 rounded-lg text-xs">إلغاء</button>
                    <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold">حفظ التعديل</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function editRole(role) {
        document.getElementById('editRoleForm').action = @json(route('admin.users.roles.update', ['id' => '__ROLE__'])).replace('__ROLE__', role.id);
        document.getElementById('edit_role_name').value = role.name;
        document.getElementById('edit_role_desc').value = role.description || '';
        document.getElementById('editRoleModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
