<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    // =========================================
    // MENAMPILKAN SEMUA JADWAL KERJA
    // =========================================
    public function index()
    {
        // Ambil semua jadwal beserta relasi user (karyawan) dan shift-nya
        // with(['user', 'shift']) = eager loading supaya tidak boros query
        $jadwals = JadwalKerja::with(['user', 'shift'])->get();

        // Kirim data ke halaman daftar jadwal
        return view('admin.jadwal.index', compact('jadwals'));
    }

    // =========================================
    // TAMPILKAN FORM TAMBAH JADWAL BARU
    // =========================================
    public function create()
    {
        // Ambil semua user yang rolenya 'karyawan' untuk pilihan dropdown
        $karyawans = User::where('role', 'karyawan')->get();

        // Ambil semua data shift yang tersedia
        $shifts = Shift::all();

        // Daftar pilihan hari dalam seminggu
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        // Kirim semua data ke form tambah jadwal
        return view('admin.jadwal.create', compact('karyawans', 'shifts', 'hari'));
    }

    // =========================================
    // SIMPAN JADWAL BARU KE DATABASE
    // =========================================
    public function store(Request $request)
    {
        // Validasi input dari form sebelum disimpan
        $request->validate([
            'user_id'  => 'required',           // Karyawan wajib dipilih
            'shift_id' => 'required',           // Shift wajib dipilih
            'hari'     => 'required|array',     // Hari wajib diisi dan bisa lebih dari satu (checkbox)
        ]);

        // Loop setiap hari yang dipilih (misal: senin, rabu, jumat)
        foreach ($request->hari as $h) {

            // updateOrCreate = cari dulu berdasarkan user_id + hari
            // Kalau SUDAH ADA → update shift_id dan status-nya
            // Kalau BELUM ADA → buat data baru
            // Tujuan: mencegah jadwal dobel untuk hari yang sama
            JadwalKerja::updateOrCreate(
                [
                    'user_id' => $request->user_id, // Kondisi pencarian
                    'hari'    => $h                 // Kondisi pencarian
                ],
                [
                    'shift_id' => $request->shift_id, // Data yang diisi/diupdate
                    'status'   => 'aktif'             // Otomatis set status aktif
                ]
            );
        }

        // Redirect ke halaman daftar jadwal dengan pesan sukses
        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal kerja berhasil disetel!');
    }

    // =========================================
    // TAMPILKAN FORM EDIT JADWAL
    // =========================================
    public function edit($id)
    {
        // Cari jadwal berdasarkan ID, kalau tidak ada otomatis error 404
        $jadwal = JadwalKerja::findOrFail($id);

        // Ambil semua karyawan untuk dropdown
        $karyawans = User::where('role', 'karyawan')->get();

        // Ambil semua shift untuk dropdown
        $shifts = Shift::all();

        // Daftar hari (huruf kecil karena disesuaikan dengan data di database)
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        // Kirim semua data ke halaman form edit
        return view('admin.jadwal.edit', compact('jadwal', 'karyawans', 'shifts', 'hari'));
    }

    // =========================================
    // SIMPAN PERUBAHAN JADWAL KE DATABASE
    // =========================================
    public function update(Request $request, $id)
    {
        // Validasi semua input wajib diisi
        $request->validate([
            'user_id'  => 'required',
            'shift_id' => 'required',
            'hari'     => 'required',
            'status'   => 'required', // Di edit bisa ubah status (aktif/nonaktif)
        ]);

        // Cari jadwal yang akan diupdate berdasarkan ID
        $jadwal = JadwalKerja::findOrFail($id);

        // Update data jadwal dengan data baru dari form
        $jadwal->update([
            'user_id'  => $request->user_id,
            'shift_id' => $request->shift_id,
            'hari'     => $request->hari,
            'status'   => $request->status,
        ]);

        // Redirect ke halaman daftar jadwal dengan pesan sukses
        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal kerja berhasil diperbarui!');
    }

    // =========================================
    // HAPUS JADWAL
    // =========================================
    public function destroy($id)
    {
        // Cari jadwal berdasarkan ID lalu langsung hapus
        // findOrFail = kalau tidak ditemukan, otomatis error 404
        JadwalKerja::findOrFail($id)->delete();

        // Kembali ke halaman sebelumnya dengan pesan sukses
        return back()->with('success', 'Jadwal berhasil dihapus');
    }
}
