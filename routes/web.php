<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::get('auth/{provider}', [App\Http\Controllers\Auth\SocialiteLoginController::class, 'redirectToProvider'])->name('oauth.redirect');
Route::get('auth/{provider}/callback', [App\Http\Controllers\Auth\SocialiteLoginController::class, 'handleProviderCallback'])->name('oauth.callback');
