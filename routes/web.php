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
use App\Http\Controllers\Admin\PresensiManualController;
use App\Http\Controllers\Admin\AssessmentAdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// 1. HALAMAN DEPAN → REDIRECT KE DASHBOARD
// Kalau user buka '/' langsung diarahkan ke '/dashboard'
Route::get('/', function () {
    return redirect('/dashboard');
});

// 2. DASHBOARD ROUTER (PENENTU ARAH SETELAH LOGIN)
// Route ini tugasnya cek role user lalu arahkan ke dashboard yang sesuai
// Inertia::location() = redirect paksa yang memutus siklus Inertia ke Blade
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user) {
        if ($user->role == 'admin') {
            return Inertia::location(route('admin.dashboard'));     // Admin → /admin/dashboard
        }
        if ($user->role == 'karyawan') {
            return Inertia::location(route('karyawan.dashboard')); // Karyawan → /karyawan/dashboard
        }
    }
    // Kalau belum login → arahkan ke halaman login
    return redirect()->route('login');
})->middleware(['auth'])->name('dashboard');

// 3. SEMUA ROUTE DI BAWAH INI BUTUH LOGIN
Route::middleware('auth')->group(function () {

    // Route Profil Breeze (fitur bawaan, tidak diubah)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // GRUP ROUTE ADMIN (prefix: /admin/...)
    // Tambah middleware role admin supaya karyawan tidak bisa akses
    Route::prefix('admin')->group(function () {

        // --- Dashboard & Profil ---
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/profil', [DashboardController::class, 'profil'])->name('admin.profil');

        // --- Manajemen Karyawan (CRUD) ---
        Route::get('/karyawan', [KaryawanController::class, 'index'])->name('admin.karyawan.index');              // GET    /admin/karyawan          → tampilkan semua karyawan
        Route::get('/karyawan/create', [KaryawanController::class, 'create'])->name('admin.karyawan.create');     // GET    /admin/karyawan/create   → form tambah karyawan
        Route::post('/karyawan/store', [KaryawanController::class, 'store'])->name('admin.karyawan.store');       // POST   /admin/karyawan/store    → simpan karyawan baru
        Route::get('/karyawan/{id}/edit', [KaryawanController::class, 'edit'])->name('admin.karyawan.edit');      // GET    /admin/karyawan/{id}/edit → form edit karyawan
        Route::put('/karyawan/{id}', [KaryawanController::class, 'update'])->name('admin.karyawan.update');       // PUT    /admin/karyawan/{id}     → simpan perubahan karyawan
        Route::delete('/karyawan/{id}', [KaryawanController::class, 'destroy'])->name('admin.karyawan.destroy');  // DELETE /admin/karyawan/{id}     → hapus karyawan

        // --- Master Data ---
        // Route::resource = otomatis buat 7 route CRUD sekaligus (index, create, store, show, edit, update, destroy)
        Route::resource('shift', ShiftController::class)->names('admin.shift');
        Route::resource('jadwal', JadwalController::class)->names('admin.jadwal');

        // Lokasi Kantor
        Route::get('/lokasi-kantor', [LokasiKantorController::class, 'index'])->name('admin.lokasi.index');
        Route::post('/lokasi-kantor', [LokasiKantorController::class, 'update'])->name('admin.lokasi.update');

        // --- Generate QR ---
        // Halaman monitor QR (tampilan live QR untuk absensi)
        Route::get('/monitor-qr', function () {
            return view('admin.monitor_qr');
        })->name('admin.monitor.index');

        // Halaman generator QR
        Route::get('/qr-scanner', function () {
            return view('admin.qr_generator');
        })->name('admin.qr.view');

        // API endpoint: generate token QR baru (generate/berganti selama 10 detik)
        Route::get('/generate-new-token', [QrController::class, 'generate'])->name('admin.qr.generate');

        // --- Persetujuan Pengajuan Izin ---
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('admin.pengajuan.index');
        // PATCH = update sebagian data (hanya status_approval)
        Route::patch('/pengajuan/{id}', [PengajuanController::class, 'updateStatus'])->name('admin.pengajuan.update');

        // --- Laporan ---
        // Dipisah antara tampilan web dan versi cetak supaya layout bisa berbeda
        Route::get('/laporan', [LaporanController::class, 'index'])->name('admin.laporan.index');
        Route::get('/laporan/print', [LaporanController::class, 'print'])->name('admin.laporan.print');

        // --- Absensi Manual ---
        Route::get('/presensi-manual', [PresensiManualController::class, 'create'])->name('admin.presensi.manual');         // GET  = tampilkan form absen manual
        Route::post('/presensi-manual', [PresensiManualController::class, 'store'])->name('admin.presensi.store_manual');   // POST = simpan data absen manual

        // --- Assessment ---
        Route::get('/assessment/categories', [AssessmentAdminController::class, 'categories'])->name('admin.assessment.categories');
        Route::post('/assessment/categories', [AssessmentAdminController::class, 'storeCategory'])->name('admin.assessment.categories.store');
        Route::get('/assessment/evaluate', [AssessmentAdminController::class, 'listEmployees'])->name('admin.assessment.employees');
        Route::get('/assessment/evaluate/{id}', [AssessmentAdminController::class, 'create'])->name('admin.assessment.create');
        Route::post('/assessment/store', [AssessmentAdminController::class, 'store'])->name('admin.assessment.store');
        Route::get('/assessment/report', [AssessmentAdminController::class, 'overallReport'])->name('admin.assessment.report');
    });

    // GRUP ROUTE KARYAWAN (prefix: /karyawan/...)
    // Tambah middleware role karyawan supaya admin tidak bisa akses!
    Route::prefix('karyawan')->group(function () {

        // --- Dashboard & Profil ---
        Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('karyawan.dashboard');
        Route::get('/profil', [KaryawanDashboardController::class, 'profil'])->name('karyawan.profil');

        // --- Absensi ---
        Route::get('/scan', [AbsensiController::class, 'index'])->name('karyawan.scan');                 // GET  = tampilkan halaman scan QR
        Route::post('/absen/store', [AbsensiController::class, 'store'])->name('karyawan.absen.store');  // POST = proses absen setelah scan QR 

        // Jadwal kerja karyawan
        Route::get('/jadwal-kerja', [KaryawanDashboardController::class, 'jadwal'])->name('karyawan.jadwal.index');

        // --- Pengajuan Izin ---
        Route::get('/izin', [PengajuanIzinController::class, 'index'])->name('karyawan.izin.index');            // GET  /karyawan/izin         → riwayat pengajuan
        Route::get('/izin/create', [PengajuanIzinController::class, 'create'])->name('karyawan.izin.create');   // GET  /karyawan/izin/create  → form pengajuan baru
        Route::post('/izin/store', [PengajuanIzinController::class, 'store'])->name('karyawan.izin.store');     // POST /karyawan/izin/store   → simpan pengajuan

        // --- Laporan Pribadi ---
        Route::get('/laporan-saya', [KaryawanDashboardController::class, 'laporan'])->name('karyawan.laporan.index');

        // Rapor Penilaian
        Route::get('/rapor-performa', [KaryawanDashboardController::class, 'raporSaya'])->name('karyawan.rapor');
    });
});

// File route khusus auth (login, register, dll) — generate otomatis dari Breeze
require __DIR__ . '/auth.php';
