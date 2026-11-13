<?php

namespace App\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            // guest() ghi nhớ trang đang định vào để quay lại sau khi đăng nhập
            return redirect()->guest(route('admin.login'));
        }

        // Tài khoản bị khóa khi đang đăng nhập thì đăng xuất ngay
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Tài khoản đã bị khóa.']);
        }

        return $next($request);
    }
}