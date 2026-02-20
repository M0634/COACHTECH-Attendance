<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminRegisterController extends Controller
{
    /**
     * 管理者登録画面
     */
    public function create()
    {
        return view('admin.register');
    }

    /**
     * 管理者登録処理（メール認証付き）
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 📧 認証メール送信（MailHog）
        event(new Registered($admin));

        // ✅ 管理者ログイン画面へ
        return redirect()
            ->route('admin.login')
            ->with('message', '認証メールを送信しました。認証後にログインしてください。');
    }
}
