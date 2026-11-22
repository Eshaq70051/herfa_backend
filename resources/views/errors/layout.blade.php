<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | {{ config('app.name', 'حِرفة') }}</title>
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
<body class="bg-gray-50 text-gray-800 antialiased flex items-center justify-center min-h-screen p-6">
    <div class="max-w-md w-full text-center bg-white rounded-3xl p-8 shadow-xl border border-gray-100">
        <div class="w-20 h-20 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center mx-auto mb-6 text-3xl shadow-sm">
            @yield('icon')
        </div>
        <h1 class="text-4xl font-black text-gray-900 mb-2">@yield('code')</h1>
        <h2 class="text-xl font-bold text-gray-800 mb-4">@yield('message')</h2>
        <p class="text-sm text-gray-500 mb-8 leading-relaxed">@yield('description')</p>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm shadow-lg shadow-primary-600/30 transition">
            <i class="fa-solid fa-house"></i>
            <span>العودة للوحة التحكم</span>
        </a>
    </div>
</body>
</html>
