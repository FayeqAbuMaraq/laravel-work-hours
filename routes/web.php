<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\WorkDayController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/work-days');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('work-days', WorkDayController::class)
        ->except(['create', 'show', 'edit'])
        ->parameters(['work-days' => 'work_day']);

    // مسارات خاصة: تسجيل دخول/خروج
    Route::post('/attendance/check-in', [WorkDayController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [WorkDayController::class, 'checkOut'])->name('attendance.check-out');

    // الحضور والانصراف + العمل بدون إنترنت (مزامنة JSON)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/token', [AttendanceController::class, 'token'])->name('attendance.token');
    Route::post('/attendance/sync', [AttendanceController::class, 'sync'])->name('attendance.sync');

    Route::resource('withdrawals', WithdrawalController::class)
        ->except(['create', 'show', 'edit']);

    // داشبورد المشرف: كل المستخدمين ورواتبهم (صلاحية Spatie، وغير ذلك 403)
    Route::get('/admin', [AdminDashboardController::class, 'index'])
        ->middleware('can:view-admin-dashboard')
        ->name('admin.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
