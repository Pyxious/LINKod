<?php

use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\RequestController;
use App\Http\Controllers\Client\EvaluationController;
use App\Http\Controllers\Client\NotificationController;
use App\Http\Controllers\Client\BomController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', '2fa', 'role:client'])->prefix('client')->name('client.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/requests',             [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/track',       [RequestController::class, 'track'])->name('requests.track');
    Route::get('/requests/create',      [RequestController::class, 'create'])->name('requests.create');
    Route::post('/requests',            [RequestController::class, 'store'])->name('requests.store')->middleware('throttle:10,1');
    Route::get('/requests/{id}',        [RequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{id}/messages', [\App\Http\Controllers\RequestMessageController::class, 'store'])->name('requests.messages.store')->middleware('throttle:15,1');
    Route::post('/requests/{id}/cancel', [RequestController::class, 'cancel'])->name('requests.cancel')->middleware('throttle:10,1');

    Route::get('/requests/{id}/evaluate',  [EvaluationController::class, 'create'])->name('evaluations.create');
    Route::post('/requests/{id}/evaluate', [EvaluationController::class, 'store'])->name('evaluations.store')->middleware('throttle:10,1');

    Route::get('/projects/{id}/bom', [BomController::class, 'show'])->name('bom.show');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'readNotification'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Dedicated Messages Portal
    Route::get('/messages/{requestId?}', [\App\Http\Controllers\MessagePortalController::class, 'index'])->name('messages.index');
});
