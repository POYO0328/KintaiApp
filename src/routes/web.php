<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseAddressController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\RegisteredUserController;



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


Route::get('/', [AuthController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::post('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');

    Route::get('/sell', [SellController::class, 'showForm'])->name('sell.form');

    Route::get('/sell', [SellController::class, 'showForm'])->name('sell.form');

    Route::get('/purchase/address/{item_id}', [PurchaseAddressController::class, 'showForm'])->name('purchase.address');

    Route::post('/purchase/address/{item_id}', [PurchaseAddressController::class, 'submitAddress'])->name('purchase.address.submit');

    // 保存処理用ルート
    Route::post('/sell', [ItemController::class, 'store'])->middleware('auth')->name('item.store');

    Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase.show');
});

Route::get('/item/{id}', [ItemController::class, 'show'])->name('item.show');

Route::post('/purchase/complete/{item_id}', [PurchaseController::class, 'complete'])->name('purchase.complete');

//コメント部分
Route::post('/items/{item}/comments', [CommentController::class, 'store'])->name('comment.store');

Route::post('/like-toggle/{item_id}', [LikeController::class, 'toggle'])->name('like.toggle');

Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

Route::post('/register', [RegisterController::class, 'store']);

Route::post('/stripe/create-checkout-session', [\App\Http\Controllers\StripeController::class, 'create']);

Route::get('/', [ItemController::class, 'index']);