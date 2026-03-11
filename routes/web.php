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
use App\Http\Controllers\Admin\AssessmentController;
use App\Http\Controllers\Admin\AssessmentCategoryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// 1. HALAMAN DEPAN
Route::get('/', function () {
    return redirect('/dashboard');
});

// 2. DASHBOARD ROUTER
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user) {
        if ($user->role == 'admin') {
            return Inertia::location(route('admin.dashboard'));
        }
        if ($user->role == 'karyawan') {
            return Inertia::location(route('karyawan.dashboard'));
        }
    }
    return redirect()->route('login');
})->middleware(['auth'])->name('dashboard');

// 3. ROUTE YANG BUTUH LOGIN
Route::middleware('auth')->group(function () {

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============= ADMIN ROUTES =============
    Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [DashboardController::class, 'profil'])->name('profil');

        // Karyawan CRUD
        Route::get('/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
        Route::get('/karyawan/create', [KaryawanController::class, 'create'])->name('karyawan.create');
        Route::post('/karyawan/store', [KaryawanController::class, 'store'])->name('karyawan.store');
        Route::get('/karyawan/{id}/edit', [KaryawanController::class, 'edit'])->name('karyawan.edit');
        Route::put('/karyawan/{id}', [KaryawanController::class, 'update'])->name('karyawan.update');
        Route::delete('/karyawan/{id}', [KaryawanController::class, 'destroy'])->name('karyawan.destroy');

        // Master Data
        Route::resource('shift', ShiftController::class)->names('shift');
        Route::resource('jadwal', JadwalController::class)->names('jadwal');

        // Lokasi Kantor
        Route::get('/lokasi-kantor', [LokasiKantorController::class, 'index'])->name('lokasi.index');
        Route::post('/lokasi-kantor', [LokasiKantorController::class, 'update'])->name('lokasi.update');

        // QR Generator
        Route::get('/monitor-qr', function () {
            return view('admin.monitor_qr');
        })->name('monitor.index');
        Route::get('/qr-scanner', function () {
            return view('admin.qr_generator');
        })->name('qr.view');
        Route::get('/generate-new-token', [QrController::class, 'generate'])->name('qr.generate');

        // Pengajuan Izin
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::patch('/pengajuan/{id}', [PengajuanController::class, 'updateStatus'])->name('pengajuan.update');

        // Laporan
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');

        // Presensi Manual
        Route::get('/presensi-manual', [PresensiManualController::class, 'create'])->name('presensi.manual');
        Route::post('/presensi-manual', [PresensiManualController::class, 'store'])->name('presensi.store_manual');

        // ============= ASSESSMENT ROUTES =============
        Route::prefix('assessment')->name('assessment.')->group(function () {

            // Kategori (pakai AssessmentCategoryController)
            Route::get('/categories', [AssessmentCategoryController::class, 'index'])->name('categories');
            Route::post('/categories', [AssessmentCategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{id}', [AssessmentCategoryController::class, 'update'])->name('categories.update');
            Route::patch('/categories/{id}/toggle', [AssessmentCategoryController::class, 'toggleActive'])->name('categories.toggle');
            Route::delete('/categories/{id}', [AssessmentCategoryController::class, 'destroy'])->name('categories.destroy');

            // Penilaian
            Route::get('/employees', [AssessmentController::class, 'employees'])->name('employees');
            Route::get('/create/{evaluatee_id}', [AssessmentController::class, 'create'])->name('create');
            Route::post('/store', [AssessmentController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [AssessmentController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [AssessmentController::class, 'update'])->name('update');
            Route::get('/report/{user_id?}', [AssessmentController::class, 'report'])->name('report');
            Route::get('/history', [AssessmentController::class, 'history'])->name('history');
            Route::delete('/destroy/{id}', [AssessmentController::class, 'destroy'])->name('destroy');
        });
    });

    // ============= KARYAWAN ROUTES =============
    Route::middleware('auth')->prefix('karyawan')->name('karyawan.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [KaryawanDashboardController::class, 'profil'])->name('profil');

        // Absensi
        Route::get('/scan', [AbsensiController::class, 'index'])->name('scan');
        Route::post('/absen/store', [AbsensiController::class, 'store'])->name('absen.store');

        // Jadwal
        Route::get('/jadwal-kerja', [KaryawanDashboardController::class, 'jadwal'])->name('jadwal.index');

        // Pengajuan Izin
        Route::get('/izin', [PengajuanIzinController::class, 'index'])->name('izin.index');
        Route::get('/izin/create', [PengajuanIzinController::class, 'create'])->name('izin.create');
        Route::post('/izin/store', [PengajuanIzinController::class, 'store'])->name('izin.store');

        // Laporan Pribadi
        Route::get('/laporan-saya', [KaryawanDashboardController::class, 'laporan'])->name('laporan.index');

        // Rapor Penilaian
        Route::get('/rapor-performa', [AssessmentController::class, 'myReport'])->name('rapor');
    });
});

require __DIR__ . '/auth.php';