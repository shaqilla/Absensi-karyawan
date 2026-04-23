<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Ambil role user, pastikan tidak null, kecilkan semua huruf & hapus spasi
        $userRole = strtolower(trim(Auth::user()->role ?? ''));

        // 3. Bersihkan daftar role yang dikirim dari web.php
        $allowedRoles = array_map(function ($role) {
            return strtolower(trim($role));
        }, $roles);

        // 4. LOGIKA PENGECEKAN
        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        // 5. TENDANG JIKA GAK PUNYA AKSES
        abort(403, 'MAAF, ROLE ' . strtoupper($userRole) . ' TIDAK DIIZINKAN MENGAKSES HALAMAN INI.');
    }
}
