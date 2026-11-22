<?php

use App\Http\Controllers\Admin\AccountingController;
use App\Http\Controllers\Admin\AdminAdvertisementController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCraftsmanController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFinancialController;
use App\Http\Controllers\Admin\AdminLocationController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminStoreServiceController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// مسار تسجيل الدخول الافتراضي لإعادة التوجيه عند انتهاء الجلسة
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// مسارات مصادقة وتسجيل دخول لوحة الإدارة
Route::get('/admin/login', [AdminDashboardController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminDashboardController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminDashboardController::class, 'logout'])->middleware('auth')->name('admin.logout');

// مسارات لوحة التحكم الإدارية المحمية بنظام الصلاحيات والأدوار
Route::prefix('admin')->middleware(['auth', 'admin.permission'])->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.shortcut');

    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}/status', 'updateStatus')->name('status');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/roles', 'roles')->name('roles');
        Route::post('/roles', 'storeRole')->name('roles.store');
        Route::put('/roles/{id}', 'updateRole')->name('roles.update');
        Route::put('/roles/{id}/permissions', 'updateRolePermissions')->name('roles.permissions');
        Route::delete('/roles/{id}', 'destroyRole')->name('roles.destroy');
    });

    // 1. Orders CRUD (الطلبات)
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [AdminOrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{id}/assign', [AdminOrderController::class, 'assignCraftsman'])->name('orders.assign');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}', [AdminOrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{id}/confirm-payment', [AdminOrderController::class, 'confirmPayment'])->name('orders.confirm-payment');
    Route::post('/orders/{id}/update-payment', [AdminOrderController::class, 'updatePayment'])->name('orders.update-payment');
    Route::delete('/orders/{id}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');

    // 2. Craftsmen CRUD (الحرفيين)
    Route::get('/craftsmen', [AdminCraftsmanController::class, 'index'])->name('craftsmen.index');
    Route::post('/craftsmen', [AdminCraftsmanController::class, 'store'])->name('craftsmen.store');
    Route::get('/craftsmen/{id}', [AdminCraftsmanController::class, 'show'])->name('craftsmen.show');
    Route::post('/craftsmen/{id}', [AdminCraftsmanController::class, 'update'])->name('craftsmen.update');
    Route::post('/craftsmen/{id}/verify', [AdminCraftsmanController::class, 'updateVerification'])->name('craftsmen.verify');
    Route::post('/craftsmen/{id}/block', [AdminCraftsmanController::class, 'toggleBlock'])->name('craftsmen.block');
    Route::delete('/craftsmen/{id}', [AdminCraftsmanController::class, 'destroy'])->name('craftsmen.destroy');

    // 3. Categories & Services CRUD (الأقسام والخدمات)
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/categories/{id}/services', [AdminCategoryController::class, 'services'])->name('categories.services');
    Route::post('/categories/{id}/services', [AdminCategoryController::class, 'storeService'])->name('categories.services.store');
    Route::post('/categories/services/{id}', [AdminCategoryController::class, 'updateService'])->name('categories.services.update');
    Route::delete('/categories/services/{id}', [AdminCategoryController::class, 'destroyService'])->name('categories.services.destroy');

    // 3.1 Stores & Partners CRUD (خدمات ومتاجر الشركاء)
    Route::get('/stores', [AdminStoreServiceController::class, 'index'])->name('stores.index');
    Route::post('/stores', [AdminStoreServiceController::class, 'store'])->name('stores.store');
    Route::post('/stores/{id}', [AdminStoreServiceController::class, 'update'])->name('stores.update');
    Route::post('/stores/{id}/toggle', [AdminStoreServiceController::class, 'toggleStatus'])->name('stores.toggle');
    Route::delete('/stores/{id}', [AdminStoreServiceController::class, 'destroy'])->name('stores.destroy');

    // 4. Clients CRUD (العملاء)
    Route::get('/clients', [AdminFinancialController::class, 'clients'])->name('clients.index');
    Route::get('/clients/{id}', [AdminFinancialController::class, 'showClient'])->name('clients.show');
    Route::post('/clients', [AdminFinancialController::class, 'storeClient'])->name('clients.store');
    Route::post('/clients/{id}', [AdminFinancialController::class, 'updateClient'])->name('clients.update');
    Route::post('/clients/{id}/block', [AdminFinancialController::class, 'toggleBlockClient'])->name('clients.block');
    Route::delete('/clients/{id}', [AdminFinancialController::class, 'destroyClient'])->name('clients.destroy');

    // 5. Locations CRUD (المناطق، المحافظات، المديريات، الأحياء)
    Route::get('/locations', [AdminLocationController::class, 'index'])->name('locations.index');
    Route::post('/locations/governorates', [AdminLocationController::class, 'storeGov'])->name('locations.gov.store');
    Route::post('/locations/governorates/{id}', [AdminLocationController::class, 'updateGov'])->name('locations.gov.update');
    Route::delete('/locations/governorates/{id}', [AdminLocationController::class, 'destroyGov'])->name('locations.gov.destroy');
    Route::post('/locations/districts', [AdminLocationController::class, 'storeDist'])->name('locations.dist.store');
    Route::post('/locations/districts/{id}', [AdminLocationController::class, 'updateDist'])->name('locations.dist.update');
    Route::delete('/locations/districts/{id}', [AdminLocationController::class, 'destroyDist'])->name('locations.dist.destroy');
    Route::post('/locations/neighborhoods', [AdminLocationController::class, 'storeNeighborhood'])->name('locations.neigh.store');
    Route::post('/locations/neighborhoods/{id}', [AdminLocationController::class, 'updateNeighborhood'])->name('locations.neigh.update');
    Route::delete('/locations/neighborhoods/{id}', [AdminLocationController::class, 'destroyNeighborhood'])->name('locations.neigh.destroy');

    // 6. Advertisements & Offers CRUD (الإعلانات والعروض)
    Route::get('/advertisements', [AdminAdvertisementController::class, 'index'])->name('advertisements.index');
    Route::post('/advertisements', [AdminAdvertisementController::class, 'storeAd'])->name('advertisements.store');
    Route::post('/advertisements/{id}', [AdminAdvertisementController::class, 'updateAd'])->name('advertisements.update');
    Route::delete('/advertisements/{id}', [AdminAdvertisementController::class, 'destroyAd'])->name('advertisements.destroy');
    Route::post('/advertisements/offers/create', [AdminAdvertisementController::class, 'storeOffer'])->name('advertisements.offers.store');
    Route::post('/advertisements/offers/{id}', [AdminAdvertisementController::class, 'updateOffer'])->name('advertisements.offers.update');
    Route::delete('/advertisements/offers/{id}', [AdminAdvertisementController::class, 'destroyOffer'])->name('advertisements.offers.destroy');

    // 7. Financials & Payment Methods CRUD (المالية ووسائل الدفع)
    Route::get('/financials/payouts', [AdminFinancialController::class, 'payouts'])->name('financials.payouts');
    Route::get('/financials/payout-requests', [AdminFinancialController::class, 'payoutRequests'])->name('financials.payout-requests');
    Route::post('/financials/payout-requests/{id}/approve', [AdminFinancialController::class, 'approvePayout'])->name('financials.payout-requests.approve');
    Route::post('/financials/payout-requests/{id}/reject', [AdminFinancialController::class, 'rejectPayout'])->name('financials.payout-requests.reject');
    Route::post('/financials/transactions', [AdminFinancialController::class, 'storeTransaction'])->name('financials.transactions.store');
    Route::delete('/financials/transactions/{id}', [AdminFinancialController::class, 'destroyTransaction'])->name('financials.transactions.destroy');
    Route::post('/financials/payouts/{id}/process', [AdminFinancialController::class, 'processPayout'])->name('financials.payouts.process');
    Route::post('/financials/payment-methods', [AdminFinancialController::class, 'storePaymentMethod'])->name('payment-methods.store');
    Route::post('/financials/payment-methods/{id}', [AdminFinancialController::class, 'updatePaymentMethod'])->name('payment-methods.update');
    Route::delete('/financials/payment-methods/{id}', [AdminFinancialController::class, 'destroyPaymentMethod'])->name('payment-methods.destroy');

    // 7.1 Coupons (كوبونات الخصم)
    Route::get('/coupons', [AdminFinancialController::class, 'coupons'])->name('coupons.index');
    Route::post('/coupons', [AdminFinancialController::class, 'storeCoupon'])->name('coupons.store');
    Route::post('/coupons/{id}', [AdminFinancialController::class, 'updateCoupon'])->name('coupons.update');
    Route::post('/coupons/{id}/toggle', [AdminFinancialController::class, 'toggleCoupon'])->name('coupons.toggle');
    Route::delete('/coupons/{id}', [AdminFinancialController::class, 'destroyCoupon'])->name('coupons.destroy');

    // 8. Reviews & Disputes (التقييمات والنزاعات)
    Route::get('/reviews', [AdminFinancialController::class, 'reviews'])->name('reviews.index');
    Route::post('/reviews/{id}/status', [AdminFinancialController::class, 'updateReviewStatus'])->name('reviews.status');
    Route::delete('/reviews/{id}', [AdminFinancialController::class, 'destroyReview'])->name('reviews.destroy');
    Route::get('/disputes', [AdminFinancialController::class, 'disputes'])->name('disputes.index');
    Route::post('/disputes/{id}/resolve', [AdminFinancialController::class, 'resolveDispute'])->name('disputes.resolve');

    // 9. Support Tickets (الدعم الفني والشكاوى)
    Route::get('/tickets', [AdminFinancialController::class, 'tickets'])->name('tickets.index');
    Route::post('/tickets', [AdminFinancialController::class, 'storeTicket'])->name('tickets.store');
    Route::get('/tickets/{id}', [AdminFinancialController::class, 'showTicket'])->name('tickets.show');
    Route::post('/tickets/{id}/reply', [AdminFinancialController::class, 'replyTicket'])->name('tickets.reply');
    Route::delete('/tickets/{id}', [AdminFinancialController::class, 'destroyTicket'])->name('tickets.destroy');

    // 10. System Settings CRUD (الإعدادات العامة)
    Route::get('/settings', [AdminFinancialController::class, 'settings'])->name('settings.index');
    Route::post('/settings', [AdminFinancialController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/custom', [AdminFinancialController::class, 'storeSetting'])->name('settings.store');
    Route::delete('/settings/{id}', [AdminFinancialController::class, 'destroySetting'])->name('settings.destroy');

    Route::prefix('accounting')->name('accounting.')->controller(AccountingController::class)->group(function () {
        Route::get('/currencies', 'currencies')->name('currencies');
        Route::post('/currencies', 'storeCurrency')->name('currencies.store');
        Route::post('/currencies/{id}', 'updateCurrency')->name('currencies.update');
        Route::post('/currencies/{id}/default', 'setDefaultCurrency')->name('currencies.default');
        Route::delete('/currencies/{id}', 'destroyCurrency')->name('currencies.destroy');
        Route::get('/periods', 'periods')->name('periods');
        Route::post('/periods', 'storePeriod')->name('periods.store');
        Route::post('/periods/{id}/status', 'updatePeriodStatus')->name('periods.status');
        Route::delete('/periods/{id}', 'destroyPeriod')->name('periods.destroy');
        Route::get('/accounts', 'accounts')->name('accounts');
        Route::post('/accounts', 'storeAccount')->name('accounts.store');
        Route::post('/accounts/{id}', 'updateAccount')->name('accounts.update');
        Route::delete('/accounts/{id}', 'destroyAccount')->name('accounts.destroy');
        Route::get('/journal', 'journal')->name('journal');
        Route::post('/journal', 'storeJournalEntry')->name('journal.store');
        Route::post('/journal/{id}/reverse', 'reverseJournalEntry')->name('journal.reverse');
        Route::delete('/journal/{id}', 'destroyJournalEntry')->name('journal.destroy');
        Route::get('/earnings', 'earnings')->name('earnings');
        Route::post('/earnings/{id}/approve', 'approveEarning')->name('earnings.approve');
        Route::get('/collections', 'collections')->name('collections');
        Route::post('/collections/{id}/verify', 'verifyCollection')->name('collections.verify');
        Route::get('/settlements', 'settlements')->name('settlements');
        Route::post('/settlements/{id}/approve', 'approveSettlement')->name('settlements.approve');
        Route::post('/remittances', 'storeRemittance')->name('remittances.store');
        Route::post('/remittances/{id}/confirm', 'confirmRemittance')->name('remittances.confirm');
        Route::get('/payouts', 'payouts')->name('payouts');
        Route::post('/payouts', 'storePayoutDirect')->name('payouts.store');
        Route::post('/payouts/{id}/process', 'processPayout')->name('payouts.process');
        Route::get('/reports', 'reports')->name('reports');
    });

    Route::prefix('suppliers')->name('suppliers.')->controller(SupplierController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/invoices', 'invoices')->name('invoices');
        Route::post('/invoices', 'storeInvoice')->name('invoices.store');
        Route::post('/invoices/{id}/status', 'updateInvoiceStatus')->name('invoices.status');
        Route::delete('/invoices/{id}', 'destroyInvoice')->name('invoices.destroy');
        Route::get('/payments', 'payments')->name('payments');
        Route::post('/payments', 'storePayment')->name('payments.store');
        Route::post('/payments/{id}/confirm', 'confirmPayment')->name('payments.confirm');
        Route::delete('/payments/{id}', 'destroyPayment')->name('payments.destroy');
        Route::get('/returns', 'returns')->name('returns');
        Route::post('/returns', 'storeReturn')->name('returns.store');
        Route::delete('/returns/{id}', 'destroyReturn')->name('returns.destroy');
        Route::post('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'send')->name('send');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::prefix('employees')->name('employees.')->controller(EmployeeController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'storeEmployee')->name('store');
        Route::get('/{id}', 'showEmployee')->name('show');
        Route::post('/{id}', 'updateEmployee')->name('update');
        Route::post('/{id}/permissions', 'updateEmployeePermissions')->name('permissions');
        Route::post('/{id}/financial-transaction', 'storeFinancialTransaction')->name('financial');
        Route::delete('/{id}', 'destroyEmployee')->name('destroy');
        Route::post('/departments', 'storeDepartment')->name('departments.store');
        Route::post('/departments/{id}', 'updateDepartment')->name('departments.update');
        Route::delete('/departments/{id}', 'destroyDepartment')->name('departments.destroy');
    });
});
