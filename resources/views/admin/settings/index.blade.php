@extends('admin.layout')

@section('title', 'إعدادات المنظومة')
@section('page_title', 'إعدادات المنصة والهوية البصرية والتواصل (Platform Settings & Branding)')

@section('content')
<div class="space-y-8" x-data="{
    activeTab: 'branding',
    showAddSettingModal: false,
    showDeleteSettingModal: false,
    selectedSetting: {},

    // Dynamic Color state for live preview
    primaryColor: '{{ $settings['primary_color'] ?? '#059669' }}',
    secondaryColor: '{{ $settings['secondary_color'] ?? '#0284c7' }}',
    accentColor: '{{ $settings['accent_color'] ?? '#f59e0b' }}',
    sidebarColor: '{{ $settings['sidebar_color'] ?? '#0f172a' }}',
    bgColor: '{{ $settings['bg_color'] ?? '#f8fafc' }}',

    showImageModal: false,
    modalImageUrl: '',
    modalImageTitle: ''
}">

    <!-- Top Action Banner -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-800">إدارة إعدادات المنصة والهوية البصرية والتواصل</h3>
            <p class="text-xs text-gray-500 mt-1">تخصيص الشعار، الأيقونات، حزم الألوان، صور الخلفيات، وسائل التواصل، ونسب العمولات</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showAddSettingModal = true" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus text-[11px]"></i>
                <span>إضافة متغير جديد</span>
            </button>
            <button type="submit" form="mainSettingsForm" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>حفظ كافة التغييرات</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-gray-200 text-xs font-bold scrollbar-none">
        <button @click="activeTab = 'branding'" :class="activeTab === 'branding' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-palette"></i>
            <span>الهوية البصرية والشعارات</span>
        </button>
        <button @click="activeTab = 'colors'" :class="activeTab === 'colors' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-eye-dropper"></i>
            <span>ألوان ومظهر الموقع</span>
        </button>
        <button @click="activeTab = 'backgrounds'" :class="activeTab === 'backgrounds' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-image"></i>
            <span>الخلفيات والصور</span>
        </button>
        <button @click="activeTab = 'social'" :class="activeTab === 'social' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-share-nodes"></i>
            <span>وسائل التواصل والدعم</span>
        </button>
        <button @click="activeTab = 'financial'" :class="activeTab === 'financial' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-coins"></i>
            <span>السياسات المالية والضمان</span>
        </button>
        <button @click="activeTab = 'system'" :class="activeTab === 'system' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-mobile-screen"></i>
            <span>التطبيقات ووضع الصيانة</span>
        </button>
        <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-database"></i>
            <span>كافة المتغيرات ({{ $settingsList->count() }})</span>
        </button>
    </div>

    <!-- MAIN SETTINGS FORM -->
    <form id="mainSettingsForm" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="is_settings_form" value="1">

        <!-- ========================================== -->
        <!-- TAB 1: BRANDING & LOGOS -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'branding'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-6">
                <div class="border-b border-gray-100 pb-4">
                    <h4 class="font-extrabold text-gray-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-shapes text-emerald-600"></i>
                        <span>الشعارات والهوية البصرية الرسمية</span>
                    </h4>
                    <p class="text-xs text-gray-400 mt-1">يُنصح باستخدام صور بصيغ PNG ذات خلفية شفافة أو ملفات متجهة SVG بدقة عالية</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- 1. Primary Site Logo -->
                    <div class="bg-gray-50/70 p-5 rounded-2xl border border-gray-200 flex flex-col justify-between">
                        <div>
                            <span class="block text-xs font-bold text-gray-700 mb-1">الشعار الرسمي الفاتح (Light Logo)</span>
                            <p class="text-[11px] text-gray-400 mb-3">يظهر على الخلفيات البيضاء والفاتحة ورأس الصفحات</p>
                            <div class="h-28 bg-white rounded-xl border border-dashed border-gray-300 flex items-center justify-center p-3 mb-3">
                                @if(!empty($settings['site_logo']))
                                    <img src="{{ asset($settings['site_logo']) }}" alt="الشعار الفاتح" class="max-h-20 max-w-full object-contain">
                                @else
                                    <span class="text-xs text-gray-400">لا يوجد شعار محدد</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <input type="file" name="site_logo" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                    </div>

                    <!-- 2. Dark Logo (Sidebar) -->
                    <div class="bg-gray-50/70 p-5 rounded-2xl border border-gray-200 flex flex-col justify-between">
                        <div>
                            <span class="block text-xs font-bold text-gray-700 mb-1">الشعار للخلفيات الداكنة (Dark Logo)</span>
                            <p class="text-[11px] text-gray-400 mb-3">يظهر في الشريط الجانبي وصفحة تسجيل الدخول</p>
                            <div class="h-28 bg-slate-900 rounded-xl border border-dashed border-slate-700 flex items-center justify-center p-3 mb-3">
                                @if(!empty($settings['site_logo_dark']))
                                    <img src="{{ asset($settings['site_logo_dark']) }}" alt="الشعار الداكن" class="max-h-20 max-w-full object-contain">
                                @else
                                    <span class="text-xs text-slate-400">لا يوجد شعار محدد</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <input type="file" name="site_logo_dark" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                    </div>

                    <!-- 3. Site Favicon / App Icon -->
                    <div class="bg-gray-50/70 p-5 rounded-2xl border border-gray-200 flex flex-col justify-between">
                        <div>
                            <span class="block text-xs font-bold text-gray-700 mb-1">أيقونة الموقع (Favicon / App Icon)</span>
                            <p class="text-[11px] text-gray-400 mb-3">تظهر في تبويب المتصفح وبطاقات الاختصار على الهواتف</p>
                            <div class="h-28 bg-white rounded-xl border border-dashed border-gray-300 flex items-center justify-center p-3 mb-3">
                                @if(!empty($settings['site_favicon']))
                                    <img src="{{ asset($settings['site_favicon']) }}" alt="Favicon" class="w-14 h-14 object-contain">
                                @else
                                    <i class="fa-solid fa-wrench text-3xl text-emerald-600"></i>
                                @endif
                            </div>
                        </div>
                        <div>
                            <input type="file" name="site_favicon" accept="image/*,.ico" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                    </div>
                </div>

                <!-- Text Branding -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-gray-100">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">اسم المنصة الرسمي (App Name)</label>
                        <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'حِرفة | HERFA' }}" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">الشعار اللفظي الترويجي (Slogan / Tagline)</label>
                        <input type="text" name="app_slogan" value="{{ $settings['app_slogan'] ?? 'منصة خدمات وصيانة المنازل والمنشآت المتكاملة' }}" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: COLORS & APPEARANCE -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'colors'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-6">
                <div class="border-b border-gray-100 pb-4">
                    <h4 class="font-extrabold text-gray-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-eye-dropper text-emerald-600"></i>
                        <span>لوحة ألوان المنصة والمظهر العام (Visual Palette)</span>
                    </h4>
                    <p class="text-xs text-gray-400 mt-1">تطبيق نظام ألوان موحد يتناغم مع لوحة الإدارة وتطبيق الهاتف</p>
                </div>

                <!-- Interactive Color Pickers Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- Primary Color -->
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                        <label class="block text-xs font-bold text-gray-700 mb-1">اللون الرئيسي (Primary Color)</label>
                        <p class="text-[11px] text-gray-400 mb-3">للأزرار الرئيسية، الروابط النشطة، وبطاقات التفاعل</p>
                        <div class="flex items-center gap-3">
                            <input type="color" name="primary_color" x-model="primaryColor" class="w-12 h-12 rounded-xl cursor-pointer border border-gray-200 p-0.5 bg-white">
                            <input type="text" x-model="primaryColor" class="flex-1 p-2.5 rounded-xl border border-gray-200 font-mono text-xs uppercase font-bold text-gray-800" dir="ltr">
                        </div>
                    </div>

                    <!-- Secondary Color -->
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                        <label class="block text-xs font-bold text-gray-700 mb-1">اللون الثانوي (Secondary Color)</label>
                        <p class="text-[11px] text-gray-400 mb-3">للعناصر المساعدة، المحفظة، والشارات المميزة</p>
                        <div class="flex items-center gap-3">
                            <input type="color" name="secondary_color" x-model="secondaryColor" class="w-12 h-12 rounded-xl cursor-pointer border border-gray-200 p-0.5 bg-white">
                            <input type="text" x-model="secondaryColor" class="flex-1 p-2.5 rounded-xl border border-gray-200 font-mono text-xs uppercase font-bold text-gray-800" dir="ltr">
                        </div>
                    </div>

                    <!-- Accent / Warning Color -->
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                        <label class="block text-xs font-bold text-gray-700 mb-1">لون التمييز والتنبيه (Accent Color)</label>
                        <p class="text-[11px] text-gray-400 mb-3">للتقييمات، التنبيهات، وحالات الانتظار</p>
                        <div class="flex items-center gap-3">
                            <input type="color" name="accent_color" x-model="accentColor" class="w-12 h-12 rounded-xl cursor-pointer border border-gray-200 p-0.5 bg-white">
                            <input type="text" x-model="accentColor" class="flex-1 p-2.5 rounded-xl border border-gray-200 font-mono text-xs uppercase font-bold text-gray-800" dir="ltr">
                        </div>
                    </div>

                    <!-- Sidebar / Header Dark Color -->
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                        <label class="block text-xs font-bold text-gray-700 mb-1">لون الشريط الجانبي (Sidebar Dark)</label>
                        <p class="text-[11px] text-gray-400 mb-3">خلفية القائمة الجانبية وعناصر التحكم الداكنة</p>
                        <div class="flex items-center gap-3">
                            <input type="color" name="sidebar_color" x-model="sidebarColor" class="w-12 h-12 rounded-xl cursor-pointer border border-gray-200 p-0.5 bg-white">
                            <input type="text" x-model="sidebarColor" class="flex-1 p-2.5 rounded-xl border border-gray-200 font-mono text-xs uppercase font-bold text-gray-800" dir="ltr">
                        </div>
                    </div>

                    <!-- Page Background Color -->
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                        <label class="block text-xs font-bold text-gray-700 mb-1">لون خلفية الصفحات (Page Background)</label>
                        <p class="text-[11px] text-gray-400 mb-3">الخلفية العامة لكافة شاشات لوحة التحكم والتطبيق</p>
                        <div class="flex items-center gap-3">
                            <input type="color" name="bg_color" x-model="bgColor" class="w-12 h-12 rounded-xl cursor-pointer border border-gray-200 p-0.5 bg-white">
                            <input type="text" x-model="bgColor" class="flex-1 p-2.5 rounded-xl border border-gray-200 font-mono text-xs uppercase font-bold text-gray-800" dir="ltr">
                        </div>
                    </div>
                </div>

                <!-- Live Palette Preview -->
                <div class="pt-4 border-t border-gray-100">
                    <span class="block text-xs font-bold text-gray-700 mb-3">معاينة حية لتدرج الألوان المختار (Live Preview)</span>
                    <div class="p-5 rounded-2xl border border-gray-200 flex flex-wrap items-center gap-4 transition" :style="'background-color: ' + bgColor">
                        <button type="button" class="px-4 py-2 rounded-xl text-white font-bold text-xs shadow-sm" :style="'background-color: ' + primaryColor">
                            زر رئيسي (Primary)
                        </button>
                        <button type="button" class="px-4 py-2 rounded-xl text-white font-bold text-xs shadow-sm" :style="'background-color: ' + secondaryColor">
                            زر ثانوي (Secondary)
                        </button>
                        <span class="px-3 py-1 rounded-full text-xs font-bold text-white shadow-xs" :style="'background-color: ' + accentColor">
                            شارة تنبيهية (Accent)
                        </span>
                        <div class="px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm flex items-center gap-2" :style="'background-color: ' + sidebarColor">
                            <i class="fa-solid fa-wrench"></i>
                            <span>شريط جانبي (Sidebar)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: BACKGROUNDS & IMAGES -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'backgrounds'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-6">
                <div class="border-b border-gray-100 pb-4">
                    <h4 class="font-extrabold text-gray-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-panorama text-emerald-600"></i>
                        <span>الخلفيات وصور الواجهات (System Backgrounds)</span>
                    </h4>
                    <p class="text-xs text-gray-400 mt-1">تحديد صور خلفيات شاشة الدخول، البانر الترحيبي، وشاشة بدء تطبيق الموبايل</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- 1. Login Page Background -->
                    <div class="bg-gray-50/70 p-5 rounded-2xl border border-gray-200 flex flex-col justify-between">
                        <div>
                            <span class="block text-xs font-bold text-gray-700 mb-1">خلفية شاشة تسجيل الدخول</span>
                            <p class="text-[11px] text-gray-400 mb-3">تظهر كخلفية لصفحة دخول مدراء النظام</p>
                            <div class="h-32 bg-slate-900 rounded-xl border border-dashed border-gray-300 overflow-hidden relative group mb-3 flex items-center justify-center">
                                @if(!empty($settings['login_background']))
                                    <img src="{{ asset($settings['login_background']) }}" class="w-full h-full object-cover">
                                    <button type="button" @click="modalImageUrl = '{{ asset($settings['login_background']) }}'; modalImageTitle = 'خلفية صفحة الدخول'; showImageModal = true" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition">
                                        <i class="fa-solid fa-magnifying-glass mr-1"></i> تكبير
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">الخلفية الافتراضية</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <input type="file" name="login_background" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                    </div>

                    <!-- 2. Hero / Header Background -->
                    <div class="bg-gray-50/70 p-5 rounded-2xl border border-gray-200 flex flex-col justify-between">
                        <div>
                            <span class="block text-xs font-bold text-gray-700 mb-1">خلفية البانر الرئيسي (Hero Banner)</span>
                            <p class="text-[11px] text-gray-400 mb-3">للصفحة الرئيسية للبوابة وبطاقات الترحيب</p>
                            <div class="h-32 bg-emerald-950 rounded-xl border border-dashed border-gray-300 overflow-hidden relative group mb-3 flex items-center justify-center">
                                @if(!empty($settings['hero_background']))
                                    <img src="{{ asset($settings['hero_background']) }}" class="w-full h-full object-cover">
                                    <button type="button" @click="modalImageUrl = '{{ asset($settings['hero_background']) }}'; modalImageTitle = 'خلفية البانر الرئيسي'; showImageModal = true" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition">
                                        <i class="fa-solid fa-magnifying-glass mr-1"></i> تكبير
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">الخلفية الافتراضية</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <input type="file" name="hero_background" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                    </div>

                    <!-- 3. App Splash Background -->
                    <div class="bg-gray-50/70 p-5 rounded-2xl border border-gray-200 flex flex-col justify-between">
                        <div>
                            <span class="block text-xs font-bold text-gray-700 mb-1">خلفية شاشة البداية للتطبيق (Splash)</span>
                            <p class="text-[11px] text-gray-400 mb-3">تظهر عند فتح تطبيق فلاتر لأول مرة</p>
                            <div class="h-32 bg-slate-900 rounded-xl border border-dashed border-gray-300 overflow-hidden relative group mb-3 flex items-center justify-center">
                                @if(!empty($settings['app_splash_background']))
                                    <img src="{{ asset($settings['app_splash_background']) }}" class="w-full h-full object-cover">
                                    <button type="button" @click="modalImageUrl = '{{ asset($settings['app_splash_background']) }}'; modalImageTitle = 'خلفية شاشة بدء التطبيق'; showImageModal = true" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition">
                                        <i class="fa-solid fa-magnifying-glass mr-1"></i> تكبير
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">الخلفية الافتراضية</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <input type="file" name="app_splash_background" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: SOCIAL MEDIA & SUPPORT -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'social'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-6">
                <div class="border-b border-gray-100 pb-4">
                    <h4 class="font-extrabold text-gray-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-headset text-emerald-600"></i>
                        <span>قنوات التواصل المباشر وخدمة العملاء</span>
                    </h4>
                    <p class="text-xs text-gray-400 mt-1">تظهر في تذييل الموقع، صفحات المساعدة، وقائمة تواصل معنا بالتطبيق</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-brands fa-whatsapp text-emerald-600 text-sm"></i>
                            <span>رقم الواتساب الرسمي (WhatsApp)</span>
                        </label>
                        <input type="text" name="support_whatsapp" value="{{ $settings['support_whatsapp'] ?? '+966500000000' }}" placeholder="+966500000000" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono" dir="ltr">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-phone text-blue-600 text-sm"></i>
                            <span>الرقم الموحد / الخط الساخن</span>
                        </label>
                        <input type="text" name="support_phone" value="{{ $settings['support_phone'] ?? '920000000' }}" placeholder="920000000" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono" dir="ltr">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-envelope text-red-500 text-sm"></i>
                            <span>البريد الإلكتروني للدعم (Support Email)</span>
                        </label>
                        <input type="email" name="support_email" value="{{ $settings['support_email'] ?? 'support@herfa.app' }}" placeholder="support@herfa.app" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono" dir="ltr">
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <h5 class="font-bold text-gray-800 text-sm mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-share-nodes text-emerald-600"></i>
                        <span>روابط حسابات التواصل الاجتماعي (Social Media Links)</span>
                    </h5>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Facebook -->
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center flex-shrink-0">
                                <i class="fa-brands fa-facebook-f text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <span class="block text-[11px] font-bold text-gray-700 mb-0.5">صفحة فيسبوك (Facebook)</span>
                                <input type="url" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/herfa" class="w-full p-2 rounded-lg border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none" dir="ltr">
                            </div>
                        </div>

                        <!-- Twitter / X -->
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="w-9 h-9 rounded-lg bg-black text-white flex items-center justify-center flex-shrink-0">
                                <i class="fa-brands fa-x-twitter text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <span class="block text-[11px] font-bold text-gray-700 mb-0.5">منصة إكس (X / Twitter)</span>
                                <input type="url" name="social_twitter" value="{{ $settings['social_twitter'] ?? '' }}" placeholder="https://x.com/herfa" class="w-full p-2 rounded-lg border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-slate-800 focus:outline-none" dir="ltr">
                            </div>
                        </div>

                        <!-- Instagram -->
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-white flex items-center justify-center flex-shrink-0">
                                <i class="fa-brands fa-instagram text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <span class="block text-[11px] font-bold text-gray-700 mb-0.5">حساب إنستغرام (Instagram)</span>
                                <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/herfa" class="w-full p-2 rounded-lg border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-rose-500 focus:outline-none" dir="ltr">
                            </div>
                        </div>

                        <!-- Telegram -->
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="w-9 h-9 rounded-lg bg-sky-500 text-white flex items-center justify-center flex-shrink-0">
                                <i class="fa-brands fa-telegram text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <span class="block text-[11px] font-bold text-gray-700 mb-0.5">قناة تيليجرام (Telegram)</span>
                                <input type="url" name="social_telegram" value="{{ $settings['social_telegram'] ?? '' }}" placeholder="https://t.me/herfa" class="w-full p-2 rounded-lg border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-sky-500 focus:outline-none" dir="ltr">
                            </div>
                        </div>

                        <!-- LinkedIn -->
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="w-9 h-9 rounded-lg bg-blue-700 text-white flex items-center justify-center flex-shrink-0">
                                <i class="fa-brands fa-linkedin-in text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <span class="block text-[11px] font-bold text-gray-700 mb-0.5">لينكد إن (LinkedIn)</span>
                                <input type="url" name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '' }}" placeholder="https://linkedin.com/company/herfa" class="w-full p-2 rounded-lg border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-blue-600 focus:outline-none" dir="ltr">
                            </div>
                        </div>

                        <!-- TikTok -->
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center flex-shrink-0">
                                <i class="fa-brands fa-tiktok text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <span class="block text-[11px] font-bold text-gray-700 mb-0.5">تيك توك (TikTok)</span>
                                <input type="url" name="social_tiktok" value="{{ $settings['social_tiktok'] ?? '' }}" placeholder="https://tiktok.com/@herfa" class="w-full p-2 rounded-lg border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-slate-700 focus:outline-none" dir="ltr">
                            </div>
                        </div>

                        <!-- YouTube -->
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="w-9 h-9 rounded-lg bg-red-600 text-white flex items-center justify-center flex-shrink-0">
                                <i class="fa-brands fa-youtube text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <span class="block text-[11px] font-bold text-gray-700 mb-0.5">قناة يوتيوب (YouTube)</span>
                                <input type="url" name="social_youtube" value="{{ $settings['social_youtube'] ?? '' }}" placeholder="https://youtube.com/@herfa" class="w-full p-2 rounded-lg border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-red-500 focus:outline-none" dir="ltr">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 5: FINANCIALS & GUARANTEES -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'financial'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-6">
                <div class="border-b border-gray-100 pb-4">
                    <h4 class="font-extrabold text-gray-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-coins text-emerald-600"></i>
                        <span>السياسات المالية ونسب العمولات والضمان</span>
                    </h4>
                    <p class="text-xs text-gray-400 mt-1">تحديد نسب اقتطاع عمولة المنصة والضرائب ورسوم الخدمة المستحقة</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">نسبة عمولة المنصة الافتراضية (%)</label>
                        <input type="number" step="0.5" name="platform_commission_percent" value="{{ $settings['platform_commission_percent'] ?? '15' }}" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono">
                        <p class="text-[11px] text-gray-400 mt-1">تقتطع تلقائياً من قيمة كل طلب صيانة مكتمل</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">نسبة ضريبة القيمة المضافة VAT (%)</label>
                        <input type="number" step="0.5" name="vat_percent" value="{{ $settings['vat_percent'] ?? '15' }}" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">رسوم طلب خدمة الطوارئ الفورية (Emergency Fee)</label>
                        <input type="number" step="1" name="emergency_fee" value="{{ $settings['emergency_fee'] ?? '25.00' }}" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono">
                        <p class="text-[11px] text-gray-400 mt-1">تضاف تلقائياً عند طلب العميل خدمة صيانة طارئة</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">رمز العملة الافتراضية</label>
                        <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? 'ر.ي' }}" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">فترة الضمان الافتراضية للأعمال (بالأيام)</label>
                        <input type="number" name="default_warranty_days" value="{{ $settings['default_warranty_days'] ?? '30' }}" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono">
                        <p class="text-[11px] text-gray-400 mt-1">ضمان منصة حِرفة على جودة قطع الغيار وعمل الفني</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">الحد الأدنى لطلب سحب الأرباح (ر.ي)</label>
                        <input type="number" step="5" name="min_payout_amount" value="{{ $settings['min_payout_amount'] ?? '50.00' }}" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono">
                        <p class="text-[11px] text-gray-400 mt-1">أقل رصيد يسمح للحرفي بطلب تحويله لحسابه البنكي</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 6: APPS & SYSTEM -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'system'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-6">
                <div class="border-b border-gray-100 pb-4">
                    <h4 class="font-extrabold text-gray-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-mobile-screen-button text-emerald-600"></i>
                        <span>روابط تطبيقات المتاجر وحالة النظام</span>
                    </h4>
                    <p class="text-xs text-gray-400 mt-1">روابط تنزيل التطبيقات للعملاء والحرفيين والتحكم بوضع الصيانة</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-brands fa-google-play text-emerald-600"></i>
                            <span>تطبيق العميل - Google Play Store</span>
                        </label>
                        <input type="url" name="play_store_url" value="{{ $settings['play_store_url'] ?? '' }}" placeholder="https://play.google.com/store/apps/details?id=com.herfa.customer" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none" dir="ltr">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-brands fa-apple text-gray-800"></i>
                            <span>تطبيق العميل - Apple App Store</span>
                        </label>
                        <input type="url" name="app_store_url" value="{{ $settings['app_store_url'] ?? '' }}" placeholder="https://apps.apple.com/app/herfa/id123" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none" dir="ltr">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-brands fa-google-play text-blue-600"></i>
                            <span>تطبيق الحرفي - Google Play Store</span>
                        </label>
                        <input type="url" name="craftsman_play_store_url" value="{{ $settings['craftsman_play_store_url'] ?? '' }}" placeholder="https://play.google.com/store/apps/details?id=com.herfa.craftsman" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none" dir="ltr">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-brands fa-apple text-gray-800"></i>
                            <span>تطبيق الحرفي - Apple App Store</span>
                        </label>
                        <input type="url" name="craftsman_app_store_url" value="{{ $settings['craftsman_app_store_url'] ?? '' }}" placeholder="https://apps.apple.com/app/herfa-craftsman/id456" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none" dir="ltr">
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">نص حقوق النشر والملكية (Copyright Footer)</label>
                        <input type="text" name="copyright_text" value="{{ $settings['copyright_text'] ?? 'جميع الحقوق محفوظة © 2026 لمنصة حِرفة لخدمات الصيانة' }}" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">نبذة عن منصة حِرفة (About Us)</label>
                        <textarea name="about_us" rows="3" class="w-full p-3 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none leading-relaxed">{{ $settings['about_us'] ?? 'منصة حِرفة هي المنصة الرائدة لخدمات صيانة المنازل والمنشآت وربط العملاء بأمهر الحرفيين والمهنيين المعتمدين.' }}</textarea>
                    </div>

                    <!-- Maintenance Mode Switch -->
                    <div class="p-4 bg-amber-50/60 rounded-2xl border border-amber-200 flex items-start gap-3">
                        <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" {{ (!empty($settings['maintenance_mode']) && $settings['maintenance_mode'] == '1') ? 'checked' : '' }} class="mt-1 rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                        <div class="flex-1">
                            <label for="maintenance_mode" class="block text-xs font-bold text-amber-900 cursor-pointer">تفعيل وضع الصيانة العام للمنصة (Maintenance Mode)</label>
                            <p class="text-[11px] text-amber-700 mt-0.5">عند التفعيل، سيتم إيقاف استقبال الطلبات مؤقتاً وعرض رسالة الصيانة للمستخدمين</p>
                            <div class="mt-3">
                                <label class="block text-[11px] font-bold text-gray-700 mb-1">رسالة وضع الصيانة</label>
                                <input type="text" name="maintenance_message" value="{{ $settings['maintenance_message'] ?? 'الموقع والمنظومة تحت الصيانة والتحديث الدوري، سنعود قريباً!' }}" class="w-full p-2.5 rounded-xl border border-amber-300 text-xs bg-white focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 7: ALL RAW SETTINGS TABLE -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'all'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-gray-800 text-base flex items-center gap-2">
                            <i class="fa-solid fa-database text-emerald-600"></i>
                            <span>سجل كافة المتغيرات والإعدادات المخزنة ({{ $settingsList->count() }})</span>
                        </h4>
                        <p class="text-xs text-gray-400 mt-1">تعديل أي قيمة مباشرة أو حذف المتغيرات المخصصة</p>
                    </div>
                    <button type="button" @click="showAddSettingModal = true" class="px-3.5 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus"></i>
                        <span>إضافة متغير</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs uppercase">
                            <tr>
                                <th class="p-3">مفتاح الإعداد (Key)</th>
                                <th class="p-3">القيمة (Value)</th>
                                <th class="p-3">المجموعة</th>
                                <th class="p-3">الوصف</th>
                                <th class="p-3 text-center">إجراء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($settingsList as $st)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="p-3 font-mono text-xs font-bold text-gray-700">
                                        {{ $st->setting_key }}
                                    </td>
                                    <td class="p-3">
                                        @if(str_contains($st->setting_value, '/storage/'))
                                            <div class="flex items-center gap-2">
                                                <img src="{{ asset($st->setting_value) }}" class="w-8 h-8 object-contain rounded border bg-white p-0.5">
                                                <span class="font-mono text-xs text-gray-500 truncate max-w-[200px]" dir="ltr">{{ $st->setting_value }}</span>
                                            </div>
                                        @elseif(str_starts_with($st->setting_value, '#') && strlen($st->setting_value) === 7)
                                            <div class="flex items-center gap-2">
                                                <div class="w-5 h-5 rounded-md border shadow-xs" style="background-color: {{ $st->setting_value }}"></div>
                                                <span class="font-mono text-xs font-bold text-gray-700 uppercase">{{ $st->setting_value }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-800 line-clamp-1 max-w-[280px]">{{ $st->setting_value }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">
                                            {{ $st->setting_group }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-xs text-gray-400">
                                        {{ $st->description ?? '-' }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <button type="button" @click="selectedSetting = { id: {{ $st->id }}, key: '{{ $st->setting_key }}' }; showDeleteSettingModal = true" class="p-1 px-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs transition" title="حذف">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sticky Save Button Bar -->
        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-gray-200 shadow-xl flex items-center justify-between">
            <div class="text-xs text-gray-500 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-emerald-600 text-sm"></i>
                <span>تأكد من الضغط على حفظ لتطبيق كافة التغييرات على الهوية والألوان والموقع فوراً</span>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>حفظ وتحديث الإعدادات</span>
            </button>
        </div>
    </form>

    <!-- MODAL: Add Custom Setting -->
    <div x-show="showAddSettingModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showAddSettingModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100">
            <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-emerald-600"></i>
                <span>إضافة متغير إعداد جديد</span>
            </h3>
            <form action="{{ route('admin.settings.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">مفتاح الإعداد (Key) *</label>
                    <input type="text" name="setting_key" required placeholder="مثال: custom_header_text" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none" dir="ltr">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">المجموعة (Group) *</label>
                    <select name="setting_group" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="general">عام (General)</option>
                        <option value="branding">هوية بصرية (Branding)</option>
                        <option value="appearance">المظهر والألوان (Appearance)</option>
                        <option value="social">التواصل والشبكات (Social)</option>
                        <option value="financial">المالية والعمولات (Financial)</option>
                        <option value="system">النظام والتطبيقات (System)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">القيمة (Value) *</label>
                    <textarea name="setting_value" rows="2" required placeholder="قيمة المتغير..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">الوصف</label>
                    <input type="text" name="description" placeholder="شرح مبسط لاستخدام هذا المتغير" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showAddSettingModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">حفظ الإعداد</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Delete Setting -->
    <div x-show="showDeleteSettingModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showDeleteSettingModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-100 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash-can text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">حذف متغير الإعداد</h3>
            <p class="text-xs text-gray-500 mt-1">هل أنت متأكد من حذف المتغير (<span class="font-mono font-bold text-red-600" x-text="selectedSetting.key"></span>) نهائياً من النظام؟</p>
            <form :action="'{{ url('/admin/settings') }}/' + selectedSetting.id" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteSettingModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 shadow-md shadow-red-600/20">تأكيد الحذف</button>
            </form>
        </div>
    </div>

    <!-- MODAL: Lightbox Image Viewer -->
    <div x-show="showImageModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
        <div @click.away="showImageModal = false" class="bg-white rounded-2xl overflow-hidden max-w-3xl w-full shadow-2xl border border-gray-700">
            <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50">
                <h4 class="font-bold text-gray-800 text-sm" x-text="modalImageTitle"></h4>
                <button @click="showImageModal = false" class="text-gray-400 hover:text-gray-700 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-4 flex items-center justify-center bg-gray-900/5 min-h-[300px]">
                <img :src="modalImageUrl" alt="معاينة الصورة" class="max-h-[70vh] max-w-full object-contain rounded-xl shadow-lg">
            </div>
            <div class="p-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button @click="showImageModal = false" class="px-4 py-2 rounded-xl bg-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-300">
                    إغلاق
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
