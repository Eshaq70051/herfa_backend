<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة التحكم') | {{ $platformSettings['app_name'] ?? 'حِرفة HERFA' }}</title>
    @if(!empty($platformSettings['site_favicon']))
        <link rel="icon" href="{{ asset($platformSettings['site_favicon']) }}">
    @endif
    <!-- Tailwind CSS (Local & Offline Ready) -->
    <script src="{{ asset('vendor/tailwind/tailwind.js') }}"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f8fc',
                            100: '#d9eef7',
                            200: '#b8e1f1',
                            300: '#87ceea',
                            400: '#4fb4df',
                            500: '#0076AD', // const Color.fromRGBO(0, 118, 173, 1)
                            600: '#006594',
                            700: '#00547c',
                            800: '#034565',
                            900: '#083a54',
                            950: '#052538',
                        },
                        brand: {
                            blue: '#0076AD',
                            teal: '#0d9488',
                            amber: '#f59e0b',
                            slate: '#0f172a',
                        }
                    },
                    fontFamily: {
                        sans: ['Cairo', 'system-ui', '-apple-system', 'Segoe UI', 'Tahoma', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Cairo Font & Google Fonts Fallback -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fonts/cairo/cairo.css') }}">

    <!-- FontAwesome Icons & CDN Fallback -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

    <!-- Custom Admin Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <!-- Alpine.js (Local & Offline Ready) -->
    <script defer src="{{ asset('vendor/alpine/alpine.min.js') }}"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased flex h-screen overflow-hidden" x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') !== null ? localStorage.getItem('sidebarOpen') === 'true' : true }" x-init="$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val))">

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="transition-all duration-300 ease-in-out bg-slate-900 text-white flex flex-col z-30 shadow-xl flex-shrink-0">
        <!-- Logo Header -->
        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800 bg-slate-950/40">
            <div class="flex items-center space-x-3 space-x-reverse" x-show="sidebarOpen">
                @if(!empty($platformSettings['site_logo_dark']))
                    <a href="{{ route('admin.dashboard') }}" class="block">
                        <img src="{{ asset($platformSettings['site_logo_dark']) }}" alt="{{ $platformSettings['app_name'] ?? 'HERFA' }}" class="h-10 max-w-[170px] object-contain">
                    </a>
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary-600 to-primary-400 flex items-center justify-center text-white shadow-lg shadow-primary-600/30">
                        <i class="fa-solid fa-wrench text-lg"></i>
                    </div>
                    <div>
                        <h1 class="font-extrabold text-lg text-white leading-tight tracking-wide">{{ $platformSettings['app_name'] ?? 'حِرفة | HERFA' }}</h1>
                        <p class="text-[10px] text-gray-400 font-medium">لوحة إدارة المنظومة الشاملة</p>
                    </div>
                @endif
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-slate-800 focus:outline-none transition">
                <i :class="sidebarOpen ? 'fa-solid fa-bars-staggered' : 'fa-solid fa-bars'" class="text-lg"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <div id="sidebarNav" class="flex-1 overflow-y-auto py-4 px-3 space-y-1.5 custom-scrollbar">
            @can('admin.dashboard.view')
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-chart-pie w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">الرئيسية والمؤشرات</span>
            </a>
            @endcan

            @canany(['admin.orders.view', 'admin.craftsmen.view', 'admin.catalog.view', 'admin.clients.view', 'admin.users.view'])
            <div class="pt-3 pb-1" x-show="sidebarOpen">
                <p class="px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">إدارة العمليات والخدمات</p>
            </div>

            <!-- Orders -->
            @can('admin.orders.view')
            <a href="{{ route('admin.orders.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.orders.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-clipboard-list w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">طلبات الصيانة والخدمات</span>
            </a>
            @endcan

            <!-- Craftsmen -->
            @can('admin.craftsmen.view')
            <a href="{{ route('admin.craftsmen.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.craftsmen.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-user-gear w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">الحرفيين والمهنيين</span>
            </a>
            @endcan

            <!-- Categories & Services -->
            @can('admin.catalog.view')
            <a href="{{ route('admin.categories.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.categories.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-layer-group w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">الأقسام والخدمات</span>
            </a>
            @endcan

            <!-- Clients -->
            @can('admin.clients.view')
            <a href="{{ route('admin.clients.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.clients.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-users w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">العملاء والمستخدمين</span>
            </a>
            @endcan

            <!-- Roles & Permissions -->
            @can('admin.users.view')
            <a href="{{ route('admin.users.roles') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-user-shield w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">الأدوار والصلاحيات</span>
            </a>
            @endcan
            @endcanany

            @canany(['admin.locations.view', 'admin.marketing.view', 'admin.notifications.view', 'admin.stores.view'])
            <div class="pt-3 pb-1" x-show="sidebarOpen">
                <p class="px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">التشغيل والمواقع والتسويق</p>
            </div>

            <!-- Locations -->
            @can('admin.locations.view')
            <a href="{{ route('admin.locations.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.locations.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-map-location-dot w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">المواقع والمحافظات</span>
            </a>
            @endcan

            <!-- Advertisements & Offers -->
            @can('admin.marketing.view')
            <a href="{{ route('admin.advertisements.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.advertisements.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-bullhorn w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">العروض والإعلانات</span>
            </a>
            @endcan

            <!-- Notifications Center -->
            @can('admin.notifications.view')
            <a href="{{ route('admin.notifications.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.notifications.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-bell w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">إشعارات التطبيق</span>
            </a>
            @endcan

            <!-- Stores & Partners -->
            @can('admin.stores.view')
            <a href="{{ route('admin.stores.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.stores.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-store w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">خدمات المتاجر والشركاء</span>
            </a>
            @endcan
            @endcanany

            @canany(['admin.financials.view', 'admin.accounting.view', 'admin.suppliers.view', 'admin.employees.view', 'admin.reviews.view', 'admin.support.view'])
            <div class="pt-3 pb-1" x-show="sidebarOpen">
                <p class="px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">المالية والمحاسبة والمشتريات</p>
            </div>

            <!-- Financials -->
            @can('admin.financials.view')
            <a href="{{ route('admin.financials.payouts') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.financials.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-wallet w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">المعاملات ووسائل الدفع</span>
            </a>
            @endcan

            @can('admin.accounting.view')
            <a href="{{ route('admin.accounting.accounts') }}" class="sidebar-item flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.accounting.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-book w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">المحاسبة والقيود</span>
            </a>
            <div x-show="sidebarOpen" class="mr-8 grid gap-1 text-xs text-gray-400">
                <a href="{{ route('admin.accounting.accounts') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.accounting.accounts') ? 'bg-primary-600/40 text-white font-bold border-r-2 border-primary-400' : '' }}">شجرة الحسابات</a>
                <a href="{{ route('admin.accounting.currencies') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.accounting.currencies') ? 'bg-primary-600/40 text-white font-bold border-r-2 border-primary-400' : '' }}">العملات وأسعار الصرف</a>
                <a href="{{ route('admin.accounting.periods') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.accounting.periods') ? 'bg-primary-600/40 text-white font-bold border-r-2 border-primary-400' : '' }}">الفترات المحاسبية</a>
                <a href="{{ route('admin.accounting.journal') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.accounting.journal') ? 'bg-primary-600/40 text-white font-bold border-r-2 border-primary-400' : '' }}">قيود اليومية العامة</a>
                <a href="{{ route('admin.accounting.earnings') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.accounting.earnings') ? 'bg-primary-600/40 text-white font-bold border-r-2 border-primary-400' : '' }}">مستحقات الحرفيين</a>
                <a href="{{ route('admin.accounting.collections') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.accounting.collections') ? 'bg-primary-600/40 text-white font-bold border-r-2 border-primary-400' : '' }}">التحصيلات النقدية</a>
                <a href="{{ route('admin.accounting.settlements') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white font-bold {{ request()->routeIs('admin.accounting.settlements') ? 'bg-primary-600/40 text-white border-r-2 border-primary-400' : 'text-amber-400' }}">تسويات وحوالات الحرفيين</a>
                <a href="{{ route('admin.accounting.payouts') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.accounting.payouts') ? 'bg-primary-600/40 text-white font-bold border-r-2 border-primary-400' : '' }}">صرف الدفعات والسحوبات</a>
                <a href="{{ route('admin.accounting.reports') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white font-bold {{ request()->routeIs('admin.accounting.reports') ? 'bg-primary-600/40 text-white border-r-2 border-primary-400' : 'text-emerald-400' }}">ميزان المراجعة والأستاذ العام</a>
            </div>
            @endcan

            @can('admin.suppliers.view')
            <a href="{{ route('admin.suppliers.index') }}" class="sidebar-item flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.suppliers.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-truck-field w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">الموردون والمشتريات</span>
            </a>
            <div x-show="sidebarOpen" class="mr-8 grid gap-1 text-xs text-gray-400">
                <a href="{{ route('admin.suppliers.index') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.suppliers.index') ? 'bg-primary-600/40 text-white font-bold border-r-2 border-primary-400' : '' }}">قائمة الموردين</a>
                <a href="{{ route('admin.suppliers.invoices') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.suppliers.invoices') ? 'bg-primary-600/40 text-white font-bold border-r-2 border-primary-400' : '' }}">فواتير الشراء</a>
                <a href="{{ route('admin.suppliers.payments') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.suppliers.payments') ? 'bg-primary-600/40 text-white font-bold border-r-2 border-primary-400' : '' }}">سندات صرف الموردين</a>
                <a href="{{ route('admin.suppliers.returns') }}" class="sidebar-subitem rounded-lg px-3 py-2 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.suppliers.returns') ? 'bg-primary-600/40 text-white font-bold border-r-2 border-primary-400' : '' }}">مردودات المشتريات</a>
            </div>
            @endcan

            @can('admin.employees.view')
            <a href="{{ route('admin.employees.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.employees.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-people-group w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">الموارد البشرية والموظفون</span>
            </a>
            @endcan

            <!-- Reviews -->
            @can('admin.reviews.view')
            <a href="{{ route('admin.reviews.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.reviews.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-star-half-stroke w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">التقييمات والآراء</span>
            </a>
            @endcan

            <!-- Support Tickets -->
            @can('admin.support.view')
            <a href="{{ route('admin.tickets.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.tickets.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-headset w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">الدعم الفني والشكاوى</span>
            </a>
            @endcan
            @endcanany

            <!-- Settings -->
            @can('admin.settings.view')
            <div class="pt-3 pb-1" x-show="sidebarOpen">
                <p class="px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">الإعدادات العامة</p>
            </div>

            <a href="{{ route('admin.settings.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.settings.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-sliders w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">إعدادات المنظومة</span>
            </a>
            @endcan
        </div>

        <!-- Footer Profile -->
        <div class="p-3 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between">
            <div class="flex items-center space-x-3 space-x-reverse" x-show="sidebarOpen">
                <div class="w-9 h-9 rounded-full bg-primary-500/20 text-primary-400 border border-primary-500/30 flex items-center justify-center font-bold">
                    {{ mb_substr(auth()->user()->full_name ?? 'م', 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-bold text-white truncate">{{ auth()->user()->full_name ?? 'المستخدم' }}</p>
                    <p class="text-[11px] text-primary-400 font-medium truncate">{{ auth()->user()->roles->first()?->name ?? (auth()->user()->role === 'admin' ? 'مدير عام' : 'موظف') }}</p>
                </div>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" title="تسجيل الخروج" class="p-2 text-gray-400 hover:text-red-400 hover:bg-slate-800 rounded-lg transition">
                    <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Top Progress Bar for Seamless In-Page Navigation -->
    <div id="spaProgressBar" class="fixed top-0 left-0 right-0 h-[2.5px] bg-gradient-to-r from-primary-500 via-emerald-400 to-primary-600 z-[99999] transition-all duration-300 opacity-0 pointer-events-none" style="width: 0%;"></div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-gray-200 px-6 flex items-center justify-between shadow-sm z-20 flex-shrink-0">
            <div class="flex items-center space-x-4 space-x-reverse">
                <h2 id="topNavPageTitle" class="text-xl font-bold text-gray-800">@yield('page_title', 'لوحة التحكم')</h2>
                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                    المنظومة متصلة ونشطة
                </div>
            </div>

            <div class="flex items-center space-x-4 space-x-reverse">
                <!-- Global Quick Search for Orders -->
                @can('admin.orders.view')
                <form action="{{ route('admin.orders.index') }}" method="GET" class="relative hidden md:block">
                    <input type="text" name="search" placeholder="بحث سريع عن طلب (مثال: 6805)..." class="w-56 pr-9 pl-3 py-1.5 rounded-xl border border-gray-200 text-xs bg-gray-50 focus:bg-white focus:ring-2 focus:ring-primary-500 outline-none transition font-medium">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                </form>
                @endcan

                @can('admin.stores.view')
                <a href="{{ url('/api/store-services') }}" target="_blank" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-store text-primary-600"></i>
                    <span>API المتاجر</span>
                </a>
                @endcan
                <div class="w-px h-6 bg-gray-200"></div>
                <span class="text-xs text-gray-500 font-medium">{{ date('Y-m-d') }}</span>
            </div>
        </header>

        <!-- Flash messages -->
        <div id="flashMessagesContainer">
            @if(session('success'))
                <div class="mx-6 mt-4 p-4 rounded-xl bg-primary-50 border border-primary-200 text-primary-900 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-primary-600 text-lg"></i>
                        <span class="font-semibold text-sm">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-primary-600 hover:text-primary-900"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="mx-6 mt-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-exclamation text-red-600 text-lg"></i>
                        <span class="font-semibold text-sm">{{ $errors->first() }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif
        </div>

        <!-- Page Dynamic Body -->
        <main id="mainContent" class="flex-1 overflow-y-auto p-6 bg-gray-50/70">
            @yield('content')
        </main>
    </div>

    {{-- نظام التنقل الداخلي الفوري وثبات القائمة الجانبية في كافة الواجهات --}}
    <script>
        // =========================================================================
        // 1. نظام ثبات القائمة الجانبية وحفظ موضع التمرير وتركيز العنصر النشط
        // =========================================================================
        function restoreSidebarScroll() {
            const sidebarNav = document.getElementById('sidebarNav');
            if (!sidebarNav) return;

            // تحديد العنصر النشط (سواء كان قسماً رئيسياً أو عنصراً فرعياً)
            const activeSub = sidebarNav.querySelector('.sidebar-subitem.bg-primary-600\\/40, .sidebar-subitem.font-bold');
            const activeMain = sidebarNav.querySelector('.sidebar-item.bg-primary-600, a.bg-primary-600');
            const activeElement = activeSub || activeMain;

            const savedScroll = sessionStorage.getItem('sidebarScrollTop');

            if (savedScroll !== null && parseInt(savedScroll, 10) > 0) {
                sidebarNav.scrollTop = parseInt(savedScroll, 10);
            } else if (activeElement) {
                // تمرير القائمة بدقة ليظهر العنصر المفتوح حالياً في منتصف القائمة تماماً
                const targetScroll = activeElement.offsetTop - (sidebarNav.clientHeight / 2) + (activeElement.clientHeight / 2);
                sidebarNav.scrollTop = Math.max(0, targetScroll);
                sessionStorage.setItem('sidebarScrollTop', sidebarNav.scrollTop);
            }
        }

        // حفظ موضع التمرير عند النقر على أي رابط في القائمة
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarNav = document.getElementById('sidebarNav');
            if (!sidebarNav) return;

            restoreSidebarScroll();

            // حفظ الموضع عند قيام المستخدم بالتمرير اليدوي
            let scrollTimeout;
            sidebarNav.addEventListener('scroll', function () {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    sessionStorage.setItem('sidebarScrollTop', sidebarNav.scrollTop);
                }, 100);
            }, { passive: true });

            // إعادة التثبيت بعد تحميل ملفات الخطوط والـ Alpine لضمان عدم ارتداد الشريط
            window.addEventListener('load', restoreSidebarScroll);
            setTimeout(restoreSidebarScroll, 100);
            setTimeout(restoreSidebarScroll, 300);
        });

        // =========================================================================
        // 2. نظام فتح الصفحات داخل المنظومة مع بقاء القائمة ثابتة تماماً (Fast SPA Loader)
        // =========================================================================
        const progressBar = document.getElementById('spaProgressBar');

        function showProgress() {
            if (!progressBar) return;
            progressBar.style.opacity = '1';
            progressBar.style.width = '35%';
            setTimeout(() => { if (progressBar.style.opacity === '1') progressBar.style.width = '75%'; }, 150);
        }

        function finishProgress() {
            if (!progressBar) return;
            progressBar.style.width = '100%';
            setTimeout(() => {
                progressBar.style.opacity = '0';
                setTimeout(() => { progressBar.style.width = '0%'; }, 300);
            }, 200);
        }

        async function loadPageInside(url, pushState = true) {
            showProgress();
            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Herfa-Spa': '1' }
                });

                if (!response.ok) {
                    window.location.href = url;
                    return;
                }

                const html = await response.text();
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');

                // 1. تحديث عنوان الصفحة بالمتصفح
                if (newDoc.title) {
                    document.title = newDoc.title;
                }

                // 2. تحديث ترويسة شريط النافبار
                const newTitle = newDoc.getElementById('topNavPageTitle');
                const curTitle = document.getElementById('topNavPageTitle');
                if (newTitle && curTitle) {
                    curTitle.innerHTML = newTitle.innerHTML;
                }

                // 3. تحديث رسائل التنبيه والـ Flash messages
                const newFlash = newDoc.getElementById('flashMessagesContainer');
                const curFlash = document.getElementById('flashMessagesContainer');
                if (newFlash && curFlash) {
                    curFlash.innerHTML = newFlash.innerHTML;
                }

                // 4. استبدال محتوى الصفحة الرئيسي داخل الحاوية دون المساس بالقائمة الجانبية
                const newMain = newDoc.getElementById('mainContent');
                const curMain = document.getElementById('mainContent');
                if (newMain && curMain) {
                    curMain.innerHTML = newMain.innerHTML;
                    curMain.scrollTop = 0;

                    // تشغيل أي سكريبتات مرفقة بالصفحة الجديدة
                    const scripts = newMain.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.textContent = oldScript.textContent;
                        document.body.appendChild(newScript);
                        newScript.remove();
                    });

                    // تشغيل سكريبتات push scripts إن وجدت في الصفحة الجديدة
                    const pushedScripts = newDoc.querySelectorAll('body > script:not([src])');
                    pushedScripts.forEach(ps => {
                        if (ps.textContent.includes('edit') || ps.textContent.includes('view') || ps.textContent.includes('Modal')) {
                            const s = document.createElement('script');
                            s.textContent = ps.textContent;
                            document.body.appendChild(s);
                            s.remove();
                        }
                    });

                    // إعادة تهيئة مكوّنات Alpine.js في الصفحة الجديدة
                    if (window.Alpine) {
                        window.Alpine.initTree(curMain);
                    }

                    // إعادة تهيئة البحث الذكي في القوائم المنسدلة
                    initAllSearchableSelects(curMain);
                }

                // 5. تحديث الروابط النشطة في القائمة الجانبية بدقة
                updateActiveSidebarLink(url);

                // 6. حفظ الرابط في سجل التصفح
                if (pushState) {
                    window.history.pushState({ url }, '', url);
                }

                finishProgress();
            } catch (err) {
                console.error('SPA load error, navigating naturally:', err);
                window.location.href = url;
            }
        }

        // تحديث إضاءة وتحديد الرابط النشط بالقائمة الجانبية
        function updateActiveSidebarLink(url) {
            const sidebarNav = document.getElementById('sidebarNav');
            if (!sidebarNav) return;

            const urlObj = new URL(url, window.location.origin);
            const currentPath = urlObj.pathname;

            // إزالة التحديد القديم
            sidebarNav.querySelectorAll('a').forEach(a => {
                a.classList.remove('bg-primary-600', 'text-white', 'shadow-md', 'shadow-primary-600/30', 'bg-primary-600/40', 'border-r-2', 'border-primary-400');
                if (a.classList.contains('sidebar-subitem')) {
                    a.classList.add('text-gray-400');
                } else if (a.classList.contains('sidebar-item')) {
                    a.classList.add('text-gray-300');
                }
            });

            // العثور على الرابط الجديد المطابق
            let matchedLink = null;
            sidebarNav.querySelectorAll('a').forEach(a => {
                const aPath = new URL(a.href, window.location.origin).pathname;
                if (aPath === currentPath) {
                    matchedLink = a;
                }
            });

            if (matchedLink) {
                if (matchedLink.classList.contains('sidebar-subitem')) {
                    matchedLink.classList.add('bg-primary-600/40', 'text-white', 'font-bold', 'border-r-2', 'border-primary-400');
                    // تمييز القائمة الرئيسية الأب أيضاً
                    const parentSection = matchedLink.closest('div');
                    if (parentSection && parentSection.previousElementSibling && parentSection.previousElementSibling.tagName === 'A') {
                        parentSection.previousElementSibling.classList.add('bg-primary-600', 'text-white', 'shadow-md', 'shadow-primary-600/30');
                    }
                } else {
                    matchedLink.classList.add('bg-primary-600', 'text-white', 'shadow-md', 'shadow-primary-600/30');
                }

                // الحفاظ على موضع القائمة ثابتاً وتوسيط العنصر إذا لزم الأمر
                sessionStorage.setItem('sidebarScrollTop', sidebarNav.scrollTop);
            }
        }

        // دعم أزرار الرجوع والتقدم بالمتصفح (Back / Forward)
        window.addEventListener('popstate', (e) => {
            loadPageInside(window.location.href, false);
        });

        // تفعيل التنقل الداخلي على كافة روابط القائمة الجانبية
        document.addEventListener('DOMContentLoaded', () => {
            const sidebarNav = document.getElementById('sidebarNav');
            if (!sidebarNav) return;

            sidebarNav.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.includes('/logout')) {
                    return;
                }

                // إذا ضغط المستخدم على Ctrl أو فتح في تبويب جديد اتركه للمتصفح
                if (e.ctrlKey || e.metaKey || e.button === 1 || link.target === '_blank') {
                    return;
                }

                e.preventDefault();
                // حفظ موضع شريط التمرير بدقة
                sessionStorage.setItem('sidebarScrollTop', sidebarNav.scrollTop);
                loadPageInside(href, true);
            });
        });

        // =========================================================================
        // نظام البحث الذكي الفوري الشامل لكافة حقول الاختيار (Searchable Selects)
        // يحوّل أي قائمة منسدلة تلقائياً إلى حقل إدخال يقبل الكتابة والفلترة اللحظية
        // =========================================================================
        function makeSearchableSelect(select) {
            if (!select || select.dataset.searchableReady === 'true') return;
            // استثناء القوائم المحددة صراحة أو القوائم متعددة الاختيار
            if (select.hasAttribute('data-no-search') || select.multiple) return;

            select.dataset.searchableReady = 'true';

            // إخفاء القائمة الأصلية شكلياً مع إبقائها في الـ DOM لضمان إرسال البيانات
            select.style.position = 'absolute';
            select.style.opacity = '0';
            select.style.pointerEvents = 'none';
            select.style.height = '0';
            select.style.width = '0';
            select.style.margin = '0';
            select.style.padding = '0';
            select.tabIndex = -1;

            // إنشاء الحاوية المخصصة
            const wrapper = document.createElement('div');
            wrapper.className = 'searchable-select-wrapper relative w-full text-right';

            // النص التلميحي (Placeholder)
            const firstOpt = select.options[0];
            const placeholder = select.getAttribute('placeholder') || 
                                (firstOpt && (!firstOpt.value || firstOpt.value === '') ? firstOpt.text.trim() : 'اكتب للبحث السريع...');

            // النص المختار حالياً
            const selectedOpt = select.options[select.selectedIndex];
            const initialText = (selectedOpt && selectedOpt.value !== '') ? selectedOpt.text.trim() : '';

            // حقل البحث والكتابة
            const input = document.createElement('input');
            input.type = 'text';
            input.autocomplete = 'off';
            input.placeholder = placeholder;
            input.value = initialText;
            input.className = 'searchable-input w-full pr-8 pl-12 py-2 text-xs font-semibold rounded-xl border border-slate-200 bg-slate-50 text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 focus:outline-none transition shadow-sm';
            
            if (select.disabled) input.disabled = true;
            if (select.required) {
                input.required = true;
                select.removeAttribute('required');
                select.dataset.wasRequired = 'true';
            }

            // أيقونة البحث في اليمين
            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-slate-400 text-xs cursor-pointer transition hover:text-primary-600';
            icon.title = 'بحث فوري';
            icon.addEventListener('click', () => {
                input.focus();
            });

            // حاوية الأزرار في اليسار (مسح + سهم القائمة)
            const actionsContainer = document.createElement('div');
            actionsContainer.className = 'absolute left-2.5 top-2 flex items-center gap-1.5 text-xs text-slate-400';

            // زر مسح الاختيار
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'hover:text-red-500 transition ' + (initialText ? '' : 'hidden');
            clearBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            clearBtn.title = 'مسح الاختيار';

            // سهم فتح القائمة
            const chevronBtn = document.createElement('button');
            chevronBtn.type = 'button';
            chevronBtn.className = 'hover:text-primary-600 transition cursor-pointer';
            chevronBtn.innerHTML = '<i class="fa-solid fa-chevron-down text-[10px]"></i>';
            chevronBtn.title = 'عرض كافة الخيارات';

            actionsContainer.appendChild(clearBtn);
            actionsContainer.appendChild(chevronBtn);

            // قائمة الخيارات المنسدلة
            const dropdown = document.createElement('div');
            dropdown.className = 'searchable-dropdown absolute z-[100] mt-1 max-h-60 w-full overflow-y-auto rounded-xl bg-white p-1.5 shadow-2xl border border-slate-200 text-xs space-y-0.5 hidden custom-scrollbar';
            dropdown.style.minWidth = '180px';

            wrapper.appendChild(input);
            wrapper.appendChild(icon);
            wrapper.appendChild(actionsContainer);
            wrapper.appendChild(dropdown);

            select.parentNode.insertBefore(wrapper, select.nextSibling);

            let activeIndex = -1;
            let visibleItems = [];

            // دالة بناء وتصفية الخيارات أثناء الكتابة
            function renderOptions(filter = '') {
                dropdown.innerHTML = '';
                visibleItems = [];
                activeIndex = -1;
                const q = filter.trim().toLowerCase();
                let matchCount = 0;

                Array.from(select.children).forEach((child) => {
                    if (child.tagName === 'OPTGROUP') {
                        const groupLabel = child.label;
                        let groupHasMatch = false;
                        const groupItems = [];

                        Array.from(child.children).forEach((opt) => {
                            const text = opt.text.trim();
                            const val = opt.value;
                            if (!val && text === '') return;
                            const isMatch = !q || text.toLowerCase().includes(q) || val.toLowerCase().includes(q);
                            if (isMatch) {
                                groupHasMatch = true;
                                groupItems.push({ opt, text, val });
                            }
                        });

                        if (groupHasMatch) {
                            const groupHeader = document.createElement('div');
                            groupHeader.className = 'px-2 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 rounded mt-1 first:mt-0';
                            groupHeader.textContent = groupLabel;
                            dropdown.appendChild(groupHeader);

                            groupItems.forEach(({ opt, text, val }) => {
                                createOptionElement(opt, text, val);
                                matchCount++;
                            });
                        }
                    } else if (child.tagName === 'OPTION') {
                        const opt = child;
                        const text = opt.text.trim();
                        const val = opt.value;
                        if (!val && !q) return;
                        const isMatch = !q || text.toLowerCase().includes(q) || val.toLowerCase().includes(q);
                        if (isMatch) {
                            createOptionElement(opt, text, val);
                            matchCount++;
                        }
                    }
                });

                if (matchCount === 0) {
                    const noRes = document.createElement('div');
                    noRes.className = 'p-3 text-center text-slate-400 text-xs font-semibold';
                    noRes.textContent = 'لا توجد نتائج مطابقة للبحث';
                    dropdown.appendChild(noRes);
                }
            }

            function createOptionElement(opt, text, val) {
                const item = document.createElement('div');
                const isSelected = select.value === val && val !== '';
                item.className = 'flex items-center justify-between p-2 rounded-lg cursor-pointer transition select-none ' + 
                    (isSelected ? 'bg-primary-50 text-primary-900 font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900') +
                    (opt.disabled ? ' opacity-50 pointer-events-none' : '');
                
                item.innerHTML = `<span class="truncate">${text}</span>` + 
                                 (isSelected ? '<i class="fa-solid fa-check text-primary-600 text-[10px] mr-2"></i>' : '');

                item.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectOption(val, text);
                });

                dropdown.appendChild(item);
                visibleItems.push({ element: item, val, text });
            }

            function selectOption(val, text) {
                select.value = val;
                input.value = text;
                if (input.required) input.setCustomValidity('');
                clearBtn.classList.toggle('hidden', !val);
                dropdown.classList.add('hidden');
                select.dispatchEvent(new Event('change', { bubbles: true }));
                select.dispatchEvent(new Event('input', { bubbles: true }));
            }

            // أحداث حقل البحث
            input.addEventListener('focus', () => {
                input.select();
                renderOptions('');
                dropdown.classList.remove('hidden');
            });

            input.addEventListener('click', () => {
                if (dropdown.classList.contains('hidden')) {
                    renderOptions(input.value);
                    dropdown.classList.remove('hidden');
                }
            });

            chevronBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (dropdown.classList.contains('hidden')) {
                    renderOptions('');
                    dropdown.classList.remove('hidden');
                    input.focus();
                } else {
                    dropdown.classList.add('hidden');
                }
            });

            input.addEventListener('input', () => {
                renderOptions(input.value);
                dropdown.classList.remove('hidden');
                clearBtn.classList.toggle('hidden', !input.value);
                if (input.required && !input.value) {
                    select.value = '';
                }
            });

            // دعم التنقل عبر أزرار الكيبورد (الأسهم، Enter، Esc)
            input.addEventListener('keydown', (e) => {
                if (dropdown.classList.contains('hidden')) {
                    if (e.key === 'ArrowDown' || e.key === 'Enter') {
                        renderOptions('');
                        dropdown.classList.remove('hidden');
                        e.preventDefault();
                    }
                    return;
                }

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (visibleItems.length > 0) {
                        activeIndex = (activeIndex + 1) % visibleItems.length;
                        updateActiveItem();
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (visibleItems.length > 0) {
                        activeIndex = (activeIndex - 1 + visibleItems.length) % visibleItems.length;
                        updateActiveItem();
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIndex >= 0 && activeIndex < visibleItems.length) {
                        const item = visibleItems[activeIndex];
                        selectOption(item.val, item.text);
                    } else if (visibleItems.length === 1) {
                        const item = visibleItems[0];
                        selectOption(item.val, item.text);
                    }
                } else if (e.key === 'Escape') {
                    dropdown.classList.add('hidden');
                    restoreSelectionText();
                }
            });

            function updateActiveItem() {
                visibleItems.forEach((it, idx) => {
                    if (idx === activeIndex) {
                        it.element.classList.add('bg-primary-100', 'text-primary-900');
                        it.element.scrollIntoView({ block: 'nearest' });
                    } else {
                        it.element.classList.remove('bg-primary-100', 'text-primary-900');
                    }
                });
            }

            function restoreSelectionText() {
                const curr = select.options[select.selectedIndex];
                input.value = (curr && curr.value !== '') ? curr.text.trim() : '';
                clearBtn.classList.toggle('hidden', !input.value);
            }

            // زر مسح الاختيار
            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                input.value = '';
                select.value = '';
                clearBtn.classList.add('hidden');
                select.dispatchEvent(new Event('change', { bubbles: true }));
                select.dispatchEvent(new Event('input', { bubbles: true }));
                renderOptions('');
                dropdown.classList.remove('hidden');
                input.focus();
            });

            // إغلاق القائمة عند النقر خارجها
            document.addEventListener('click', (e) => {
                if (!wrapper.contains(e.target)) {
                    dropdown.classList.add('hidden');
                    if (select.value === '' && input.value !== '') {
                        const exactMatch = Array.from(select.options).find(o => o.text.trim().toLowerCase() === input.value.trim().toLowerCase());
                        if (exactMatch) {
                            selectOption(exactMatch.value, exactMatch.text.trim());
                        } else {
                            restoreSelectionText();
                        }
                    } else {
                        restoreSelectionText();
                    }
                }
            });

            // مزامنة الحقل عند تغير القيمة من أي سكربت خارجي
            select.addEventListener('change', () => {
                restoreSelectionText();
            });

            // مراقبة أي تحديث للخيارات ديناميكياً
            const optObserver = new MutationObserver(() => {
                restoreSelectionText();
            });
            optObserver.observe(select, { childList: true, subtree: true, attributes: true });
        }

        // دالة فحص وتفعيل البحث لكافة القوائم بالصفحة
        function initAllSearchableSelects(root = document) {
            const selects = root.querySelectorAll('select:not([data-searchable-ready="true"]):not([data-no-search]):not([multiple])');
            selects.forEach(select => {
                makeSearchableSelect(select);
            });
        }

        // تشغيل النظام عند تحميل الصفحة ومراقبة العناصر الجديدة والنوافذ المنبثقة تلقائياً
        document.addEventListener('DOMContentLoaded', () => {
            initAllSearchableSelects();
            
            const observer = new MutationObserver(() => {
                initAllSearchableSelects();
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>
</body>
</html>
