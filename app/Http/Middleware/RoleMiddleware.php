<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // أضف هذا السطر


class RoleMiddleware
{
   public function handle($request, Closure $next, ...$roles)
{
    if (!Auth::check()) {
        return redirect('login');
    }

    $userRole = Auth::user()->role->name;

    // التحقق إذا كان دور المستخدم موجوداً ضمن الأدوار المسموحة للمسار
    if (in_array($userRole, $roles)) {
        return $next($request);
    }

    abort(403, 'غير مصرح لك بالدخول لهذه الصفحة');
}
}
