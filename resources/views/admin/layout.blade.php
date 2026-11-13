<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة التحكم') | {{ $platformSettings['app_name'] ?? 'حِرفة HERFA' }}</title>
    @if(!empty($platformSettings['site_favicon']))
        <link rel="icon" href="{{ asset($platformSettings['site_favicon']) }}">
    @endif
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
                        sans: ['Cairo', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">

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
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1.5 custom-scrollbar">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-chart-pie w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">الرئيسية والمؤشرات</span>
            </a>

            <div class="pt-3 pb-1" x-show="sidebarOpen">
                <p class="px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">إدارة العمليات والخدمات</p>
            </div>

            <!-- Orders -->
            <a href="{{ route('admin.orders.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.orders.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-clipboard-list w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">طلبات الصيانة</span>
            </a>

            <!-- Craftsmen -->
            <a href="{{ route('admin.craftsmen.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.craftsmen.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-user-gear w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">الحرفيين والمهنيين</span>
            </a>

            <!-- Categories & Services -->
            <a href="{{ route('admin.categories.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.categories.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-layer-group w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">الأقسام والخدمات</span>
            </a>

            <!-- Store Services (خدمات ومتاجر الشركاء) -->
            <a href="{{ route('admin.stores.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.stores.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-store w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">خدمات ومتاجر الشركاء</span>
            </a>

            <!-- Clients -->
            <a href="{{ route('admin.clients.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.clients.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-users w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">العملاء والمستخدمين</span>
            </a>

            <div class="pt-3 pb-1" x-show="sidebarOpen">
                <p class="px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">التشغيل والمواقع والتسويق</p>
            </div>

            <!-- Locations -->
            <a href="{{ route('admin.locations.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.locations.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-map-location-dot w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">المواقع والمحافظات</span>
            </a>

            <!-- Advertisements & Offers -->
            <a href="{{ route('admin.advertisements.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.advertisements.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-bullhorn w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">العروض والإعلانات</span>
            </a>

            <div class="pt-3 pb-1" x-show="sidebarOpen">
                <p class="px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">المالية والدعم</p>
            </div>

            <!-- Financials -->
            <a href="{{ route('admin.financials.payouts') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.financials.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-wallet w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">المالية ووسائل الدفع</span>
            </a>

            <!-- Reviews -->
            <a href="{{ route('admin.reviews.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.reviews.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-star-half-stroke w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">التقييمات والآراء</span>
            </a>

            <!-- Support Tickets -->
            <a href="{{ route('admin.tickets.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.tickets.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-headset w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">الدعم الفني والشكاوى</span>
            </a>

            <div class="pt-3 pb-1" x-show="sidebarOpen">
                <p class="px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">الإعدادات العامة</p>
            </div>

            <!-- Settings -->
            <a href="{{ route('admin.settings.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.settings.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-sliders w-6 text-center text-base"></i>
                <span class="mr-3 whitespace-nowrap" x-show="sidebarOpen">إعدادات المنظومة</span>
            </a>
        </div>

        <!-- Footer Profile -->
        <div class="p-3 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between">
            <div class="flex items-center space-x-3 space-x-reverse" x-show="sidebarOpen">
                <div class="w-9 h-9 rounded-full bg-primary-500/20 text-primary-400 border border-primary-500/30 flex items-center justify-center font-bold">
                    {{ mb_substr(auth()->user()->full_name ?? 'م', 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-bold text-white truncate">{{ auth()->user()->full_name ?? 'المدير' }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ auth()->user()->phone ?? '0500000000' }}</p>
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

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-gray-200 px-6 flex items-center justify-between shadow-sm z-20 flex-shrink-0">
            <div class="flex items-center space-x-4 space-x-reverse">
                <h2 class="text-xl font-bold text-gray-800">@yield('page_title', 'لوحة التحكم')</h2>
                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                    المنظومة متصلة ونشطة
                </div>
            </div>

            <div class="flex items-center space-x-4 space-x-reverse">
                <a href="{{ url('/api/store-services') }}" target="_blank" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-store text-primary-600"></i>
                    <span>API المتاجر</span>
                </a>
                <div class="w-px h-6 bg-gray-200"></div>
                <span class="text-xs text-gray-500 font-medium">{{ date('Y-m-d') }}</span>
            </div>
        </header>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="mx-6 mt-4 p-4 rounded-xl bg-primary-50 border border-primary-200 text-primary-900 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-primary-600 text-lg"></i>
                    <span class="font-semibold text-sm">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-primary-600 hover:text-primary-900"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if($errors->any())
            <div class="mx-6 mt-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-red-600 text-lg"></i>
                    <span class="font-semibold text-sm">{{ $errors->first() }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        <!-- Page Dynamic Body -->
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50/70">
            @yield('content')
        </main>
    </div>

</body>
</html>
