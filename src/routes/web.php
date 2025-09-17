<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ListController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/register', [RegisterController::class, 'store']);


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
});
Route::middleware(['auth'])->group(function () {
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    // 勤怠一覧
    Route::get('/attendance/list', [ListController::class, 'index'])->name('attendance.list');

});

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/attendance/list', [AttendanceController::class, 'adminList'])->name('admin.attendances.index');
});

Route::view('/admin/login', 'auth.admin-login')->name('admin.login');

// 一般ユーザー詳細
Route::get('/attendance/{date}', [AttendanceController::class, 'detail'])
    ->name('attendance.detail');

// 管理者承認画面
Route::get('/stamp_correction_request/approve/{attendanceCorrection}',
    [AttendanceController::class, 'approve']
)->name('admin.attendance.approve');

// 管理者用 承認処理
Route::put('/stamp_correction_request/approve/{attendanceCorrection}',
    [AttendanceController::class, 'approveUpdate']
)->name('admin.attendance.approveUpdate');

Route::put('/attendance/{date}/update', [AttendanceController::class, 'update'])->name('attendance.update');

Route::get('/stamp_correction_request/list', [AttendanceController::class, 'pendingList'])->name('stamp_correction_request.list');

Route::get('/admin/staff/list', [\App\Http\Controllers\AttendanceController::class, 'staffList'])
    ->name('admin.staff.list');

Route::get('/admin/attendance/staff/{id}', [AttendanceController::class, 'staffMonthly'])
    ->name('admin.staff.attendance');

//CSV出力
Route::get('/admin/staff/{id}/attendance/csv', [AttendanceController::class, 'exportCsv'])
    ->name('admin.staff.attendance.csv');

// Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
//     ->name('logout');