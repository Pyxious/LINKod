<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RequestController;
use App\Http\Controllers\Admin\WorkforceController;
use App\Http\Controllers\Admin\BomController;
use App\Http\Controllers\Admin\MaterialsController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', '2fa', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Users / Role management
    Route::get('/users',           [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}',      [UserController::class, 'update'])->name('users.update');

    // Requests
    Route::get('/requests',              [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create',       [RequestController::class, 'create'])->name('requests.create');
    Route::post('/requests',             [RequestController::class, 'store'])->name('requests.store')->middleware('throttle:10,1');
    Route::get('/requests/{id}',         [RequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{id}/messages', [\App\Http\Controllers\RequestMessageController::class, 'store'])->name('requests.messages.store')->middleware('throttle:15,1');
    Route::get('/requests/{id}/export',  [RequestController::class, 'export'])->name('requests.export');
    Route::get('/requests/{id}/satisfaction', [RequestController::class, 'printSatisfaction'])->name('requests.satisfaction');
    Route::post('/requests/{id}/approve',[RequestController::class, 'approve'])->name('requests.approve')->middleware('throttle:10,1');
    Route::post('/requests/{id}/reject', [RequestController::class, 'reject'])->name('requests.reject')->middleware('throttle:10,1');
    Route::post('/requests/{id}/verify', [RequestController::class, 'verifyCompletion'])->name('requests.verify')->middleware('throttle:10,1');

    // Workforce
    Route::get('/workforce',        [WorkforceController::class, 'index'])->name('workforce.index');
    Route::post('/workforce/assign',[WorkforceController::class, 'assign'])->name('workforce.assign');
    Route::post('/workforce/{worker_id}/make-leader', [WorkforceController::class, 'makeTeamLeader'])->name('workforce.make-leader');
    Route::post('/workforce/{worker_id}/assign-team', [WorkforceController::class, 'assignTeam'])->name('workforce.assign-team');


    // Bill of Materials
    Route::get('/bom',                    [BomController::class, 'index'])->name('bom.index');
    Route::get('/bom/{projectId}/create', [BomController::class, 'create'])->name('bom.create');
    Route::post('/bom/{projectId}',       [BomController::class, 'store'])->name('bom.store');
    Route::get('/bom/{projectId}',        [BomController::class, 'show'])->name('bom.show');
    Route::post('/bom/{projectId}/approve',[BomController::class, 'approve'])->name('bom.approve');

    // Materials catalog
    Route::get('/materials',          [MaterialsController::class, 'index'])->name('materials.index');
    Route::get('/materials/create',   [MaterialsController::class, 'create'])->name('materials.create');
    Route::post('/materials',         [MaterialsController::class, 'store'])->name('materials.store');
    Route::get('/materials/{id}/edit',[MaterialsController::class, 'edit'])->name('materials.edit');
    Route::put('/materials/{id}',     [MaterialsController::class, 'update'])->name('materials.update');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Notifications
    Route::get('/notifications/{id}/read', [DashboardController::class, 'readNotification'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Audit Logs
    Route::get('/audit', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit.index');

    // Dedicated Messages Portal
    Route::get('/messages/{requestId?}', [\App\Http\Controllers\MessagePortalController::class, 'index'])->name('messages.index');

    // Dedicated Admin Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
});
