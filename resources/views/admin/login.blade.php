<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول الإدارة | {{ $platformSettings['app_name'] ?? 'حِرفة HERFA' }}</title>
    @if(!empty($platformSettings['site_favicon']))
        <link rel="icon" href="{{ asset($platformSettings['site_favicon']) }}">
    @endif

    <!-- خط كايرو العربي والخطوط البديلة -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fonts/cairo/cairo.css') }}">

    <!-- أيقونات FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

    <!-- ملف التنسيقات المخصص لكافة واجهات لوحة تحكم حِرفة -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="login-page-wrapper" style="{{ !empty($platformSettings['login_background']) ? "background-image: url('" . asset($platformSettings['login_background']) . "');" : '' }} {{ !empty($platformSettings['sidebar_color']) ? "background-color: " . $platformSettings['sidebar_color'] . ";" : '' }}">

    <!-- طبقة تغبيش شفافة أعلى الخلفية -->
    <div class="login-backdrop-overlay"></div>

    <!-- بطاقة تسجيل الدخول -->
    <div class="login-card">
        <div class="login-brand-header">
            @if(!empty($platformSettings['site_logo']))
                <div class="login-brand-logo-custom">
                    <img src="{{ asset($platformSettings['site_logo']) }}" alt="{{ $platformSettings['app_name'] ?? 'HERFA' }}">
                </div>
            @else
                <div class="login-brand-logo">
                    <i class="fa-solid fa-wrench"></i>
                </div>
                <h1 class="login-title">{{ $platformSettings['app_name'] ?? 'حِرفة | HERFA' }}</h1>
            @endif
            <p class="login-subtitle">{{ $platformSettings['app_slogan'] ?? 'بوابة الدخول إلى لوحة التحكم الإدارية' }}</p>
        </div>

        @if($errors->any())
            <div class="login-alert login-alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            <div class="login-form-group">
                <label class="login-form-label">رقم الجوال أو البريد الإلكتروني</label>
                <div class="login-input-wrapper">
                    <span class="login-input-icon">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="phone" value="{{ old('phone', '') }}" required class="login-input" dir="ltr" placeholder="777000000 أو admin@example.com">
                </div>
            </div>

            <div class="login-form-group">
                <label class="login-form-label">كلمة المرور</label>
                <div class="login-input-wrapper">
                    <span class="login-input-icon">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" value="" required class="login-input" placeholder="••••••••">
                </div>
            </div>

            <div class="login-remember-row">
                <label class="login-remember-label">
                    <input type="checkbox" name="remember" class="login-checkbox">
                    <span>تذكرني على هذا الجهاز</span>
                </label>
            </div>

            <button type="submit" class="login-submit-btn">
                <span>تسجيل الدخول إلى النظام</span>
                <i class="fa-solid fa-arrow-left"></i>
            </button>
        </form>

        <div class="login-footer">
            <span>{{ $platformSettings['app_name'] ?? 'HERFA' }} &copy; {{ date('Y') }} - كافة الحقوق محفوظة</span>
        </div>
    </div>

</body>
</html>
