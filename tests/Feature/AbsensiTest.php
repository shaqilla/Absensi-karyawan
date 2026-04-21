<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Shift;
use App\Models\JadwalKerja;
use App\Models\QrSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AbsensiTest extends TestCase
{
    // Ini perintah buat "BERSIHIN & MIGRATE" database testing otomatis tiap kali test jalan
    use RefreshDatabase;

    #[Test]
    public function karyawan_bisa_absen_masuk_tepat_waktu()
    {
        // 1. Bikin data User (Karyawan)
        /** @var User $user */ // Trik biar VS Code gak merah-merah
        $user = User::factory()->create([
            'role' => 'karyawan'
        ]);

        // 2. Bikin data Shift (Wajib ada karena Controller lu manggil shift)
        $shift = Shift::create([
            'nama_shift' => 'Shift Pagi',
            'jam_masuk' => '07:00:00',
            'jam_keluar' => '16:00:00',
            'toleransi_telat' => 15
        ]);

        // 3. Bikin Jadwal Kerja hari ini buat si User tadi
        JadwalKerja::create([
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'hari' => 'rabu', // mapping hari otomatis
            'status' => 'aktif'
        ]);

        // 4. Bikin QR Token yang masih aktif
        QrSession::create([
            'token' => 'token-test-123',
            'is_active' => true,
            'expired_at' => now()->addMinutes(10)
        ]);

        // 5. SIMULASI KIRIM DATA (actingAs = Login otomatis)
        $response = $this->actingAs($user)->postJson(route('karyawan.absen.store'), [
            'token' => 'token-test-123',
            'lat' => '-6.1234',
            'lng' => '106.1234'
        ]);

        // 6. CEK HASILNYA
        // Cek apakah statusnya 200 (Berhasil)
        $response->assertStatus(200);

        // Cek apakah datanya beneran masuk ke tabel presensis di database testing
        $this->assertDatabaseHas('presensis', [
            'user_id' => $user->id,
            'status' => 'hadir'
        ]);
    }
}
