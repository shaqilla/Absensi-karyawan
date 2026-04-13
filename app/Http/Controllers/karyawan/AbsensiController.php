<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller; // Induk Class (Inheritance)
use Illuminate\Http\Request;
// Import Model (Objek yang mewakili Tabel di Database)
use App\Models\{Presensi, QrSession, Pengajuan, LokasiKantor, JadwalKerja, PointRule, PointLedger, UserToken};
use App\Helpers\GeoHelper;       // Helper untuk rumus matematika Haversine
use Illuminate\Support\Facades\Auth; // Untuk mengambil data user yang sedang login
use Illuminate\Support\Facades\DB;   // Untuk menjaga keamanan transaksi data
use Carbon\Carbon;                   // Library untuk manipulasi waktu

// "class" adalah kerangka besar. "extends" adalah inheritance (pewarisan sifat dari induk)
class AbsensiController extends Controller
{
    // Tampilan Halaman Scan (Metode GET)
    public function index()
    {
        // "Variable" $lokasi menyimpan "Object" data kantor dari database
        $lokasi = LokasiKantor::first();

        // "Pengkondisian" (If-Else): Cek apakah admin sudah set lokasi?
        if (!$lokasi) {
            return redirect()->route('karyawan.dashboard')
                ->with('error', 'Lokasi belum diatur admin!');
        }

        return view('karyawan.scan', compact('lokasi'));
    }

    // "Method" Store: Jantung aplikasi untuk memproses absen (Metode POST)
    public function store(Request $request)
    {
        $lokasi         = LokasiKantor::first();
        $user           = Auth::user(); // "Object" User yang login
        $userId         = $user->id;   // "Variable" menyimpan ID
        $hariIniTanggal = now()->toDateString();
        $waktuSekarang  = now();

        // "Array" Asosiatif: Untuk mapping hari Inggris ke Indonesia
        $hariInggris = now()->format('l');
        $daftarHari  = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat',
            'Saturday' => 'sabtu', 'Sunday' => 'minggu'
        ];
        $namaHariIni = $daftarHari[$hariInggris];

        // LOGIKA 1: Validasi Jadwal (Cek apakah hari ini jadwalnya masuk?)
        $jadwal = JadwalKerja::with('shift')
            ->where('user_id', $userId)
            ->whereRaw('LOWER(hari) = ?', [$namaHariIni])
            ->where('status', 'aktif')
            ->first();

        if (!$jadwal) {
            return response()->json(['success' => false, 'message' => 'Gak ada jadwal hari ini!'], 422);
        }

        // LOGIKA 2: Validasi Geofencing (Jarak GPS)
        // Memanggil "Function" calculateDistance dari class GeoHelper
        $jarak = GeoHelper::calculateDistance(
            $request->lat, $request->lng,
            $lokasi->latitude, $lokasi->longitude
        );

        if ($jarak > $lokasi->radius) {
            return response()->json(['success' => false, 'message' => 'Gagal! Anda di luar radius kantor.'], 403);
        }

