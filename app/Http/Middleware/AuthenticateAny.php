<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateAny
{
    public function handle($request, Closure $next)
    {
        if (
            Auth::check() ||        // 一般ユーザー
            Auth::guard('admin')->check() // 管理者
        ) {
            return $next($request);
        }

        return redirect()->route('login');
    }
}
