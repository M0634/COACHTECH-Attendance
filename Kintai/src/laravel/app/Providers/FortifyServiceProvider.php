<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\LogoutResponse as CustomLogoutResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
// ★ 追加（LogoutResponse 用）
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // ★ ここに追加（最重要）
        $this->app->singleton(
            LogoutResponse::class,
            CustomLogoutResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        /*
        |--------------------------------------------------------------------------
        | Fortify Actions
        |--------------------------------------------------------------------------
        */
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        /*
        |--------------------------------------------------------------------------
        | 認証画面のView指定（Bladeを使う場合）
        |--------------------------------------------------------------------------
        */
        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        /*
        |--------------------------------------------------------------------------
        | Rate Limiter（ログイン制限）
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::lower($request->input(Fortify::username())).'|'.$request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
