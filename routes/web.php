<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

// ── Root ────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ── Auth ─────────────────────────────────────────────────────────────
Route::get('/login', fn() => view('auth.login'))->name('login');

// Legacy login redirects
Route::get('/admin/login', fn() => redirect()->route('login'))->name('admin.login');
Route::get('/admin', fn() => redirect()->route('login'));
Route::get('/staffs/login', fn() => redirect()->route('login'))->name('staff.login');
Route::get('/staff', fn() => redirect()->route('login'));

// Google SSO
Route::get('/admin/auth/google',    fn() => redirect()->route('google.redirect'))->name('admin.google.redirect');
Route::get('/staff/auth/google',    fn() => redirect()->route('google.redirect'))->name('staff.google.redirect');
Route::get('/auth/google',          [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
Route::post('/logout',              [GoogleAuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/portal-select', fn() => view('portal-select'))->name('portal.select');

    Route::get('/2fa', [\App\Http\Controllers\Auth\TwoFactorController::class, 'index'])->name('2fa.index');
    Route::post('/2fa', [\App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->name('2fa.verify');
    
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/2fa/initiate', [\App\Http\Controllers\ProfileController::class, 'initiate2fa'])->name('profile.2fa.initiate');
    Route::post('/profile/2fa/enable', [\App\Http\Controllers\ProfileController::class, 'enable2fa'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/disable', [\App\Http\Controllers\ProfileController::class, 'disable2fa'])->name('profile.2fa.disable');
    
});
// ── Unauthorized ─────────────────────────────────────────────────────
Route::get('/unauthorized', fn() => view('errors.unauthorized'))->name('unauthorized');

// ── Role Route Files ─────────────────────────────────────────────────
require __DIR__ . '/client.php';
require __DIR__ . '/worker.php';
require __DIR__ . '/admin.php';
