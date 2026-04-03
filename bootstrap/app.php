<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckMustChangePassword; // ✅ أضف هذا السطر

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'check.must.change.password' => CheckMustChangePassword::class, // ✅ أضف هذا السطر
        ]);

        // تعطيل CSRF لمسارات البصمة
        $middleware->validateCsrfTokens(except: [
            'face-attendance/*',
            'external-attendance',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
