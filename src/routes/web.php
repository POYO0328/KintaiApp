<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*--------------------勤怠---------------------*/
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ListController;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;

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

// Route::middleware('auth')->group(function () {
//     Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');

//     Route::post('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');

//     Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');

//     Route::get('/sell', [SellController::class, 'showForm'])->name('sell.form');

//     Route::get('/sell', [SellController::class, 'showForm'])->name('sell.form');

//     Route::get('/purchase/address/{item_id}', [PurchaseAddressController::class, 'showForm'])->name('purchase.address');

//     Route::post('/purchase/address/{item_id}', [PurchaseAddressController::class, 'submitAddress'])->name('purchase.address.submit');

//     // 保存処理用ルート
//     Route::post('/sell', [ItemController::class, 'store'])->middleware('auth')->name('item.store');

//     Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase.show');
// });

// Route::get('/item/{id}', [ItemController::class, 'show'])->name('item.show');

// Route::post('/purchase/complete/{item_id}', [PurchaseController::class, 'complete'])->name('purchase.complete');

// //コメント部分
// Route::post('/items/{item}/comments', [CommentController::class, 'store'])->name('comment.store');

// Route::post('/like-toggle/{item_id}', [LikeController::class, 'toggle'])->name('like.toggle');

// Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

Route::post('/register', [RegisterController::class, 'store']);

// Route::post('/stripe/create-checkout-session', [\App\Http\Controllers\StripeController::class, 'create']);


/*--------------------勤怠---------------------*/

Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    // 勤怠一覧
    Route::get('/attendance/list', [ListController::class, 'index'])->name('attendance.list');

});


Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/attendances', [AttendanceController::class, 'adminList'])->name('admin.attendances.index');
});


Route::view('/admin/login', 'auth.admin-login')->name('admin.login');

// 一般ユーザー詳細
Route::get('/attendance/{date}', [AttendanceController::class, 'detail'])
    ->name('attendance.detail');

// 管理者承認画面
Route::get('/stamp_correction_request/approve/{attendanceCorrection}',
    [AttendanceController::class, 'approve']
)->name('admin.attendance.approve');

// 管理者用 承認/却下処理
Route::put('/stamp_correction_request/approve/{attendanceCorrection}',
    [AttendanceController::class, 'approveUpdate']
)->name('admin.attendance.approveUpdate');

Route::put('/attendance/{date}/update', [AttendanceController::class, 'update'])->name('attendance.update');

Route::get('/stamp_correction_request/list', [AttendanceController::class, 'pendingList'])->name('stamp_correction_request.list');

Route::get('/admin/staff/list', [\App\Http\Controllers\AttendanceController::class, 'staffList'])
    ->name('admin.staff.list');

Route::get('/admin/attendance/staff/{id}', [AttendanceController::class, 'staffMonthly'])
    ->name('admin.staff.attendance');





