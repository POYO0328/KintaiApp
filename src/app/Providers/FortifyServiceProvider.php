<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\LoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {Fortify::ignoreRoutes();
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });

        // Fortify::loginView(function () {
        //     return view('auth.login');
        // });
        Fortify::loginView(function () {
        // URLが /admin/login のときだけ admin用ログインビューを返す
        return request()->is('admin/login')
            ? view('auth.admin-login') // 管理者用のビュー
            : view('auth.login');      // 通常のログインビュー
        });

        // RateLimiter::for('login', function (Request $request) {
        //     $email = (string) $request->email;

        //     return Limit::perMinute(10)->by($email . $request->ip());
        // });

        // Fortify::authenticateUsing(function (Request $request) {
        //     $user = Auth::attempt([
        //         'email' => $request->email,
        //         'password' => $request->password,
        //     ]);

        //     if (! $user) {
        //         return null;
        //     }

        //     $user = Auth::user();

        //     return $user;
        // });

    }
}
