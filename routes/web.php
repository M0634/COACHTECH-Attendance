<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;




/*
|--------------------------------------------------------------------------
| 一般ユーザー Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\ApplicationController;

/*
|--------------------------------------------------------------------------
| 管理者 Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminRegisterController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AttendanceFixRequestController;
use App\Http\Controllers\Admin\StaffController;

/*
|--------------------------------------------------------------------------
| 一般ユーザー（auth + verified）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // 当日勤怠
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::post('/attendance/status', [AttendanceController::class, 'updateStatus'])
        ->name('attendance.status');

    Route::post('/attendance/finish', [AttendanceController::class, 'finish'])
        ->name('attendance.finish');

    // 月次勤怠一覧（本体）
    Route::get('/attendance/monthly', [AttendanceController::class, 'monthly'])
        ->name('attendance.monthly');

    // 月次勤怠一覧（エイリアス：ビューが attendance.list を使うため）
    Route::get('/attendance/list', [AttendanceController::class, 'monthly'])
        ->name('attendance.list');

    // 修正申請一覧（ユーザー用）
    Route::get('/attendance/requests', [AttendanceRequestController::class, 'index'])
        ->name('attendance.requests.index');

    // 修正申請（提出）
    Route::put('/attendance/{attendance}/request-fix', [AttendanceController::class, 'requestFix'])
        ->name('attendance.request-fix');

    // 勤怠詳細（ユーザー用）
    Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])
        ->name('attendance.show');

    // 各種申請一覧
    Route::get('/application', [ApplicationController::class, 'index'])
        ->name('application.index');
});

/*
|--------------------------------------------------------------------------
| Admin 認証
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/register', [AdminRegisterController::class, 'create'])
        ->name('register');

    Route::post('/register', [AdminRegisterController::class, 'store'])
        ->name('register.store');

    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AdminLoginController::class, 'login'])
        ->name('login.store');
});

/*
|--------------------------------------------------------------------------
| 管理者専用（auth + admin）
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->name('admin.')
    ->group(function () {

        // 勤怠一覧
        Route::get('attendance/list', [AdminAttendanceController::class, 'list'])
            ->name('attendance.list');

        // ユーザー別 月次勤怠
        Route::get('attendance/{user}/monthly', [AdminAttendanceController::class, 'monthly'])
            ->name('attendance.monthly');

        // 月次CSV出力
        Route::get('attendance/{user}/monthly/csv', [AdminAttendanceController::class, 'exportMonthlyCsv'])
            ->name('attendance.monthly.csv');

        // 勤怠詳細（管理者用）
        Route::get('attendance/{attendance}', [AdminAttendanceController::class, 'show'])
            ->name('attendance.show');

        Route::put('attendance/{attendance}', [AdminAttendanceController::class, 'update'])
            ->name('attendance.update');

        // 修正申請管理
        Route::get('stamp_correction_requests', [AttendanceFixRequestController::class, 'index'])
            ->name('stamp_correction_requests.index');

        Route::get('stamp_correction_requests/{fixRequest}', [AttendanceFixRequestController::class, 'show'])
            ->name('stamp_correction_requests.show');

        Route::post('stamp_correction_requests/{fixRequest}/approve', [AttendanceFixRequestController::class, 'approve'])
            ->name('stamp_correction_requests.approve');

        // スタッフ管理
        Route::get('staff/list', [StaffController::class, 'index'])
            ->name('staff.list');

        Route::post('/logout', [AdminLoginController::class, 'logout'])
            ->name('logout');
    });

