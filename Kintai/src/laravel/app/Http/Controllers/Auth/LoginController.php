<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * ログイン後のリダイレクト先
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * ログイン処理（エラーメッセージカスタマイズ）
     */
    public function login(LoginRequest $request)
    {
        // LoginRequestでバリデーション実行済み
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended($this->redirectTo);
        }

        // 認証失敗時
        return back()->withErrors([
            'login' => 'ログイン情報が登録されていません',
        ])->withInput($request->only('email'));
    }

    /**
     * ログアウト後のリダイレクト先
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function loggedOut(Request $request)
    {
        return redirect('/login');
    }

    protected function authenticated(Request $request, $user)
    {
        // メール未認証 → 従来通り
        if (! $user->hasVerifiedEmail()) {
            return;
        }

        // admin は除外
        if ($user->role === 'admin') {
            return;
        }

        // 初回ログイン（2FA未完了）
        if ($user->two_factor_confirmed_at === null) {

            // 2FAコード生成＆送信
            $user->sendTwoFactorCode();

            // 2FA待ちフラグ
            session(['two_factor_pending' => true]);

            // ★ 既存の verify.blade.php を表示
            return redirect()->route('verification.notice');
        }
    }
}
