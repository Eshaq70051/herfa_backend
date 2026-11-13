<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminCraftsmanController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminFinancialController;
use App\Http\Controllers\Admin\AdminLocationController;
use App\Http\Controllers\Admin\AdminAdvertisementController;
use App\Http\Controllers\Admin\AdminStoreServiceController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Fallback login route for Laravel default auth redirect
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Admin Auth Routes
Route::get('/admin/login', [AdminDashboardController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminDashboardController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminDashboardController::class, 'logout'])->name('admin.logout');

// Protected Admin Routes
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    // 1. Orders CRUD (الطلبات)
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [AdminOrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{id}/assign', [AdminOrderController::class, 'assignCraftsman'])->name('orders.assign');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}', [AdminOrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
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
    Route::post('/clients', [AdminFinancialController::class, 'storeClient'])->name('clients.store');
    Route::post('/clients/{id}', [AdminFinancialController::class, 'updateClient'])->name('clients.update');
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
    Route::post('/financials/transactions', [AdminFinancialController::class, 'storeTransaction'])->name('financials.transactions.store');
    Route::delete('/financials/transactions/{id}', [AdminFinancialController::class, 'destroyTransaction'])->name('financials.transactions.destroy');
    Route::post('/financials/payouts/{id}/process', [AdminFinancialController::class, 'processPayout'])->name('financials.payouts.process');
    Route::post('/financials/payment-methods', [AdminFinancialController::class, 'storePaymentMethod'])->name('payment-methods.store');
    Route::post('/financials/payment-methods/{id}', [AdminFinancialController::class, 'updatePaymentMethod'])->name('payment-methods.update');
    Route::delete('/financials/payment-methods/{id}', [AdminFinancialController::class, 'destroyPaymentMethod'])->name('payment-methods.destroy');

    // 8. Reviews (التقييمات)
    Route::get('/reviews', [AdminFinancialController::class, 'reviews'])->name('reviews.index');
    Route::post('/reviews/{id}/status', [AdminFinancialController::class, 'updateReviewStatus'])->name('reviews.status');
    Route::delete('/reviews/{id}', [AdminFinancialController::class, 'destroyReview'])->name('reviews.destroy');

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
});
