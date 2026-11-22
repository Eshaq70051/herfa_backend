<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول الإدارة | {{ $platformSettings['app_name'] ?? 'حِرفة HERFA' }}</title>
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
                            500: '#0076AD',
                            600: '#006594',
                            700: '#00547c',
                            800: '#034565',
                            900: '#083a54',
                            950: '#052538',
                        }
                    },
                    fontFamily: {
                        sans: ['Cairo', 'system-ui', '-apple-system', 'Segoe UI', 'Tahoma', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Cairo Font (Local & Offline Ready) -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/cairo/cairo.css') }}">
    <!-- FontAwesome Icons (Local & Offline Ready) -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>body { font-family: 'Cairo', system-ui, -apple-system, 'Segoe UI', Tahoma, Arial, sans-serif; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-cover bg-center relative" style="background-image: url('{{ !empty($platformSettings['login_background']) ? asset($platformSettings['login_background']) : '' }}'); background-color: {{ $platformSettings['sidebar_color'] ?? '#0f172a' }};">

    <!-- Subtle dark overlay -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>

    <div class="relative z-10 max-w-md w-full bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl p-8 border border-white/20">
        <div class="text-center mb-8">
            @if(!empty($platformSettings['site_logo']))
                <div class="flex justify-center mb-4">
                    <img src="{{ asset($platformSettings['site_logo']) }}" alt="{{ $platformSettings['app_name'] ?? 'HERFA' }}" class="h-14 max-w-[240px] object-contain">
                </div>
            @else
                <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#0076AD] to-[#0091D5] items-center justify-center text-white shadow-xl shadow-[#0076AD]/30 mb-4">
                    <i class="fa-solid fa-wrench text-2xl"></i>
                </div>
                <h1 class="text-2xl font-black text-gray-900">{{ $platformSettings['app_name'] ?? 'حِرفة | HERFA' }}</h1>
            @endif
            <p class="text-gray-500 text-xs mt-2 font-medium">{{ $platformSettings['app_slogan'] ?? 'بوابة الدخول إلى لوحة التحكم الإدارية' }}</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">رقم الجوال أو البريد الإلكتروني</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="phone" value="{{ old('phone', '') }}" required class="w-full pr-11 pl-4 py-3 rounded-xl border border-gray-300 focus:border-[#0076AD] focus:ring-2 focus:ring-[#0076AD]/20 outline-none text-left font-medium transition" dir="ltr" placeholder="777000000">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">كلمة المرور</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" value="" required class="w-full pr-11 pl-4 py-3 rounded-xl border border-gray-300 focus:border-[#0076AD] focus:ring-2 focus:ring-[#0076AD]/20 outline-none transition" placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#0076AD] focus:ring-[#0076AD] border-gray-300">
                    <span class="text-gray-600 font-medium">تذكرني على هذا الجهاز</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-[#0076AD] to-[#0091D5] hover:from-[#006594] hover:to-[#0076AD] text-white font-extrabold rounded-xl shadow-lg shadow-[#0076AD]/30 transition duration-200">
                تسجيل الدخول إلى النظام
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center text-xs text-gray-400">
           
        </div>
    </div>

</body>
</html>
