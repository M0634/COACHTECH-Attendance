<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    /**
     * 管理者ログイン画面
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(Request $request)
    {
        $request->validate(
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'min:8'],
            ],
            [
                'email.required' => 'メールアドレスを入力してください',
                'password.required' => 'パスワードを入力してください',
                'password.min' => 'パスワードは8文字以上で入力してください',
            ]
        );

        if (Auth::attempt($request->only('email', 'password'))) {

            if (Auth::user()->role !== 'admin') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'メールアドレスまたはパスワードが正しくありません。',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->route('admin.attendance.list');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }


    /**
     * ログアウト
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
