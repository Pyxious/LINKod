<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

// ── Root ────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/faq', function () {
    return view('faq');
})->name('faq');

// ── Auth ─────────────────────────────────────────────────────────────
Route::get('/login', fn() => view('auth.login'))->name('login');

// Legacy login redirects
Route::get('/admin/login', fn() => redirect()->route('login'))->name('admin.login');
Route::get('/admin', fn() => redirect()->route('login'));
Route::get('/staffs/login', fn() => redirect()->route('login'))->name('staff.login');
Route::get('/staff', fn() => redirect()->route('login'));

// Google SSO (with Rate Limiting)
Route::get('/admin/auth/google',    fn() => redirect()->route('google.redirect'))->name('admin.google.redirect');
Route::get('/staff/auth/google',    fn() => redirect()->route('google.redirect'))->name('staff.google.redirect');
Route::get('/auth/google',          [GoogleAuthController::class, 'redirect'])->name('google.redirect')->middleware('throttle:10,1');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback')->middleware('throttle:6,1');
Route::post('/logout',              [GoogleAuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/portal-select', fn() => view('portal-select'))->name('portal.select');

    Route::get('/2fa', [\App\Http\Controllers\Auth\TwoFactorController::class, 'index'])->name('2fa.index');
    Route::post('/2fa', [\App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->name('2fa.verify')->middleware('throttle:6,1');
    
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/2fa/initiate', [\App\Http\Controllers\ProfileController::class, 'initiate2fa'])->name('profile.2fa.initiate');
    Route::post('/profile/2fa/enable', [\App\Http\Controllers\ProfileController::class, 'enable2fa'])->name('profile.2fa.enable')->middleware('throttle:6,1');
    Route::post('/profile/2fa/disable', [\App\Http\Controllers\ProfileController::class, 'disable2fa'])->name('profile.2fa.disable')->middleware('throttle:6,1');
});
// ── Storage Asset Serving (Works seamlessly on Laravel Cloud / Docker without symlink) ──
Route::get('/storage/{path}', function (string $path) {
    // 1. Check in public storage disk
    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
        return \Illuminate\Support\Facades\Storage::disk('public')->response($path);
    }
    // 2. Check in local storage disk
    if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
        return \Illuminate\Support\Facades\Storage::disk('local')->response($path);
    }
    // 3. Direct filesystem check in storage/app/public
    $publicPath = storage_path('app/public/' . $path);
    if (file_exists($publicPath)) {
        return response()->file($publicPath);
    }
    // 4. Direct filesystem check in storage/app
    $directPath = storage_path('app/' . $path);
    if (file_exists($directPath)) {
        return response()->file($directPath);
    }
    abort(404);
})->where('path', '.*')->name('storage.serve');

// ── Unauthorized ─────────────────────────────────────────────────────
Route::get('/unauthorized', fn() => view('errors.unauthorized'))->name('unauthorized');

// ── Role Route Files ─────────────────────────────────────────────────
require __DIR__ . '/client.php';
require __DIR__ . '/worker.php';
require __DIR__ . '/admin.php';
