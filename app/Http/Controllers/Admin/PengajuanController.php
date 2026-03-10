<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanController extends Controller
{
    // =========================================
    // TAMPILKAN SEMUA PENGAJUAN IZIN/SAKIT/CUTI
    // =========================================
    public function index(Request $request)
    {
        // Ambil input filter dari URL kalau ada
        // Contoh: /pengajuan?search=budi&status=pending
        $search = $request->search;
        $status = $request->status;

        // Buat query dasar dengan eager loading relasi
        // 'karyawan' → relasi ke model User (yang mengajukan)
        // 'karyawan.karyawan' → relasi dari User ke detail Karyawan (NIP, jabatan, dll)
        $query = Pengajuan::with(['karyawan.karyawan']);

        // Kalau ada input search, filter berdasarkan nama karyawan
        // whereHas = filter berdasarkan kondisi di relasi
        if ($search) {
            $query->whereHas('karyawan', function($q) use ($search) {
                // 'like' + %...% = cari nama yang mengandung kata tersebut
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        // Kalau ada filter status, tambahkan kondisi where
        if ($status) {
            $query->where('status_approval', $status);
        }

        // ⚠️ CATATAN: Baris di bawah ini tidak menggunakan $query yang sudah difilter
        // Harusnya pakai: $pengajuans = $query->orderBy('created_at', 'desc')->get();
        // Tapi kode sekarang membuat query baru tanpa filter → search & status belum berfungsi
        $pengajuans = Pengajuan::with(['karyawan.karyawan'])
            ->orderBy('created_at', 'desc') // Urutkan dari yang terbaru
            ->get();

        return view('admin.pengajuan.index', compact('pengajuans'));
    }

    // =========================================
    // UPDATE STATUS PENGAJUAN (APPROVE / TOLAK)
    // =========================================
    public function updateStatus(Request $request, $id)
    {
        // Cari pengajuan berdasarkan ID, kalau tidak ada → error 404
        $pengajuan = Pengajuan::findOrFail($id);

        // Update status pengajuan dengan data dari form
        $pengajuan->update([
            'status_approval' => $request->status,  // 'disetujui' atau 'ditolak'
            'approved_by'     => Auth::id(),         // Simpan ID admin yang memproses
            'catatan_admin'   => $request->catatan   // Catatan/alasan dari admin
        ]);

        // Kembali ke halaman sebelumnya dengan pesan sukses
        return back()->with('success', 'Status pengajuan berhasil diperbarui!');
    }
}