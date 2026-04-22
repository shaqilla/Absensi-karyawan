<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Tambahkan Trust Proxies agar Laravel percaya pada koneksi Ngrok
        $middleware->trustProxies(at: '*');

        // 2. Tambahkan pengecualian CSRF untuk route absen
        $middleware->validateCsrfTokens(except: [
            'karyawan/absen/store',
        ]);

        // 3. DAFTARKAN ALIAS ROLE MIDDLEWARE DI SINI (JANTUNG RBAC)
        $middleware->alias([
            'checkRole' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
