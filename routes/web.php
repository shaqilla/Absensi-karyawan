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
use App\Http\Controllers\Admin\LokasiKantorController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. HALAMAN UTAMA
Route::get('/', function () {
    return redirect('/dashboard');
});

// 2. LOGIKA REDIRECT SETELAH LOGIN
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user) {
        if ($user->role == 'admin') {
            return \Inertia\Inertia::location(route('admin.dashboard'));
        } 
        if ($user->role == 'karyawan') {
            return \Inertia\Inertia::location(route('karyawan.dashboard'));
        }
    } 
    return redirect()->route('login');
})->middleware(['auth'])->name('dashboard');

// 3. RUTE TERPROTEKSI (LOGIN WAJIB)
Route::middleware('auth')->group(function () {
    
    // Rute Profile Standar
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================
    // --- GRUP ROLE ADMIN ---
    // ============================
    Route::prefix('admin')->group(function () {
        // Dashboard & Profil
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/profil', [DashboardController::class, 'profil'])->name('admin.profil');

        // Manajemen User
        Route::get('/karyawan', [KaryawanController::class, 'index'])->name('admin.karyawan.index');
        Route::get('/karyawan/create', [KaryawanController::class, 'create'])->name('admin.karyawan.create');
        Route::post('/karyawan/store', [KaryawanController::class, 'store'])->name('admin.karyawan.store');
        Route::get('/karyawan/{id}/edit', [KaryawanController::class, 'edit'])->name('admin.karyawan.edit');
        Route::put('/karyawan/{id}', [KaryawanController::class, 'update'])->name('admin.karyawan.update');
        Route::delete('/karyawan/{id}', [KaryawanController::class, 'destroy'])->name('admin.karyawan.destroy');

        // Master Data
        Route::resource('shift', ShiftController::class)->names('admin.shift');
        Route::resource('jadwal', JadwalController::class)->names('admin.jadwal');
        Route::get('/lokasi-kantor', [LokasiKantorController::class, 'index'])->name('admin.lokasi.index');
        Route::post('/lokasi-kantor', [LokasiKantorController::class, 'update'])->name('admin.lokasi.update');

        // Operasional & Laporan
        Route::get('/monitor-qr', function () { return view('admin.monitor_qr'); })->name('admin.monitor.index');
        Route::get('/generate-new-token', [QrController::class, 'generate'])->name('admin.qr.generate');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('admin.laporan.index');
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('admin.pengajuan.index');
        Route::patch('/pengajuan/{id}', [PengajuanController::class, 'updateStatus'])->name('admin.pengajuan.update'); 
    });

    // ============================
    // --- GRUP ROLE KARYAWAN ---
    // ============================
    Route::prefix('karyawan')->group(function () {
        // Dashboard & Profil
        Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('karyawan.dashboard');
        Route::get('/profil', [KaryawanDashboardController::class, 'profil'])->name('karyawan.profil');

        // Fitur Absensi (PERBAIKAN: Diarahkan ke Controller)
        Route::get('/scan', [AbsensiController::class, 'index'])->name('karyawan.scan');
        Route::post('/absen/store', [AbsensiController::class, 'store'])->name('karyawan.absen.store');
        Route::get('/jadwal-kerja', [KaryawanDashboardController::class, 'jadwal'])->name('karyawan.jadwal.index');

        // Fitur Pengajuan Izin
        Route::get('/izin', [PengajuanIzinController::class, 'index'])->name('karyawan.izin.index');
        Route::get('/izin/create', [PengajuanIzinController::class, 'create'])->name('karyawan.izin.create');
        Route::post('/izin/store', [PengajuanIzinController::class, 'store'])->name('karyawan.izin.store');
    });

});

require __DIR__.'/auth.php';