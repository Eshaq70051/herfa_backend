@extends('admin.layout')

@section('title', 'العروض والإعلانات الترويجية')
@section('page_title', 'إدارة العروض والبنرات الترويجية (Offers & Advertisements)')

@section('content')
<div class="space-y-8" x-data="{
    activeTab: 'ads',
    showAddAdModal: false,
    showEditAdModal: false,
    showDeleteAdModal: false,
    selectedAd: {},

    showAddOfferModal: false,
    showEditOfferModal: false,
    showDeleteOfferModal: false,
    selectedOffer: {}
}">

    <!-- Tab Switcher & Action Header -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center gap-2 p-1.5 bg-gray-100 rounded-xl">
            <button @click="activeTab = 'ads'" :class="activeTab === 'ads' ? 'bg-white text-primary-700 shadow-sm font-bold' : 'text-gray-600 font-semibold'" class="px-5 py-2 rounded-lg text-sm transition">
                <i class="fa-solid fa-rectangle-ad ml-1"></i>
                <span>البنرات والإعلانات ({{ $advertisements->total() }})</span>
            </button>
            <button @click="activeTab = 'offers'" :class="activeTab === 'offers' ? 'bg-white text-primary-700 shadow-sm font-bold' : 'text-gray-600 font-semibold'" class="px-5 py-2 rounded-lg text-sm transition">
                <i class="fa-solid fa-tags ml-1"></i>
                <span>عروض وخصومات الخدمات ({{ $offers->total() }})</span>
            </button>
        </div>

        <div>
            <button x-show="activeTab === 'ads'" @click="showAddAdModal = true" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm shadow-md shadow-primary-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>إضافة إعلان / بنر جديد</span>
            </button>
            <button x-show="activeTab === 'offers'" @click="showAddOfferModal = true" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm shadow-md shadow-primary-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>إنشاء عرض ترويجي جديد</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: Advertisements -->
    <div x-show="activeTab === 'ads'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs uppercase">
                        <tr>
                            <th class="p-4">بنر الإعلان</th>
                            <th class="p-4">عنوان الإعلان</th>
                            <th class="p-4">المعلن</th>
                            <th class="p-4">الرابط الموجه</th>
                            <th class="p-4">الفترة الزمنية</th>
                            <th class="p-4">الحالة</th>
                            <th class="p-4 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($advertisements as $ad)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="p-4">
                                    @if($ad->image_url)
                                        <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" class="w-16 h-10 rounded-lg object-cover border border-gray-200 shadow-sm">
                                    @else
                                        <div class="w-16 h-10 rounded-lg bg-gray-100 text-gray-400 flex items-center justify-center text-xs border border-gray-200">
                                            <i class="fa-solid fa-rectangle-ad"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-gray-800">{{ $ad->title }}</div>
                                    @if($ad->description)
                                        <div class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{{ $ad->description }}</div>
                                    @endif
                                </td>
                                <td class="p-4 font-medium text-gray-700">{{ $ad->advertiser_name ?? 'المنصة' }}</td>
                                <td class="p-4 text-xs font-mono text-blue-600">
                                    @if($ad->target_url)
                                        <a href="{{ $ad->target_url }}" target="_blank" class="hover:underline flex items-center gap-1">
                                            <span>{{ Str::limit($ad->target_url, 25) }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                        </a>
                                    @else
                                        <span class="text-gray-400">داخلي بالمنصة</span>
                                    @endif
                                </td>
                                <td class="p-4 text-xs text-gray-600">
                                    <div>من: {{ $ad->start_date ? $ad->start_date->format('Y-m-d') : '-' }}</div>
                                    <div>إلى: {{ $ad->end_date ? $ad->end_date->format('Y-m-d') : '-' }}</div>
                                </td>
                                <td class="p-4">
                                    @if($ad->status === 'active')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">نشط</span>
                                    @elseif($ad->status === 'expired')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">منتهي</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-200">معطل</span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button @click="selectedAd = {
                                            id: {{ $ad->id }},
                                            title: '{{ addslashes($ad->title) }}',
                                            advertiser_name: '{{ addslashes($ad->advertiser_name ?? '') }}',
                                            description: '{{ addslashes($ad->description ?? '') }}',
                                            target_url: '{{ addslashes($ad->target_url ?? '') }}',
                                            start_date: '{{ $ad->start_date ? $ad->start_date->format('Y-m-d') : '' }}',
                                            end_date: '{{ $ad->end_date ? $ad->end_date->format('Y-m-d') : '' }}',
                                            image_url: '{{ $ad->image_url ?? '' }}',
                                            status: '{{ $ad->status }}'
                                        }; showEditAdModal = true" class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition" title="تعديل">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </button>
                                        <button @click="selectedAd = { id: {{ $ad->id }}, title: '{{ addslashes($ad->title) }}' }; showDeleteAdModal = true" class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition" title="حذف">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center text-gray-400">
                                    <i class="fa-solid fa-rectangle-ad text-4xl text-gray-300 mb-2"></i>
                                    <p>لا توجد إعلانات مسجلة في المنظومة بعد</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($advertisements->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $advertisements->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: Offers -->
    <div x-show="activeTab === 'offers'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs uppercase">
                        <tr>
                            <th class="p-4">صورة العرض</th>
                            <th class="p-4">عنوان العرض</th>
                            <th class="p-4">الخدمة المستهدفة</th>
                            <th class="p-4">الحرفي / المنفذ</th>
                            <th class="p-4">نسبة الخصم</th>
                            <th class="p-4">صلاحية العرض</th>
                            <th class="p-4">الحالة</th>
                            <th class="p-4 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($offers as $offer)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="p-4">
                                    @if($offer->image_url)
                                        <img src="{{ $offer->image_url }}" alt="{{ $offer->title }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 shadow-sm">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-xs border border-gray-200">
                                            <i class="fa-solid fa-tags"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-gray-800">{{ $offer->title }}</div>
                                    @if($offer->description)
                                        <div class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{{ $offer->description }}</div>
                                    @endif
                                </td>
                                <td class="p-4 font-semibold text-emerald-700">{{ $offer->service->name ?? '-' }}</td>
                                <td class="p-4 text-xs text-gray-600">{{ $offer->craftsman->full_name ?? 'عرض عام لجميع الحرفيين' }}</td>
                                <td class="p-4 font-extrabold text-red-600">{{ $offer->discount_percentage }}% خصم</td>
                                <td class="p-4 text-xs text-gray-600">
                                    <div>من: {{ $offer->start_date ? $offer->start_date->format('Y-m-d') : '-' }}</div>
                                    <div>إلى: {{ $offer->end_date ? $offer->end_date->format('Y-m-d') : '-' }}</div>
                                </td>
                                <td class="p-4">
                                    @if($offer->offer_status === 'active')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">ساري</span>
                                    @elseif($offer->offer_status === 'expired')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">منتهي</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-200">معطل</span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button @click="selectedOffer = {
                                            id: {{ $offer->id }},
                                            title: '{{ addslashes($offer->title) }}',
                                            service_id: {{ $offer->service_id ?? 0 }},
                                            craftsman_id: '{{ $offer->craftsman_id ?? '' }}',
                                            discount_percentage: '{{ $offer->discount_percentage }}',
                                            description: '{{ addslashes($offer->description ?? '') }}',
                                            start_date: '{{ $offer->start_date ? $offer->start_date->format('Y-m-d') : '' }}',
                                            end_date: '{{ $offer->end_date ? $offer->end_date->format('Y-m-d') : '' }}',
                                            image_url: '{{ $offer->image_url ?? '' }}',
                                            offer_status: '{{ $offer->offer_status }}'
                                        }; showEditOfferModal = true" class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition" title="تعديل العرض">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </button>
                                        <button @click="selectedOffer = { id: {{ $offer->id }}, title: '{{ addslashes($offer->title) }}' }; showDeleteOfferModal = true" class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition" title="حذف العرض">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-12 text-center text-gray-400">
                                    <i class="fa-solid fa-tags text-4xl text-gray-300 mb-2"></i>
                                    <p>لا توجد عروض ترويجية مسجلة حالياً</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($offers->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $offers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL: Add Ad -->
    <div x-show="showAddAdModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showAddAdModal = false" class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-bullhorn text-emerald-600"></i>
                <span>إضافة إعلان أو بنر ترويجي</span>
            </h3>
            <form action="{{ route('admin.advertisements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">عنوان الإعلان *</label>
                    <input type="text" name="title" required placeholder="مثال: خصم 20% على صيانة المكيفات المركزية" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">صورة البنر الإعلاني (Banner Image)</label>
                    <input type="file" name="image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-gray-50 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">اسم المعلن</label>
                        <input type="text" name="advertiser_name" placeholder="حِرفة أو شريك خارجي" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">الحالة</label>
                        <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="active">نشط وساري</option>
                            <option value="inactive">معطل مؤقتاً</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">الرابط الموجه (اختياري)</label>
                    <input type="url" name="target_url" placeholder="https://..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">تاريخ البدء *</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">تاريخ الانتهاء *</label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">تفاصيل وملاحظات الإعلان</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showAddAdModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">حفظ الإعلان</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Ad -->
    <div x-show="showEditAdModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showEditAdModal = false" class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-emerald-600"></i>
                <span>تعديل الإعلان</span>
            </h3>
            <form :action="'{{ url('/admin/advertisements') }}/' + selectedAd.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">عنوان الإعلان *</label>
                    <input type="text" name="title" x-model="selectedAd.title" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">تحديث صورة البنر</label>
                    <input type="file" name="image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-gray-50 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <template x-if="selectedAd.image_url">
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-[11px] text-gray-500">الصورة الحالية:</span>
                            <img :src="selectedAd.image_url" class="h-10 rounded object-cover border">
                        </div>
                    </template>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">اسم المعلن</label>
                        <input type="text" name="advertiser_name" x-model="selectedAd.advertiser_name" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">الحالة</label>
                        <select name="status" x-model="selectedAd.status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="active">نشط وساري</option>
                            <option value="inactive">معطل مؤقتاً</option>
                            <option value="expired">منتهي</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">الرابط الموجه</label>
                    <input type="url" name="target_url" x-model="selectedAd.target_url" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">تاريخ البدء *</label>
                        <input type="date" name="start_date" x-model="selectedAd.start_date" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">تاريخ الانتهاء *</label>
                        <input type="date" name="end_date" x-model="selectedAd.end_date" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">الوصف</label>
                    <textarea name="description" x-model="selectedAd.description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showEditAdModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">تحديث الإعلان</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Delete Ad -->
    <div x-show="showDeleteAdModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showDeleteAdModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash-can text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">تأكيد حذف الإعلان</h3>
            <p class="text-xs text-gray-500 mt-1">هل أنت متأكد من حذف الإعلان (<span class="font-bold text-red-600" x-text="selectedAd.title"></span>)؟</p>
            <form :action="'{{ url('/admin/advertisements') }}/' + selectedAd.id" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteAdModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 shadow-md shadow-red-600/20">تأكيد الحذف</button>
            </form>
        </div>
    </div>

    <!-- MODAL: Add Offer -->
    <div x-show="showAddOfferModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showAddOfferModal = false" class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-tag text-emerald-600"></i>
                <span>إنشاء عرض ترويجي جديد</span>
            </h3>
            <form action="{{ route('admin.advertisements.offers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">عنوان العرض *</label>
                    <input type="text" name="title" required placeholder="مثال: خصم 25% على أعمال السباكة" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">صورة العرض الترويجي</label>
                    <input type="file" name="image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-gray-50 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">الخدمة المستهدفة *</label>
                        <select name="service_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">اختر الخدمة...</option>
                            @foreach($services as $svc)
                                <option value="{{ $svc->id }}">{{ $svc->name }} ({{ number_format($svc->base_price, 2) }} ر.ي)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">نسبة الخصم (%) *</label>
                        <input type="number" step="0.5" min="1" max="100" name="discount_percentage" required placeholder="25" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">حرفي محدد (اختياري - اتركه فارغاً لجميع الحرفيين)</label>
                    <select name="craftsman_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">عرض عام للمنصة كاملة</option>
                        @foreach($craftsmen as $cm)
                            <option value="{{ $cm->id }}">{{ $cm->full_name }} ({{ $cm->phone }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">تاريخ البدء *</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">تاريخ الانتهاء *</label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">حالة العرض</label>
                    <select name="offer_status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="active">ساري ونشط</option>
                        <option value="inactive">معطل مؤقتاً</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">شروط ووصف العرض</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showAddOfferModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">تفعيل العرض</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Offer -->
    <div x-show="showEditOfferModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showEditOfferModal = false" class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-emerald-600"></i>
                <span>تعديل العرض الترويجي</span>
            </h3>
            <form :action="'{{ url('/admin/advertisements/offers') }}/' + selectedOffer.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">عنوان العرض *</label>
                    <input type="text" name="title" x-model="selectedOffer.title" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">تحديث صورة العرض</label>
                    <input type="file" name="image" accept="image/*" class="w-full p-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-gray-50 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <template x-if="selectedOffer.image_url">
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-[11px] text-gray-500">الصورة الحالية:</span>
                            <img :src="selectedOffer.image_url" class="w-10 h-10 rounded-lg object-cover border">
                        </div>
                    </template>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">الخدمة المستهدفة *</label>
                        <select name="service_id" x-model="selectedOffer.service_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            @foreach($services as $svc)
                                <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">نسبة الخصم (%) *</label>
                        <input type="number" step="0.5" min="1" max="100" name="discount_percentage" x-model="selectedOffer.discount_percentage" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">تاريخ البدء *</label>
                        <input type="date" name="start_date" x-model="selectedOffer.start_date" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">تاريخ الانتهاء *</label>
                        <input type="date" name="end_date" x-model="selectedOffer.end_date" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">حالة العرض</label>
                    <select name="offer_status" x-model="selectedOffer.offer_status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="active">ساري ونشط</option>
                        <option value="inactive">معطل مؤقتاً</option>
                        <option value="expired">منتهي</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">الوصف</label>
                    <textarea name="description" x-model="selectedOffer.description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showEditOfferModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">تحديث العرض</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Delete Offer -->
    <div x-show="showDeleteOfferModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showDeleteOfferModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash-can text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">حذف العرض الترويجي</h3>
            <p class="text-xs text-gray-500 mt-1">هل أنت متأكد من حذف العرض (<span class="font-bold text-red-600" x-text="selectedOffer.title"></span>)؟</p>
            <form :action="'{{ url('/admin/advertisements/offers') }}/' + selectedOffer.id" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteOfferModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 shadow-md shadow-red-600/20">تأكيد الحذف</button>
            </form>
        </div>
    </div>

</div>
@endsection
