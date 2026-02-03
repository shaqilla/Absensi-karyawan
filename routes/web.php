<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\QrController;
use App\Http\Controllers\Karyawan\AbsensiController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\PengajuanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Karyawan\KaryawanDashboardController;
use App\Http\Controllers\Karyawan\PengajuanIzinController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\JadwalController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// 1. HALAMAN WELCOME
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// 2. PENGALIHAN DASHBOARD (OTAKNYA DI SINI)
Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user) {
        if ($user->role == 'admin') {
            return \Inertia\Inertia::location(route('admin.dashboard'));
        } 
        
        if ($user->role == 'karyawan') {
            // PAKSA KARYAWAN KELUAR DARI INERTIA KE BLADE
            return \Inertia\Inertia::location(route('karyawan.dashboard'));
        }
    } 
    
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 3. SEMUA ROUTE YANG BUTUH LOGIN
Route::middleware('auth')->group(function () {
    
    // Route Profile Bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- GRUP ADMIN ---
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/qr-scanner', function () { return view('admin.qr_generator'); })->name('admin.qr.view');
        Route::get('/generate-new-token', [QrController::class, 'generate'])->name('admin.qr.generate');

        Route::get('/karyawan', [KaryawanController::class, 'index'])->name('admin.karyawan.index');
        Route::get('/karyawan/create', [KaryawanController::class, 'create'])->name('admin.karyawan.create');
        Route::post('/karyawan/store', [KaryawanController::class, 'store'])->name('admin.karyawan.store');
        Route::get('/karyawan/{id}/edit', [KaryawanController::class, 'edit'])->name('admin.karyawan.edit');
        Route::put('/karyawan/{id}', [KaryawanController::class, 'update'])->name('admin.karyawan.update');
        Route::delete('/karyawan/{id}', [KaryawanController::class, 'destroy'])->name('admin.karyawan.destroy');
        
        // --- PERBAIKAN DI SINI: TAMBAHKAN ROUTE UPDATE ---
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('admin.pengajuan.index');
        Route::patch('/pengajuan/{id}', [PengajuanController::class, 'updateStatus'])->name('admin.pengajuan.update'); 
        
        Route::get('/laporan', [LaporanController::class, 'index'])->name('admin.laporan.index');
        Route::resource('shift', ShiftController::class)->names('admin.shift');
        Route::resource('shift', App\Http\Controllers\Admin\ShiftController::class)->names('admin.shift');
        Route::resource('jadwal', JadwalController::class)->names('admin.jadwal');
    });
    
    // --- GRUP KARYAWAN ---
    Route::prefix('karyawan')->group(function () {
        Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('karyawan.dashboard');
        Route::get('/scan', function () { return view('karyawan.scan'); })->name('karyawan.scan');
        Route::post('/absen/store', [AbsensiController::class, 'store'])->name('karyawan.absen.store');
        Route::get('/izin', [PengajuanIzinController::class, 'create'])->name('karyawan.izin.create');
        Route::post('/izin/store', [PengajuanIzinController::class, 'store'])->name('karyawan.izin.store');
        Route::get('/jadwal-kerja', [KaryawanDashboardController::class, 'jadwal'])->name('karyawan.jadwal.index');
    });
});

require __DIR__.'/auth.php';