<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\{QrController, DashboardController, KaryawanController, PengajuanController, LaporanController, ShiftController, JadwalController, LokasiKantorController, PresensiManualController, AssessmentController, AssessmentCategoryController, QuestionController, PointRuleController, AnalyticsController as AdminAnalytics};
use App\Http\Controllers\Karyawan\{AbsensiController, KaryawanDashboardController, PengajuanIzinController, WalletController, TicketController as KaryawanTicket};
use App\Http\Controllers\Operator\HelpdeskController as OperatorHelpdesk;
use Illuminate\Support\Facades\{Route, Auth};
use Inertia\Inertia;

// 1. HALAMAN DEPAN
Route::get('/', function () {
    return redirect('/dashboard');
});

// 2. LOGIKA REDIRECT DASHBOARD (Handle SEMUA ROLE)
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user) {
        if (in_array($user->role, ['admin', 'pimpinan', 'operator'])) {
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

    // Profile Dasar (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // AREA ADMIN, PIMPINAN, & OPERATOR (Rute yang Bisa Diakses Bareng)
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'checkRole:admin,pimpinan,operator'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [DashboardController::class, 'profil'])->name('profil');
        Route::get('/analytics/helpdesk', [AdminAnalytics::class, 'index'])->name('analytics.helpdesk');
    });

    // AREA KHUSUS OPERATOR & ADMIN
    Route::prefix('operator')->name('operator.')->middleware(['auth', 'checkRole:operator,admin'])->group(function () {
        Route::get('/tickets', [OperatorHelpdesk::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{id}', [OperatorHelpdesk::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{id}/reply', [OperatorHelpdesk::class, 'reply'])->name('tickets.reply');
        Route::patch('/tickets/{id}/status', [OperatorHelpdesk::class, 'updateStatus'])->name('tickets.status');
        Route::get('/tickets/suggestion/{category_id}', [OperatorHelpdesk::class, 'getSuggestion'])->name('tickets.suggestion');
    });

    // AREA ADMIN & PIMPINAN (Persetujuan & Penilaian)
    Route::prefix('admin')->name('admin.')->middleware('checkRole:admin,pimpinan')->group(function () {
        // Pengajuan Izin
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::patch('/pengajuan/{id}', [PengajuanController::class, 'updateStatus'])->name('pengajuan.update');

        // Laporan Absensi
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');

        // Modul Penilaian (Input & Report)
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

    // AREA KHUSUS ADMIN (FULL POWER - MASTER DATA)
    Route::prefix('admin')->name('admin.')->middleware('checkRole:admin')->group(function () {
        // CRUD Karyawan
        Route::resource('karyawan', KaryawanController::class)->except(['show']);

        // Master Data Absensi
        Route::resource('shift', ShiftController::class);
        Route::resource('jadwal', JadwalController::class);
        Route::get('/lokasi-kantor', [LokasiKantorController::class, 'index'])->name('lokasi.index');
        Route::post('/lokasi-kantor', [LokasiKantorController::class, 'update'])->name('lokasi.update');

        // QR & Presensi Manual
        Route::get('/monitor-qr', function () {
            return view('admin.monitor_qr');
        })->name('monitor.index');
        Route::get('/qr-scanner', function () {
            return view('admin.qr_generator');
        })->name('qr.view');
        Route::get('/generate-new-token', [QrController::class, 'generate'])->name('qr.generate');
        Route::get('/presensi-manual', [PresensiManualController::class, 'create'])->name('presensi.manual');
        Route::post('/presensi-manual', [PresensiManualController::class, 'store_manual'])->name('presensi.store_manual');

        // Setup Penilaian (Kategori & Pertanyaan)
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

        // Modul Poin (Integrity)
        Route::get('/integrity', [PointRuleController::class, 'index'])->name('integrity.index');
        Route::post('/integrity/rule', [PointRuleController::class, 'storeRule'])->name('integrity.rule.store');
        Route::put('/integrity/rule/{id}', [PointRuleController::class, 'updateRule'])->name('integrity.rule.update');
        Route::delete('/integrity/rule/{id}', [PointRuleController::class, 'destroyRule'])->name('integrity.rule.destroy');
        Route::post('/integrity/item', [PointRuleController::class, 'storeItem'])->name('integrity.item.store');
        Route::put('/integrity/item/{id}', [PointRuleController::class, 'updateItem'])->name('integrity.item.update');
        Route::delete('/integrity/item/{id}', [PointRuleController::class, 'destroyItem'])->name('integrity.item.destroy');
    });

    // AREA KHUSUS KARYAWAN
    Route::prefix('karyawan')->name('karyawan.')->middleware('checkRole:karyawan,admin')->group(function () {
        // Dashboard & Profil
        Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [KaryawanDashboardController::class, 'profil'])->name('profil');

        // Absensi & Jadwal
        Route::get('/scan', [AbsensiController::class, 'index'])->name('scan');
        Route::post('/absen/store', [AbsensiController::class, 'store'])->name('absen.store');
        Route::get('/jadwal-kerja', [KaryawanDashboardController::class, 'jadwal'])->name('jadwal.index');
        Route::get('/laporan-saya', [KaryawanDashboardController::class, 'laporan'])->name('laporan.index');

        // Izin (Pengajuan)
        Route::get('/izin', [PengajuanIzinController::class, 'index'])->name('izin.index');
        Route::get('/izin/create', [PengajuanIzinController::class, 'create'])->name('izin.create');
        Route::post('/izin/store', [PengajuanIzinController::class, 'store'])->name('izin.store');

        // Wallet & Integritas
        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/exchange/{id}', [WalletController::class, 'exchange'])->name('wallet.exchange');
        Route::get('/rapor-performa', [AssessmentController::class, 'myReport'])->name('rapor');

        // FITUR TIKET HELPDESK 
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [KaryawanTicket::class, 'index'])->name('index');
            Route::get('/create', [KaryawanTicket::class, 'create'])->name('create');
            Route::post('/store', [KaryawanTicket::class, 'store'])->name('store');
            Route::get('/{id}', [KaryawanTicket::class, 'show'])->name('show');
            Route::post('/check-duplicate', [KaryawanTicket::class, 'checkDuplicate'])->name('check');
            Route::post('/{id}/reply', [KaryawanTicket::class, 'reply'])->name('reply');
            Route::post('/{id}/rate', [KaryawanTicket::class, 'rate'])->name('rate');
        });
    });
});

require __DIR__ . '/auth.php';
