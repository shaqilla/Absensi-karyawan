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

// 2. LOGIKA REDIRECT DASHBOARD (Handle Pimpinan juga)
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user) {
        // TAMBAHIN PIMPINAN DI SINI!
        if ($user->role == 'admin' || $user->role == 'pimpinan') {
            return redirect()->route('admin.dashboard');
        }
        if ($user->role == 'karyawan') {
            return redirect()->route('karyawan.dashboard');
        }
    }
    return redirect()->route('login');
})->middleware(['auth'])->name('dashboard');

// 3. SEMUA RUTE YANG BUTUH LOGIN
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // AREA ADMIN & PIMPINAN (RBAC SHARED)
    Route::prefix('admin')->name('admin.')->middleware('checkRole:admin,pimpinan')->group(function () {

        // Dashboard & Profil
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [DashboardController::class, 'profil'])->name('profil');

        // Pengajuan Izin (Approval)
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::patch('/pengajuan/{id}', [PengajuanController::class, 'updateStatus'])->name('pengajuan.update');

        // Laporan Absensi (View Only)
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');

        // Operasional Penilaian (Input & View Report)
        Route::prefix('assessment')->name('assessment.')->group(function () {
            Route::get('/employees', [AssessmentController::class, 'employees'])->name('employees');
            Route::get('/create/{evaluatee_id}', [AssessmentController::class, 'create'])->name('create');
            Route::post('/store', [AssessmentController::class, 'store'])->name('store');
            Route::get('/report', [AssessmentController::class, 'report'])->name('report');
            Route::get('/detail/{id}', [AssessmentController::class, 'detail'])->name('detail');
            Route::get('/edit/{id}', [AssessmentController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [AssessmentController::class, 'update'])->name('update');
        });
    });

    // AREA KHUSUS ADMIN (FULL ACCESS)
    Route::prefix('admin')->name('admin.')->middleware('checkRole:admin')->group(function () {

        // Manajemen User & Karyawan
        Route::resource('karyawan', KaryawanController::class)->except(['show']);

        // Master Data
        Route::resource('shift', ShiftController::class);
        Route::resource('jadwal', JadwalController::class);
        Route::get('/lokasi-kantor', [LokasiKantorController::class, 'index'])->name('lokasi.index');
        Route::post('/lokasi-kantor', [LokasiKantorController::class, 'update'])->name('lokasi.update');

        // Operasional QR
        Route::get('/monitor-qr', function () {
            return view('admin.monitor_qr');
        })->name('monitor.index');
        Route::get('/qr-scanner', function () {
            return view('admin.qr_generator');
        })->name('qr.view');
        Route::get('/generate-new-token', [QrController::class, 'generate'])->name('qr.generate');

        // Presensi Manual
        Route::get('/presensi-manual', [PresensiManualController::class, 'create'])->name('presensi.manual');
        Route::post('/presensi-manual', [PresensiManualController::class, 'store_manual'])->name('presensi.store_manual');

        // Modul Penilaian (Setup Kategori & Soal)
        Route::prefix('assessment')->name('assessment.')->group(function () {
            Route::get('/categories', [AssessmentCategoryController::class, 'index'])->name('categories');
            Route::post('/categories', [AssessmentCategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{id}', [AssessmentCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{id}', [AssessmentCategoryController::class, 'destroy'])->name('categories.destroy');
            Route::patch('/categories/{id}/toggle', [AssessmentCategoryController::class, 'toggle'])->name('categories.toggle');

            Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
            Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
            Route::put('/questions/{id}', [QuestionController::class, 'update'])->name('questions.update');
            Route::delete('/questions/{id}', [QuestionController::class, 'destroy'])->name('questions.destroy');
        });

        // Modul Ekonomi (Rules & Marketplace)
        Route::get('/integrity', [PointRuleController::class, 'index'])->name('integrity.index');
        Route::post('/integrity/rule', [PointRuleController::class, 'storeRule'])->name('integrity.rule.store');
        Route::put('/integrity/rule/{id}', [PointRuleController::class, 'updateRule'])->name('integrity.rule.update');
        Route::delete('/integrity/rule/{id}', [PointRuleController::class, 'destroyRule'])->name('integrity.rule.destroy');

        Route::post('/integrity/item', [PointRuleController::class, 'storeItem'])->name('integrity.item.store');
        Route::put('/integrity/item/{id}', [PointRuleController::class, 'updateItem'])->name('integrity.item.update');
        Route::delete('/integrity/item/{id}', [PointRuleController::class, 'destroyItem'])->name('integrity.item.destroy');
    });

    // AREA KHUSUS KARYAWAN
    Route::prefix('karyawan')->name('karyawan.')->middleware('checkRole:karyawan')->group(function () {
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
