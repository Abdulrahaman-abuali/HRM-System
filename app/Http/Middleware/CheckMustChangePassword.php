<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckMustChangePassword
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->must_change_password) {
                $allowedRoutes = ['password.change', 'password.update'];

                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    return redirect()->route('password.change')
                        ->with('warning', 'يجب عليك تغيير كلمة المرور أولاً للمتابعة');
                }
            }
        }

        return $next($request);
    }
}
