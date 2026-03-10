<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanIzinController extends Controller
{
    // =========================================
    // TAMPILKAN RIWAYAT PENGAJUAN KARYAWAN
    // =========================================
    public function index()
    {
        $userId = Auth::id();

        // Ambil semua pengajuan milik karyawan yang sedang login
        // Diurutkan dari yang terbaru
        $pengajuans = Pengajuan::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('karyawan.izin_index', compact('pengajuans'));
    }

    // =========================================
    // TAMPILKAN FORM PENGAJUAN BARU
    // =========================================
    public function create()
    {
        // Tidak perlu ambil data apapun, langsung tampilkan form kosong
        return view('karyawan.izin');
    }

    // =========================================
    // SIMPAN PENGAJUAN BARU KE DATABASE
    // =========================================
    public function store(Request $request)
    {
        // LANGKAH 1: Validasi semua input dari form
        $request->validate([
            // Jenis hanya boleh salah satu dari 4 pilihan ini
            'jenis_pengajuan' => 'required|in:cuti,sakit,izin,lembur',

            'tanggal_mulai'   => 'required|date',

            // Tanggal selesai harus sama atau setelah tanggal mulai
            // after_or_equal = tidak boleh tanggal selesai lebih awal dari mulai
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',

            'alasan'          => 'required|string|max:500',

            // Lampiran boleh kosong (nullable), tapi kalau diisi harus:
            // - Berupa gambar (image)
            // - Format jpg, png, atau jpeg
            // - Maksimal 2048 KB (2 MB)
            'lampiran'        => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
        ]);

        // LANGKAH 2: Handle upload file lampiran (kalau ada)
        $lampiranPath = null; // Default null kalau tidak ada lampiran

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');

            // Buat nama file unik dengan menambahkan timestamp di depan
            // Contoh: 1741234567_surat_dokter.jpg
            // Tujuan: mencegah nama file bentrok kalau ada file dengan nama sama
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Pindahkan file ke folder public/uploads/lampiran
            // public_path() = path ke folder public di project Laravel
            $file->move(public_path('uploads/lampiran'), $fileName);

            // Simpan nama file saja (bukan full path) ke database
            $lampiranPath = $fileName;
        }

        // LANGKAH 3: Simpan pengajuan ke database
        Pengajuan::create([
            'user_id'         => Auth::id(),
            'jenis_pengajuan' => $request->jenis_pengajuan,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan'          => $request->alasan,
            'lampiran'        => $lampiranPath,         // Nama file atau null
            'status_approval' => 'pending'              // Default pending, nunggu admin approve
        ]);

        // LANGKAH 4: Redirect ke riwayat dengan pesan sukses
        // Pesan menyebut jenis pengajuan yang baru dibuat
        return redirect()->route('karyawan.izin.index')
            ->with('success', 'Pengajuan ' . $request->jenis_pengajuan . ' Anda telah dikirim dan menunggu persetujuan admin.');
    }
}
