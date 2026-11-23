<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GeneralApiController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

// المسارات العامة: لا تتطلب تسجيل الدخول، وتعرض التسجيل والتصنيفات والخدمات
// والإعدادات وطرق الدفع والتحقق من القسائم.
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/otp/send', [AuthController::class, 'sendOtp']);
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/services', [CategoryController::class, 'services']);
Route::get('/store-services', [GeneralApiController::class, 'storeServices']);
Route::get('/banners', [GeneralApiController::class, 'banners']);
Route::get('/payment-methods', [GeneralApiController::class, 'paymentMethods']);
Route::get('/settings', [GeneralApiController::class, 'settings']);
Route::post('/coupons/check', [GeneralApiController::class, 'checkCoupon']);

// المسارات التالية محمية بمصادقة Sanctum؛ يجب أن يرسل العميل رمز دخول صالحًا.
Route::middleware('auth:sanctum')->group(function () {
    // تسجيل الخروج وإدارة بيانات الحساب ووضع المستخدم وحالة ظهوره للصُنّاع.
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/switch-mode', [AuthController::class, 'switchMode']);
    Route::post('/auth/toggle-online', [AuthController::class, 'toggleOnline']);
    Route::post('/auth/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/craftsman/kyc', [AuthController::class, 'updateKyc']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // عرض عناوين المستخدم وإضافتها وتحديد العنوان الافتراضي أو حذف عنوان.
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::post('/addresses/{id}/default', [AddressController::class, 'setDefault']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);

    // إنشاء الطلبات واستعراضها ومتابعة حالتها وموقعها وفواتيرها ومدفوعاتها.
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/nearby', [OrderController::class, 'nearby']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/accept', [OrderController::class, 'accept']);
    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/invoice', [OrderController::class, 'updateInvoice']);
    Route::post('/orders/{id}/pay', [OrderController::class, 'pay']);
    Route::post('/orders/{id}/verify-completion', [OrderController::class, 'verifyCompletion']);
    Route::post('/orders/{id}/dispute', [OrderController::class, 'openDispute']);
    Route::post('/orders/{id}/apply-coupon', [OrderController::class, 'applyCoupon']);
    Route::post('/orders/{id}/warranty', [OrderController::class, 'claimWarranty']);
    Route::post('/orders/{id}/location', [OrderController::class, 'updateLocation']);
    Route::get('/orders/{id}/location', [OrderController::class, 'getLocation']);
    Route::get('/disputes', [OrderController::class, 'listDisputes']);

    // إرسال عرض سعر مرتبط بطلب معيّن.
    Route::post('/orders/{id}/offers', [GeneralApiController::class, 'submitOffer']);

    // قراءة رسائل الطلب وإرسال رسائل جديدة ضمن المحادثة المرتبطة به.
    Route::get('/orders/{id}/messages', [GeneralApiController::class, 'getMessages']);
    Route::post('/orders/{id}/messages', [GeneralApiController::class, 'sendMessage']);

    // إضافة تقييم بعد تنفيذ الخدمة وتقييم الحرفي للعميل.
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::post('/orders/{id}/rate-customer', [ReviewController::class, 'rateCustomer']);

    // عرض المحفظة وطلب سحب الرصيد أو إيداع مبلغ فيها.
    Route::get('/wallet', [WalletController::class, 'index']);
    Route::post('/wallet/payout', [WalletController::class, 'requestPayout']);
    Route::post('/wallet/deposit', [WalletController::class, 'deposit']);

    // عرض الإشعارات وتحديد إشعار واحد أو جميع الإشعارات كمقروءة.
    Route::get('/notifications', [GeneralApiController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [GeneralApiController::class, 'markNotificationAsRead']);
    Route::post('/notifications/read-all', [GeneralApiController::class, 'markAllNotificationsAsRead']);

    // إدارة ومتابعة تذاكر الدعم والشكاوى للعميل
    Route::get('/support/tickets', [GeneralApiController::class, 'getMyTickets']);
    Route::post('/support/tickets', [GeneralApiController::class, 'submitSupportTicket']);
    Route::get('/support/tickets/{id}', [GeneralApiController::class, 'getTicketDetails']);
    Route::post('/support/tickets/{id}/reply', [GeneralApiController::class, 'replyToTicket']);
});

// مسار احتياطي لخدمة وسائط التخزين عند الطلب عبر واجهة برمجة التطبيقات
Route::get('/storage/{path}', function (string $path) {
    $fullPath = public_path('storage/'.$path);
    if (! file_exists($fullPath)) {
        $fullPath = storage_path('app/public/'.$path);
    }
    if (! file_exists($fullPath) || is_dir($fullPath)) {
        abort(404);
    }

    return response()->file($fullPath);
})->where('path', '.*');
