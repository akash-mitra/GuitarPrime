<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Authentication Routes
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Socialite Authentication
Route::get('auth/{provider}', [App\Http\Controllers\Auth\SocialiteLoginController::class, 'redirectToProvider'])->name('oauth.redirect');
Route::get('auth/{provider}/callback', [App\Http\Controllers\Auth\SocialiteLoginController::class, 'handleProviderCallback'])->name('oauth.callback');

// Theme management - Admin only (specific routes first)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('themes/create', [ThemeController::class, 'create'])->name('themes.create');
    Route::post('themes', [ThemeController::class, 'store'])->name('themes.store');
    Route::get('themes/{theme}/edit', [ThemeController::class, 'edit'])->name('themes.edit');
    Route::put('themes/{theme}', [ThemeController::class, 'update'])->name('themes.update');
    Route::patch('themes/{theme}', [ThemeController::class, 'update']);
    Route::delete('themes/{theme}', [ThemeController::class, 'destroy'])->name('themes.destroy');
});

// Theme browsing - All authenticated users can view themes (parameterized routes last)
Route::middleware('auth')->group(function () {
    Route::get('themes', [ThemeController::class, 'index'])->name('themes.index');
    Route::get('themes/{theme}', [ThemeController::class, 'show'])->name('themes.show');
});

// Courses - Admin only routes (most specific first)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('courses/approval-queue', [CourseController::class, 'approvalQueue'])->name('courses.approval-queue');
    Route::post('courses/{course}/approve', [CourseController::class, 'approve'])->name('courses.approve');
});

// Course management - Admin and coach only (specific routes before parameterized)
Route::middleware(['auth', 'role:admin,coach'])->group(function () {
    Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::patch('courses/{course}', [CourseController::class, 'update']);
    Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
});

// Course browsing - All authenticated users can view courses (parameterized routes last)
Route::middleware('auth')->group(function () {
    Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('courses/{course}/modules/{module}', [ModuleController::class, 'showInCourse'])->name('courses.modules.show');
});

// Module management - Admin and coach only (specific routes first)
Route::middleware(['auth', 'role:admin,coach'])->group(function () {
    Route::get('modules/create', [ModuleController::class, 'create'])->name('modules.create');
    Route::post('modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');
    Route::post('modules', [ModuleController::class, 'store'])->name('modules.store');
    Route::get('modules/{module}/edit', [ModuleController::class, 'edit'])->name('modules.edit');
    Route::put('modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
    Route::patch('modules/{module}', [ModuleController::class, 'update']);
    Route::delete('modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');
});

// Attachment management - Admin and coach only for upload/edit/delete
Route::middleware(['auth', 'role:admin,coach'])->group(function () {
    Route::post('attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::put('attachments/{attachment}', [AttachmentController::class, 'update'])->name('attachments.update');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
});

// Attachment download - All authenticated users (with access control in controller)
Route::middleware('auth')->group(function () {
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
});

// Module browsing - Admin and coach only for direct access
Route::middleware(['auth', 'role:admin,coach'])->group(function () {
    Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::get('modules/{module}', [ModuleController::class, 'show'])->name('modules.show');
});

// Purchase Routes
Route::middleware('auth')->group(function () {
    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('purchases/{purchase}/success', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchase.cancel');
    Route::post('purchases/{purchase}/verify-razorpay', [PurchaseController::class, 'verifyRazorpay'])->name('purchase.verify-razorpay');
});

// Webhook Routes (no auth middleware)
Route::post('webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('webhooks/razorpay', [WebhookController::class, 'razorpay'])->name('webhooks.razorpay');
