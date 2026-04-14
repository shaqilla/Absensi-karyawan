<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Presensi, QrSession, Pengajuan, LokasiKantor, JadwalKerja, PointRule, PointLedger, UserToken};
use App\Helpers\GeoHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index()
    {
        $lokasi = LokasiKantor::first();
        if (!$lokasi) {
            return redirect()->route('karyawan.dashboard')->with('error', 'Lokasi belum diatur admin!');
        }
        return view('karyawan.scan', compact('lokasi'));
    }

    public function store(Request $request)
    {
        $timezone = 'Asia/Jakarta';
        $waktuSekarang = Carbon::now($timezone);
        $hariIniTanggal = $waktuSekarang->toDateString();
        $user = Auth::user();

        $lokasi = LokasiKantor::first();
        $mappingHari = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu'
        ];
        $namaHariIni = $mappingHari[$waktuSekarang->format('l')];

        $jadwal = JadwalKerja::with('shift')->where('user_id', $user->id)
            ->whereRaw('LOWER(hari) = ?', [$namaHariIni])
            ->where('status', 'aktif')->first();

        if (!$jadwal) return response()->json(['message' => 'Gak ada jadwal hari ini!'], 422);

        $jarak = GeoHelper::calculateDistance($request->lat, $request->lng, $lokasi->latitude, $lokasi->longitude);
        if ($jarak > $lokasi->radius) return response()->json(['message' => 'Gagal! Diluar radius kantor'], 403);

        $qr = QrSession::where('token', $request->token)->where('is_active', true)->where('expired_at', '>', $waktuSekarang)->first();
        if (!$qr) return response()->json(['message' => 'QR Code Kadaluwarsa!'], 403);

        $jamMasukShift = Carbon::createFromFormat('Y-m-d H:i:s', $hariIniTanggal . ' ' . $jadwal->shift->jam_masuk, $timezone);
        $batasToleransi = $jamMasukShift->copy()->addMinutes($jadwal->shift->toleransi_telat);
        $jamPulangShift = Carbon::createFromFormat('Y-m-d H:i:s', $hariIniTanggal . ' ' . $jadwal->shift->jam_keluar, $timezone);

        $presensi = Presensi::where('user_id', $user->id)->where('tanggal', $hariIniTanggal)->first();

        DB::beginTransaction();
        try {
            if (!$presensi) {
                // --- LOGIKA ABSEN MASUK ---
                if ($waktuSekarang->lt($jamMasukShift->copy()->subMinutes(60))) {
                    return response()->json(['message' => 'Belum masuk jam kerja!'], 422);
                }

                $status = 'hadir';
                $keterangan = 'Hadir Tepat Waktu';
                $tokenObj = null;

                // --- CEK TERLAMBAT ---
                if ($waktuSekarang->gt($batasToleransi)) {
                    // Cari Token "Bebas Telat"
                    $tokenObj = UserToken::where('user_id', $user->id)
                                ->where('status', 'AVAILABLE')
                                ->whereHas('item', function($q) {
                                    $q->where('item_name', 'LIKE', '%Telat%')
                                      ->orWhere('item_name', 'LIKE', '%Terlambat%');
                                })->first();

                    if ($tokenObj) {
                        $status = 'hadir';
                        $keterangan = 'Hadir (Pakai Voucher: ' . $tokenObj->item->item_name . ')';
                    } else {
                        $status = 'telat';
                        $keterangan = 'Terlambat (Tanpa Voucher)';

                        // CARI ATURAN DENDA (Gak peduli huruf besar/kecil)
                        $ruleTelat = PointRule::whereRaw('LOWER(rule_name) = ?', ['telat'])
                                    ->orWhereRaw('LOWER(rule_name) = ?', ['terlambat'])
                                    ->first();

                        if ($ruleTelat) {
                            $lastL = PointLedger::where('user_id', $user->id)->latest()->first();
                            $currentBal = $lastL ? $lastL->current_balance : 0;

                            PointLedger::create([
                                'user_id' => $user->id,
                                'transaction_type' => 'PENALTY',
                                'amount' => $ruleTelat->point_modifier, // Minus
                                'current_balance' => $currentBal + $ruleTelat->point_modifier,
                                'description' => 'Denda Terlambat: ' . $hariIniTanggal
                            ]);
                        }
                    }
                }

                $newP = Presensi::create([
                    'user_id' => $user->id,
                    'qr_session_id' => $qr->id,
                    'shift_id' => $jadwal->shift_id,
                    'tanggal' => $hariIniTanggal,
                    'jam_masuk' => $waktuSekarang->toDateTimeString(),
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'latitude' => $request->lat,
                    'longitude' => $request->lng,
                    'kategori_id' => 1
                ]);

                // MATIKAN VOUCHER (JADI USED)
                if ($tokenObj) {
                    $tokenObj->update(['status' => 'USED', 'used_at_attendance_id' => $newP->id]);
                }

                // Berikan Poin Hadir Pagi (Jika status Hadir & Tepat Waktu)
                if ($status == 'hadir') {
                    $this->applyPointRules($user, $waktuSekarang);
                }

                DB::commit();
                return response()->json(['message' => $keterangan], 200);

            } else {
                // --- LOGIKA ABSEN PULANG ---
                if ($presensi->jam_keluar) return response()->json(['message' => 'Udah absen pulang!'], 422);
                if ($waktuSekarang->lt($jamPulangShift)) return response()->json(['message' => 'Belum jam pulang!'], 422);

                $presensi->update(['jam_keluar' => $waktuSekarang->toDateTimeString()]);
                DB::commit();
                return response()->json(['message' => 'Berhasil absen pulang!'], 200);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Sistem Error: ' . $e->getMessage()], 500);
        }
    }

    private function applyPointRules($user, $waktuSekarang)
    {
        $rules = PointRule::where('target_role', $user->role)->where('point_modifier', '>', 0)->get();
        $lastL = PointLedger::where('user_id', $user->id)->latest()->first();
        $balance = $lastL ? $lastL->current_balance : 0;

        foreach ($rules as $rule) {
            if ($rule->condition_operator == '<' && $waktuSekarang->toTimeString() <= $rule->condition_value) {
                PointLedger::create([
                    'user_id' => $user->id,
                    'transaction_type' => 'EARN',
                    'amount' => $rule->point_modifier,
                    'current_balance' => $balance + $rule->point_modifier,
                    'description' => 'Bonus Absen: ' . $rule->rule_name
                ]);
                break;
            }
        }
    }
}
