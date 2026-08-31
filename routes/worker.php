<?php

use App\Http\Controllers\Worker\DashboardController;
use App\Http\Controllers\Worker\JobOrderController;
use App\Http\Controllers\Worker\TaskProgressController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', '2fa', 'role:worker'])->prefix('worker')->name('worker.')->group(function () {

    // Redirect /worker and /worker/ to dashboard
    Route::redirect('/', '/worker/dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/job-orders',       [JobOrderController::class, 'index'])->name('job-orders.index');
    Route::get('/job-orders/{id}',  [JobOrderController::class, 'show'])->name('job-orders.show');
    Route::post('/job-orders/{id}/messages', [\App\Http\Controllers\RequestMessageController::class, 'store'])->name('job-orders.messages.store')->middleware('throttle:15,1');
    Route::post('/job-orders/{id}/messages/mark-read', [\App\Http\Controllers\RequestMessageController::class, 'markAsRead'])->name('job-orders.messages.mark-read');

    Route::post('/job-orders/{id}/bom',   [\App\Http\Controllers\Worker\BomController::class, 'store'])->name('bom.store')->middleware('throttle:10,1');

    Route::put('/job-orders/{id}/progress', [TaskProgressController::class, 'update'])->name('task-progress.update')->middleware('throttle:10,1');
    Route::post('/job-orders/{id}/sync-progress', [TaskProgressController::class, 'syncProgress'])->name('task-progress.sync');

    Route::get('/units', [\App\Http\Controllers\Worker\UnitController::class, 'index'])->name('units.index');
    Route::get('/notifications', [\App\Http\Controllers\Worker\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [\App\Http\Controllers\Worker\NotificationController::class, 'readNotification'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Worker\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Dedicated Messages Portal
    Route::get('/messages/{requestId?}', [\App\Http\Controllers\MessagePortalController::class, 'index'])->name('messages.index');

    // Dedicated Worker Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
});
