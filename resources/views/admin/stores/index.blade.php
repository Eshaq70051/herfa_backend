@extends('admin.layout')

@section('title', 'خدمات ومتاجر الشركاء')

@section('content')
<div class="space-y-6" x-data="{
    createModal: false,
    editModal: false,
    currentStore: {}
}">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-900 leading-tight">خدمات ومتاجر الشركاء</h2>
                    <p class="text-xs text-gray-500 font-medium">إدارة الشركاء والمتاجر والمؤسسات المتخصصة المعروضة بتطبيق حِرفة (Ajeer Store Services)</p>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-3 space-x-reverse">
            <button @click="createModal = true" class="inline-flex items-center justify-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-primary-600/30 transition transform active:scale-95">
                <i class="fa-solid fa-plus ml-2"></i>
                إضافة متجر / خدمة جديدة
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500">إجمالي المتاجر والشركاء</p>
                <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $totalStores }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-shop"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500">المتاجر النشطة بالتطبيق</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $activeStores }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500">متوسط تقييم الشركاء</p>
                <h3 class="text-2xl font-black text-amber-500 mt-1">{{ $avgRating }} ⭐</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl">
                <i class="fa-solid fa-star"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500">إجمالي تقييمات العملاء</p>
                <h3 class="text-2xl font-black text-indigo-600 mt-1">{{ number_format($totalReviews) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-comments"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
        <form method="GET" action="{{ route('admin.stores.index') }}" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-search absolute right-3.5 top-3.5 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث باسم الخدمة، اسم المتجر، الوصف، أو الهاتف..." class="w-full pr-10 pl-4 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition">
            </div>

            <div class="w-full md:w-48">
                <select name="status" class="w-full px-3 py-2.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط ومعروض</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>معطّل / متوقف</option>
                </select>
            </div>

            <button type="submit" class="px-5 py-2.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition">
                تصفية
            </button>

            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.stores.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition text-center">
                    إعادة ضبط
                </a>
            @endif
        </form>
    </div>

    <!-- Stores Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($stores as $store)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition p-5 flex flex-col justify-between relative overflow-hidden group">
                <!-- Top Badge & Active Indicator -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <img src="{{ $store->logo_url ?: 'https://api.dicebear.com/7.x/shapes/svg?seed=' . urlencode($store->company_name) }}" alt="{{ $store->name }}" class="w-14 h-14 rounded-2xl object-cover bg-gray-50 border border-gray-100 p-1 shadow-sm">
                        <div>
                            <h3 class="font-black text-gray-900 text-sm leading-snug group-hover:text-primary-600 transition">{{ $store->name }}</h3>
                            <p class="text-xs text-gray-500 font-semibold">{{ $store->company_name }}</p>
                            @if($store->badge)
                                <span class="inline-block mt-1 px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-bold rounded-md border border-amber-200">
                                    {{ $store->badge }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="text-left">
                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-black {{ $store->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200' }}">
                            {{ $store->is_active ? 'نشط' : 'معطل' }}
                        </span>
                    </div>
                </div>

                <!-- Description -->
                <p class="text-xs text-gray-600 line-clamp-3 leading-relaxed mb-4">
                    {{ $store->description ?: 'لا يوجد وصف مفصل مضاف لهذا المتجر.' }}
                </p>

                <!-- Tags / Specialties -->
                @if(!empty($store->tags))
                    <div class="flex flex-wrap gap-1.5 mb-4">
                        @foreach(array_slice($store->tags, 0, 3) as $tag)
                            <span class="px-2 py-1 bg-gray-50 text-gray-600 rounded-lg text-[10.5px] font-semibold border border-gray-100">
                                {{ $tag }}
                            </span>
                        @endforeach
                        @if(count($store->tags) > 3)
                            <span class="px-1.5 py-1 bg-gray-100 text-gray-500 rounded-lg text-[10px] font-bold">
                                +{{ count($store->tags) - 3 }}
                            </span>
                        @endif
                    </div>
                @endif

                <!-- Bottom Stats & Actions -->
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex items-center space-x-2 space-x-reverse text-xs">
                        <span class="font-black text-gray-900 flex items-center">
                            <i class="fa-solid fa-star text-amber-400 ml-1 text-xs"></i>
                            {{ $store->rating }}
                        </span>
                        <span class="text-gray-400">({{ $store->reviews_count }} تقييم)</span>
                        @if($store->phone)
                            <span class="text-gray-300">|</span>
                            <span class="text-gray-500 font-mono text-[11px]">{{ $store->phone }}</span>
                        @endif
                    </div>

                    <div class="flex items-center space-x-1.5 space-x-reverse">
                        <!-- Toggle Status -->
                        <button onclick="toggleStoreStatus({{ $store->id }})" title="تبديل حالة التفعيل" class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                            <i class="fa-solid fa-power-off text-xs"></i>
                        </button>

                        <!-- Edit -->
                        <button @click="
                            currentStore = {
                                id: {{ $store->id }},
                                name: '{{ addslashes($store->name) }}',
                                company_name: '{{ addslashes($store->company_name) }}',
                                description: '{{ addslashes($store->description ?? '') }}',
                                phone: '{{ addslashes($store->phone ?? '') }}',
                                rating: '{{ $store->rating }}',
                                reviews_count: '{{ $store->reviews_count }}',
                                badge: '{{ addslashes($store->badge ?? '') }}',
                                display_order: '{{ $store->display_order }}',
                                tags: '{{ !empty($store->tags) ? addslashes(implode(', ', $store->tags)) : '' }}',
                                logo: '{{ $store->logo_url }}',
                                is_active: {{ $store->is_active ? 'true' : 'false' }}
                            };
                            editModal = true;
                        " title="تعديل المتجر" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </button>

                        <!-- Delete -->
                        <form action="{{ route('admin.stores.destroy', $store->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا المتجر نهائياً؟');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="حذف المتجر" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-gray-100 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fa-solid fa-store-slash"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">لا توجد متاجر مطابقة للبحث</h3>
                <p class="text-xs text-gray-500 mb-4">قم بإضافة أول متجر أو خدمة شريك لعرضها مباشرة داخل التطبيق.</p>
                <button @click="createModal = true" class="px-4 py-2 bg-primary-600 text-white text-xs font-bold rounded-xl shadow-md">
                    إضافة متجر جديد
                </button>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($stores->hasPages())
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
            {{ $stores->links() }}
        </div>
    @endif

    <!-- Create Store Modal -->
    <div x-cloak x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="createModal = false" class="bg-white w-full max-w-xl rounded-3xl shadow-2xl border border-gray-100 overflow-hidden transform transition-all">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center space-x-2.5 space-x-reverse">
                    <div class="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <h3 class="font-black text-gray-900 text-base">إضافة متجر / خدمة شريك جديدة</h3>
                </div>
                <button @click="createModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('admin.stores.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">اسم الخدمة المعروضة بالتطبيق *</label>
                        <input type="text" name="name" required placeholder="مثال: عاملات منزلية بالساعة" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">اسم الشركة / المؤسسة *</label>
                        <input type="text" name="company_name" required placeholder="مثال: تراك كلين Track Clean" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">وصف الخدمة ونبذة عن الشريك</label>
                    <textarea name="description" rows="3" placeholder="اشرح ما يقدمه المتجر ومميزاته للعملاء..." class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">رقم التواصل / واتساب</label>
                        <input type="text" name="phone" placeholder="مثال: 777123456" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">التقييم (من 5)</label>
                        <input type="number" step="0.1" min="1" max="5" name="rating" value="4.8" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">عدد التقييمات</label>
                        <input type="number" min="0" name="reviews_count" value="15" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">شارة التوثيق / التميز</label>
                        <input type="text" name="badge" value="معتمد" placeholder="مثال: معتمد، شريك بلاتيني..." class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">ترتيب الظهور</label>
                        <input type="number" name="display_order" value="1" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">وسوم الخدمات (مفصولة بفواصل)</label>
                    <input type="text" name="tags" placeholder="مثال: الفترة الصباحية, الفترة المسائية, خدمة بالساعة" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">رفع شعار المتجر (صورة)</label>
                        <input type="file" name="logo_file" accept="image/*" class="w-full text-xs text-gray-500 file:mr-0 file:ml-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">أو رابط الشعار (URL)</label>
                        <input type="url" name="logo_url" placeholder="https://example.com/logo.png" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                </div>

                <div class="flex items-center pt-2">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                        <span class="mr-2 text-xs font-bold text-gray-800">تفعيل المتجر وإظهاره فوراً في التطبيق</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-2 space-x-reverse">
                    <button type="button" @click="createModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-600/30 transition">
                        حفظ المتجر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Store Modal -->
    <div x-cloak x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="editModal = false" class="bg-white w-full max-w-xl rounded-3xl shadow-2xl border border-gray-100 overflow-hidden transform transition-all">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center space-x-2.5 space-x-reverse">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <h3 class="font-black text-gray-900 text-base">تعديل بيانات المتجر / الشريك</h3>
                </div>
                <button @click="editModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/stores') }}/' + currentStore.id" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">اسم الخدمة المعروضة *</label>
                        <input type="text" name="name" x-model="currentStore.name" required class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">اسم الشركة / المؤسسة *</label>
                        <input type="text" name="company_name" x-model="currentStore.company_name" required class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">وصف الخدمة ونبذة عن الشريك</label>
                    <textarea name="description" x-model="currentStore.description" rows="3" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">رقم التواصل / واتساب</label>
                        <input type="text" name="phone" x-model="currentStore.phone" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">التقييم (من 5)</label>
                        <input type="number" step="0.1" min="1" max="5" name="rating" x-model="currentStore.rating" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">عدد التقييمات</label>
                        <input type="number" min="0" name="reviews_count" x-model="currentStore.reviews_count" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">شارة التوثيق / التميز</label>
                        <input type="text" name="badge" x-model="currentStore.badge" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">ترتيب الظهور</label>
                        <input type="number" name="display_order" x-model="currentStore.display_order" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">وسوم الخدمات (مفصولة بفواصل)</label>
                    <input type="text" name="tags" x-model="currentStore.tags" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">تحديث شعار المتجر</label>
                        <input type="file" name="logo_file" accept="image/*" class="w-full text-xs text-gray-500 file:mr-0 file:ml-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">أو رابط الشعار (URL)</label>
                        <input type="url" name="logo_url" x-model="currentStore.logo" class="w-full px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 font-mono">
                    </div>
                </div>

                <div class="flex items-center pt-2">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" :checked="currentStore.is_active" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                        <span class="mr-2 text-xs font-bold text-gray-800">تفعيل المتجر وإظهاره في التطبيق</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-2 space-x-reverse">
                    <button type="button" @click="editModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-600/30 transition">
                        حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleStoreStatus(id) {
        fetch(`{{ url('admin/stores') }}/${id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status) {
                location.reload();
            }
        })
        .catch(err => alert('تعذر تغيير الحالة'));
    }
</script>
@endsection