        // LOGIKA 3: Validasi QR Code (Token Security)
        $qr = QrSession::where('token', $request->token)->where('is_active', true)->where('expired_at', '>', now())->first();
        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR Code Kadaluwarsa!'], 403);
        }

        // LOGIKA 4: Persiapan Waktu Shift (Temporal Validation)
        $jamMasukShift  = Carbon::parse($hariIniTanggal . ' ' . $jadwal->shift->jam_masuk);
        $batasToleransi = $jamMasukShift->copy()->addMinutes($jadwal->shift->toleransi_telat);
        $jamPulangShift = Carbon::parse($hariIniTanggal . ' ' . $jadwal->shift->jam_keluar);

        // Cek apakah sudah ada baris absen hari ini? (Prinsip Satu Row per Hari)
        $presensi = Presensi::where('user_id', $userId)->where('tanggal', $hariIniTanggal)->first();

        // "DB::beginTransaction" untuk memastikan data aman jika terjadi error di tengah jalan
        DB::beginTransaction();
        try {
            if (!$presensi) {
                // LOGIKA ABSEN MASUK (INSERT/CREATE)

                // Cek kalau terlalu pagi (Overloading: membandingkan waktu)
                if ($waktuSekarang->lt($jamMasukShift)) {
                    return response()->json(['success' => false, 'message' => 'Belum masuk jam kerja!']);
                }

                $status = 'hadir';
                $keterangan = 'Hadir Tepat Waktu';

                // FITUR BARU: TOKEN INTERCEPTOR (GAMIFIKASI)
                // Jika waktu sekarang melebihi toleransi, cek apakah punya "Token Kelonggaran"
                if ($waktuSekarang->gt($batasToleransi)) {
                    $token = UserToken::where('user_id', $userId)
                                ->where('status', 'AVAILABLE')
                                ->whereHas('item', function($q) {
                                    $q->where('item_name', 'LIKE', '%Terlambat%');
                                })->first();

                    if ($token) {
                        $status = 'hadir'; // Status dipaksa jadi Hadir karena pake Token
                        $keterangan = 'Hadir (Token ' . $token->item->item_name . ' digunakan)';
                        $token->update(['status' => 'USED']); // Token berubah jadi Terpakai
                    } else {    
                        $status = 'telat';
                        $keterangan = 'Terlambat (Tanpa Token)';
                    }
                }

                // Simpan data (INSERT transaksi baru)
                Presensi::create([
                    'user_id'       => $userId,
                    'qr_session_id' => $qr->id,
                    'shift_id'      => $jadwal->shift_id, // SNAPSHOT: Kunci aturan shift saat ini
                    'tanggal'       => $hariIniTanggal,
                    'jam_masuk'     => $waktuSekarang,
                    'latitude'      => $request->lat,
                    'longitude'     => $request->lng,
                    'status'        => $status,
                    'keterangan'    => $keterangan,
                    'kategori_id'   => 1
                ]);

                // FITUR BARU: RULE ENGINE (POIN OTOMATIS)
                // Memanggil fungsi internal untuk hitung poin
                $this->applyPointRules($user, $waktuSekarang);

                DB::commit(); // Simpan permanen
                return response()->json(['success' => true, 'message' => $keterangan]);

            } else {
                // LOGIKA ABSEN PULANG (UPDATE)

                if ($presensi->jam_keluar != null) {
                    return response()->json(['success' => false, 'message' => 'Udah absen pulang!']);
                }

                // Proteksi Pulang Awal (Early Clock-out Protection)
                if ($waktuSekarang->lt($jamPulangShift)) {
                    return response()->json(['success' => false, 'message' => 'Belum jam pulang!']);
                }

                $presensi->update(['jam_keluar' => $waktuSekarang]); // Mengupdate baris yang sama
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Berhasil absen pulang!']);
            }
        } catch (\Exception $e) {
            DB::rollback(); // Batalkan semua jika error
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    // "Private Method" applyPointRules: Mesin aturan poin dinamis
    // Ini dipanggil di dalam method store (modularitas program)
    private function applyPointRules($user, $waktuSekarang)
    {
        // Ambil aturan (Rules) yang dibuat Admin
        $rules = PointRule::where('target_role', $user->role)->get();

        // Ambil saldo poin terakhir dari tabel Ledger (Audit Trail)
        $lastLedger = PointLedger::where('user_id', $user->id)->latest()->first();
        $currentBalance = $lastLedger ? $lastLedger->current_balance : 0;

        // "Pengulangan" (Foreach): Cek satu per satu aturan yang cocok dengan jam absen
        foreach ($rules as $rule) {
            $apply = false;
            $nowTime = $waktuSekarang->toTimeString();

            if ($rule->condition_operator == '<' && $nowTime <= $rule->condition_value) $apply = true;
            if ($rule->condition_operator == '>' && $nowTime >= $rule->condition_value) $apply = true;

            if ($apply) {
                // "Object" PointLedger: Mencatat mutasi poin (seperti rekening bank)
                PointLedger::create([
                    'user_id' => $user->id,
                    'transaction_type' => $rule->point_modifier > 0 ? 'EARN' : 'PENALTY',
                    'amount' => $rule->point_modifier,
                    'current_balance' => $currentBalance + $rule->point_modifier,
                    'description' => 'Poin Otomatis: ' . $rule->rule_name
                ]);
                break; // Berhenti jika sudah menemukan satu aturan yang cocok
            }
        }
    }
}
