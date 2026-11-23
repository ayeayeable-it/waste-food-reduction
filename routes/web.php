<?php

use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ListingController as AdminListingController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Buyer\DashboardController as BuyerDashboardController;
use App\Http\Controllers\Buyer\DiscoveryController as BuyerDiscoveryController;
use App\Http\Controllers\Buyer\ReservationController as BuyerReservationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\ListingController as SellerListingController;
use App\Http\Controllers\Seller\ReportController as SellerReportController;
use App\Http\Controllers\Seller\ReservationController as SellerReservationController;
use App\Http\Controllers\Seller\StoreController as SellerStoreController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.attempt');

    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

// Authenticated Routes (All Active Users)
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // In-App Notifications (All Roles)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Buyer Module
    Route::prefix('buyer')
        ->name('buyer.')
        ->middleware('role:buyer')
        ->group(function () {
            Route::get('/dashboard', [BuyerDashboardController::class, 'index'])->name('dashboard');
            Route::get('/discover', [BuyerDiscoveryController::class, 'index'])->name('discover');
            Route::get('/map', [BuyerDiscoveryController::class, 'map'])->name('map');
            Route::get('/listings/{listing}', [BuyerDiscoveryController::class, 'show'])->name('listings.show');

            Route::post('/listings/{listing}/reserve', [BuyerReservationController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('reservations.store');
            Route::get('/reservations', [BuyerReservationController::class, 'index'])->name('reservations.index');
            Route::get('/reservations/{reservation}', [BuyerReservationController::class, 'show'])->name('reservations.show');
            Route::patch('/reservations/{reservation}/cancel', [BuyerReservationController::class, 'cancel'])->name('reservations.cancel');
        });

    // Seller Module
    Route::prefix('seller')
        ->name('seller.')
        ->middleware('role:seller')
        ->group(function () {
            Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');

            Route::get('/stores', [SellerStoreController::class, 'index'])->name('stores.index');
            Route::get('/stores/create', [SellerStoreController::class, 'create'])->name('stores.create');
            Route::post('/stores', [SellerStoreController::class, 'store'])->name('stores.store');
            Route::get('/stores/{store}/edit', [SellerStoreController::class, 'edit'])->name('stores.edit');
            Route::put('/stores/{store}', [SellerStoreController::class, 'update'])->name('stores.update');

            Route::get('/listings', [SellerListingController::class, 'index'])->name('listings.index');
            Route::get('/listings/create', [SellerListingController::class, 'create'])->name('listings.create');
            Route::post('/listings', [SellerListingController::class, 'store'])->name('listings.store');
            Route::get('/listings/{listing}/edit', [SellerListingController::class, 'edit'])->name('listings.edit');
            Route::put('/listings/{listing}', [SellerListingController::class, 'update'])->name('listings.update');
            Route::patch('/listings/{listing}/close', [SellerListingController::class, 'close'])->name('listings.close');

            Route::get('/reservations', [SellerReservationController::class, 'index'])->name('reservations.index');
            Route::post('/reservations/confirm', [SellerReservationController::class, 'confirmCollection'])->name('reservations.confirm');

            Route::get('/reports', [SellerReportController::class, 'index'])->name('reports.index');
        });

    // Administrator Module
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');

            Route::get('/stores', [AdminStoreController::class, 'index'])->name('stores.index');
            Route::patch('/stores/{store}/toggle-status', [AdminStoreController::class, 'toggleStatus'])->name('stores.toggle-status');

            Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

            Route::get('/listings', [AdminListingController::class, 'index'])->name('listings.index');
            Route::patch('/listings/{listing}/close', [AdminListingController::class, 'close'])->name('listings.close');

            Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');

            Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
            Route::post('/notifications', [AdminNotificationController::class, 'store'])->name('notifications.store');

            Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');

            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export', [AdminReportController::class, 'exportCsv'])->name('reports.export');
        });
});
