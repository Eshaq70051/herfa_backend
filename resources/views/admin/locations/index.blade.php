@extends('admin.layout')

@section('title', 'إدارة المواقع الجغرافية')
@section('page_title', 'المواقع والمناطق الجغرافية (المحافظات، المديريات، الأحياء)')

@section('content')
<div class="space-y-6" x-data="{
    showAddGovModal: false,
    showEditGovModal: false,
    showDeleteGovModal: false,
    selectedGov: {},

    showAddDistModal: false,
    showEditDistModal: false,
    showDeleteDistModal: false,
    selectedDist: {},

    showAddNeighModal: false,
    showEditNeighModal: false,
    showDeleteNeighModal: false,
    selectedNeigh: {}
}">

    <!-- Top Action Banner -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-800">التغطية الجغرافية لمنصة حِرفة</h3>
            <p class="text-xs text-gray-500 mt-1">إدارة المحافظات والمديريات والأحياء المشمولة بخدمات الصيانة المنزلية</p>
        </div>
        <button @click="showAddGovModal = true" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة محافظة / منطقة جديدة</span>
        </button>
    </div>

    <!-- Governorates Grid -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($governorates as $gov)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <!-- Governorate Header -->
                <div class="p-5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold">
                            <i class="fa-solid fa-map-pin"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-lg font-bold">{{ $gov->name }}</h4>
                                @if($gov->is_active)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">نشطة ومتاحة</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-500/20 text-red-300 border border-red-500/30">معطلة مؤقتاً</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-300 mt-0.5">{{ $gov->districts->count() }} مديريات / مدن تابعة</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="selectedGov = { id: {{ $gov->id }}, name: '{{ addslashes($gov->name) }}' }; showAddDistModal = true" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition flex items-center gap-1.5">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>إضافة مديرية</span>
                        </button>
                        <button @click="selectedGov = { id: {{ $gov->id }}, name: '{{ addslashes($gov->name) }}', is_active: {{ $gov->is_active ? 'true' : 'false' }} }; showEditGovModal = true" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs transition" title="تعديل المحافظة">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button @click="selectedGov = { id: {{ $gov->id }}, name: '{{ addslashes($gov->name) }}' }; showDeleteGovModal = true" class="p-2 rounded-lg bg-red-500/20 hover:bg-red-500 text-red-300 hover:text-white text-xs transition" title="حذف المحافظة">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>

                <!-- Districts Table / Grid -->
                <div class="p-5 divide-y divide-gray-100">
                    @forelse($gov->districts as $dist)
                        <div class="py-4 first:pt-0 last:pb-0 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-city text-gray-400 text-sm"></i>
                                    <h5 class="font-bold text-gray-800 text-sm">{{ $dist->name }}</h5>
                                    @if($dist->is_active)
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                    @endif
                                </div>
                                <!-- Neighborhoods Tags -->
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span class="text-[11px] text-gray-400 font-semibold ml-1">الأحياء:</span>
                                    @forelse($dist->neighborhoods as $neigh)
                                        <div class="group inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-medium hover:bg-gray-200 transition">
                                            <span>{{ $neigh->name }}</span>
                                            <button @click="selectedNeigh = { id: {{ $neigh->id }}, name: '{{ addslashes($neigh->name) }}' }; showEditNeighModal = true" class="text-gray-400 hover:text-emerald-600 text-[10px]" title="تعديل الحي">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button @click="selectedNeigh = { id: {{ $neigh->id }}, name: '{{ addslashes($neigh->name) }}' }; showDeleteNeighModal = true" class="text-gray-400 hover:text-red-600 text-[10px]" title="حذف الحي">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    @empty
                                        <span class="text-xs text-gray-400 italic">لا توجد أحياء مسجلة بعد</span>
                                    @endforelse
                                    <button @click="selectedDist = { id: {{ $dist->id }}, name: '{{ addslashes($dist->name) }}' }; showAddNeighModal = true" class="text-[11px] text-emerald-600 font-bold hover:underline mr-2">
                                        + إضافة حي
                                    </button>
                                </div>
                            </div>

                            <!-- District Actions -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button @click="selectedDist = { id: {{ $dist->id }}, name: '{{ addslashes($dist->name) }}', is_active: {{ $dist->is_active ? 'true' : 'false' }} }; showEditDistModal = true" class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs transition" title="تعديل المديرية">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button @click="selectedDist = { id: {{ $dist->id }}, name: '{{ addslashes($dist->name) }}' }; showDeleteDistModal = true" class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs transition" title="حذف المديرية">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-400">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 text-gray-300"></i>
                            <p class="text-xs">لا توجد مديريات مسجلة تحت هذه المحافظة بعد</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="bg-white p-12 text-center rounded-2xl border border-gray-200">
                <i class="fa-solid fa-map-location text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500 font-medium">لم يتم تسجيل أي محافظات بعد</p>
            </div>
        @endforelse
    </div>

    <!-- MODAL: Add Governorate -->
    <div x-show="showAddGovModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showAddGovModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-emerald-600"></i>
                <span>إضافة محافظة / منطقة جديدة</span>
            </h3>
            <form action="{{ route('admin.locations.gov.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">اسم المحافظة / المنطقة *</label>
                    <input type="text" name="name" required placeholder="مثال: صنعاء، عدن، تعز، حضرموت" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showAddGovModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">حفظ المحافظة</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Governorate -->
    <div x-show="showEditGovModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showEditGovModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-emerald-600"></i>
                <span>تعديل المحافظة</span>
            </h3>
            <form :action="'{{ url('/admin/locations/governorates') }}/' + selectedGov.id" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">اسم المحافظة *</label>
                    <input type="text" name="name" x-model="selectedGov.name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="gov_is_active" :checked="selectedGov.is_active" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <label for="gov_is_active" class="text-xs font-semibold text-gray-700">تفعيل هذه المحافظة في التطبيق</label>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showEditGovModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">تحديث المحافظة</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Delete Governorate -->
    <div x-show="showDeleteGovModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showDeleteGovModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">تأكيد حذف المحافظة</h3>
            <p class="text-xs text-gray-500 mt-1">هل أنت متأكد من حذف المحافظة (<span class="font-bold text-red-600" x-text="selectedGov.name"></span>)؟ سيؤدي ذلك لحذف كافة المديريات والأحياء التابعة لها.</p>
            <form :action="'{{ url('/admin/locations/governorates') }}/' + selectedGov.id" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteGovModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 shadow-md shadow-red-600/20">تأكيد الحذف</button>
            </form>
        </div>
    </div>

    <!-- MODAL: Add District -->
    <div x-show="showAddDistModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showAddDistModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-city text-emerald-600"></i>
                <span>إضافة مديرية / مدينة</span>
            </h3>
            <p class="text-xs text-gray-500 mb-4">تابعة لمحافظة: <span class="font-bold text-emerald-600" x-text="selectedGov.name"></span></p>
            <form action="{{ route('admin.locations.dist.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="governorate_id" :value="selectedGov.id">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">اسم المدينة / المديرية *</label>
                    <input type="text" name="name" required placeholder="مثال: صنعاء، عدن، المكلا، إب" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showAddDistModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">حفظ المديرية</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit District -->
    <div x-show="showEditDistModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showEditDistModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-emerald-600"></i>
                <span>تعديل المديرية / المدينة</span>
            </h3>
            <form :action="'{{ url('/admin/locations/districts') }}/' + selectedDist.id" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">اسم المديرية / المدينة *</label>
                    <input type="text" name="name" x-model="selectedDist.name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="dist_is_active" :checked="selectedDist.is_active" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <label for="dist_is_active" class="text-xs font-semibold text-gray-700">تفعيل هذه المديرية</label>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showEditDistModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">تحديث المديرية</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Delete District -->
    <div x-show="showDeleteDistModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showDeleteDistModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash-can text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">حذف المديرية</h3>
            <p class="text-xs text-gray-500 mt-1">هل أنت متأكد من حذف المديرية (<span class="font-bold text-red-600" x-text="selectedDist.name"></span>)؟</p>
            <form :action="'{{ url('/admin/locations/districts') }}/' + selectedDist.id" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteDistModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 shadow-md shadow-red-600/20">تأكيد الحذف</button>
            </form>
        </div>
    </div>

    <!-- MODAL: Add Neighborhood -->
    <div x-show="showAddNeighModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showAddNeighModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-house-chimney text-emerald-600"></i>
                <span>إضافة حي سكني جديد</span>
            </h3>
            <p class="text-xs text-gray-500 mb-4">تابع لمدينة / مديرية: <span class="font-bold text-emerald-600" x-text="selectedDist.name"></span></p>
            <form action="{{ route('admin.locations.neigh.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="district_id" :value="selectedDist.id">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">اسم الحي السكني *</label>
                    <input type="text" name="name" required placeholder="مثال: حي حدة، حي الصافية، حي التحرير" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showAddNeighModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">حفظ الحي</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Neighborhood -->
    <div x-show="showEditNeighModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showEditNeighModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-emerald-600"></i>
                <span>تعديل الحي السكني</span>
            </h3>
            <form :action="'{{ url('/admin/locations/neighborhoods') }}/' + selectedNeigh.id" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">اسم الحي السكني *</label>
                    <input type="text" name="name" x-model="selectedNeigh.name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showEditNeighModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">تحديث الحي</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Delete Neighborhood -->
    <div x-show="showDeleteNeighModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showDeleteNeighModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash-can text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">حذف الحي</h3>
            <p class="text-xs text-gray-500 mt-1">هل أنت متأكد من حذف الحي (<span class="font-bold text-red-600" x-text="selectedNeigh.name"></span>)؟</p>
            <form :action="'{{ url('/admin/locations/neighborhoods') }}/' + selectedNeigh.id" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteNeighModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 shadow-md shadow-red-600/20">تأكيد الحذف</button>
            </form>
        </div>
    </div>

</div>
@endsection
