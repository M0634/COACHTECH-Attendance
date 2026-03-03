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
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ログアウトのレスポンス差し替え
        $this->app->singleton(LogoutResponse::class, CustomLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
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
        | 認証画面のView指定（Blade）
        |--------------------------------------------------------------------------
        */
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));

        // ✅ メール認証（/email/verify）で落ちてるのはこれが無いのが原因
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));

        /*
        |--------------------------------------------------------------------------
        | パスワードリセット系（Blade）
        |--------------------------------------------------------------------------
        */

        // 「メールアドレスを入れてリセットリンク送る」画面（/forgot-password）
        // もしあなたが reset.blade.php をここにも使うなら auth.reset を返す
        Fortify::requestPasswordResetLinkView(fn () => view('auth.reset'));

        // 「新しいパスワードを設定する」画面（/reset-password/{token}）
        // 同じく reset.blade.php を使う
        Fortify::resetPasswordView(function (Request $request) {
            return view('auth.reset', [
                'request' => $request,
                'token'   => $request->route('token'),
                'email'   => $request->email,
            ]);
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