<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QrSession;
use Illuminate\Support\Facades\Auth;

class QrController extends Controller
{
    // GENERATE TOKEN QR BARU UNTUK ABSENSI
    public function generate()
    {
        // Dibungkus try-catch supaya kalau ada error tidak crash
        // tapi balik response JSON dengan pesan error
        try {
            // Buat token acak yang unik
            // random_bytes(20) = generate 20 byte data acak
            // bin2hex() = ubah byte tersebut jadi string heksadesimal (40 karakter)
            // Contoh hasil: "a3f9c2e1b7d4082..."
            $token = bin2hex(random_bytes(20));  

            // Matikan semua token QR yang masih aktif
            // Supaya tidak ada 2 QR aktif sekaligus — mencegah penyalahgunaan
            QrSession::where('is_active', true)->update(['is_active' => false]);

            // Simpan token baru ke database
            QrSession::create([
                'token'      => $token,
                'created_by' => Auth::id(),          // Catat siapa admin yang generate
                'expired_at' => now()->addSeconds(10), // Token aktif hanya 10 detik
                'is_active'  => true                 // Tandai sebagai aktif
            ]);

            // Kirim token ke frontend dalam format JSON
            // Frontend akan pakai token ini untuk tampilkan QR Code
            return response()->json(['token' => $token]);

        } catch (\Exception $e) {
            // Kalau ada error → kirim pesan error dalam format JSON
            // 500 = HTTP status code untuk "Internal Server Error"
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}