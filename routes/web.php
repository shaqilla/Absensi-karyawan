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
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\PointRuleController;
use App\Http\Controllers\Karyawan\WalletController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// 1. HALAMAN DEPAN
Route::get('/', function () {
    return redirect('/dashboard');
});

// 2. LOGIKA REDIRECT DASHBOARD
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

// 3. SEMUA RUTE YANG BUTUH LOGIN
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // KHUSUS ROLE ADMIN
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [DashboardController::class, 'profil'])->name('profil');

        Route::get('/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
        Route::get('/karyawan/create', [KaryawanController::class, 'create'])->name('karyawan.create');
        Route::post('/karyawan/store', [KaryawanController::class, 'store'])->name('karyawan.store');
        Route::get('/karyawan/{id}/edit', [KaryawanController::class, 'edit'])->name('karyawan.edit');
        Route::put('/karyawan/{id}', [KaryawanController::class, 'update'])->name('karyawan.update');
        Route::delete('/karyawan/{id}', [KaryawanController::class, 'destroy'])->name('karyawan.destroy');

        Route::resource('shift', ShiftController::class)->names('shift');
        Route::resource('jadwal', JadwalController::class)->names('jadwal');
        Route::get('/lokasi-kantor', [LokasiKantorController::class, 'index'])->name('lokasi.index');
        Route::post('/lokasi-kantor', [LokasiKantorController::class, 'update'])->name('lokasi.update');

        Route::get('/monitor-qr', function () {
            return view('admin.monitor_qr');
        })->name('monitor.index');
        Route::get('/qr-scanner', function () {
            return view('admin.qr_generator');
        })->name('qr.view');
        Route::get('/generate-new-token', [QrController::class, 'generate'])->name('qr.generate');

        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::patch('/pengajuan/{id}', [PengajuanController::class, 'updateStatus'])->name('pengajuan.update');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');

        Route::get('/presensi-manual', [PresensiManualController::class, 'create'])->name('presensi.manual');
        Route::post('/presensi-manual', [PresensiManualController::class, 'store_manual'])->name('presensi.store_manual');

        // MODUL PENILAIAN (ASSESSMENT) - SUDAH DIPERBAIKI
        Route::prefix('assessment')->name('assessment.')->group(function () {

            // Rute untuk Kategori Penilaian
            Route::get('/categories', [AssessmentCategoryController::class, 'index'])->name('categories');
            Route::post('/categories', [AssessmentCategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{id}', [AssessmentCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{id}', [AssessmentCategoryController::class, 'destroy'])->name('categories.destroy');
            Route::patch('/categories/{id}/toggle', [AssessmentCategoryController::class, 'toggle'])->name('categories.toggle');

            // Rute untuk Pertanyaan (Questions)
            Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
            Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
            Route::put('/questions/{id}', [QuestionController::class, 'update'])->name('questions.update');
            Route::delete('/questions/{id}', [QuestionController::class, 'destroy'])->name('questions.destroy');

            // Operasional Penilaian
            Route::get('/employees', [AssessmentController::class, 'employees'])->name('employees');
            Route::get('/create/{evaluatee_id}', [AssessmentController::class, 'create'])->name('create');
            Route::post('/store', [AssessmentController::class, 'store'])->name('store');
            Route::get('/report', [AssessmentController::class, 'report'])->name('report');
            Route::get('/detail/{id}', [AssessmentController::class, 'detail'])->name('detail');
        });

        // MODUL POIN INTEGRITAS
        Route::get('/integrity', [PointRuleController::class, 'index'])->name('integrity.index');
        Route::post('/integrity/rule', [PointRuleController::class, 'storeRule'])->name('integrity.rule.store');
        Route::delete('/integrity/rule/{id}', [PointRuleController::class, 'destroyRule'])->name('integrity.rule.destroy');
        Route::post('/integrity/item', [PointRuleController::class, 'storeItem'])->name('integrity.item.store');
        Route::delete('/integrity/item/{id}', [PointRuleController::class, 'destroyItem'])->name('integrity.item.destroy');
        Route::put('/integrity/rule/{id}', [PointRuleController::class, 'updateRule'])->name('integrity.rule.update');
        Route::put('/integrity/item/{id}', [PointRuleController::class, 'updateItem'])->name('integrity.item.update');
    });

    // KHUSUS ROLE KARYAWAN
    Route::prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [KaryawanDashboardController::class, 'profil'])->name('profil');

        Route::get('/scan', [AbsensiController::class, 'index'])->name('scan');
        Route::post('/absen/store', [AbsensiController::class, 'store'])->name('absen.store');
        Route::get('/jadwal-kerja', [KaryawanDashboardController::class, 'jadwal'])->name('jadwal.index');

        Route::get('/izin', [PengajuanIzinController::class, 'index'])->name('izin.index');
        Route::get('/izin/create', [PengajuanIzinController::class, 'create'])->name('izin.create');
        Route::post('/izin/store', [PengajuanIzinController::class, 'store'])->name('izin.store');

        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/exchange/{id}', [WalletController::class, 'exchange'])->name('wallet.exchange');

        Route::get('/laporan-saya', [KaryawanDashboardController::class, 'laporan'])->name('laporan.index');
        Route::get('/rapor-performa', [AssessmentController::class, 'myReport'])->name('rapor');
    });
});

require __DIR__ . '/auth.php';
