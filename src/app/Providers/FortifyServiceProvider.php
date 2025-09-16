<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Fortify 標準ルートを無効化
        Fortify::ignoreRoutes();

        // ユーザー作成処理をカスタム
        Fortify::createUsersUsing(CreateNewUser::class);

        // 会員登録画面
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // ログイン画面（管理者用と通常用の切り替え）
        Fortify::loginView(function () {
            return request()->is('admin/login')
                ? view('auth.admin-login') // 管理者用ビュー
                : view('auth.login');      // 通常ログインビュー
        });

        // メール認証画面
        Fortify::verifyEmailView(function () {
            return view('auth.verify');
        });

        // カスタム RegisterResponse をバインド
        $this->app->singleton(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            \App\Actions\Fortify\CustomRegisterResponse::class
        );
    }
}
