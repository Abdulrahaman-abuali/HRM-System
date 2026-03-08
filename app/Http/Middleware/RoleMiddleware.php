<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // أضف هذا السطر

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // استخدام Auth Facade يزيل الخطوط الحمراء عادةً
        if (!Auth::check() || Auth::user()->role?->name !== $role) {
            abort(403, 'غير مصرح لك بالدخول لهذه الصفحة');
        }

        return $next($request);
    }
}
