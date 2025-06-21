<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\ThemeController;
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

// Themes - Students can view but not manage
Route::middleware('auth')->group(function () {
    Route::get('themes/{theme}', [ThemeController::class, 'show'])->name('themes.show');
});
Route::resource('themes', ThemeController::class)->except(['show'])->middleware(['auth', 'role:admin,coach']);

// Courses
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('courses/approval-queue', [CourseController::class, 'approvalQueue'])->name('courses.approval-queue');
    Route::post('courses/{course}/approve', [CourseController::class, 'approve'])->name('courses.approve');
});

Route::resource('courses', CourseController::class)->middleware(['auth', 'role:admin,coach']);
