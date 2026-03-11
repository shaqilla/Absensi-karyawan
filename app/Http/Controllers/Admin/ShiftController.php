<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    // TAMPILKAN SEMUA DATA SHIFT
    public function index()
    {
        // Ambil semua data shift dari database
        $shifts = Shift::all();
        return view('admin.shift.index', compact('shifts'));
    }

    // TAMPILKAN FORM TAMBAH SHIFT BARU
    public function create()
    {
        // Tidak perlu ambil data apapun, langsung tampilkan form kosong
        return view('admin.shift.create');
    }

    // SIMPAN SHIFT BARU KE DATABASE
    public function store(Request $request)
    {
        // Validasi semua input dari form
        $request->validate([
            'nama_shift'      => 'required|string|max:50',
            'jam_masuk'       => 'required',                    // Format HH:MM, contoh: 08:00
            'jam_keluar'      => 'required',                    // Format HH:MM, contoh: 17:00
            'toleransi_telat' => 'required|numeric|min:0',      // Menit toleransi, minimal 0
        ]);

        // Simpan data shift baru ke database
        Shift::create([
            'nama_shift'      => $request->nama_shift,
            'jam_masuk'       => $request->jam_masuk,
            'jam_keluar'      => $request->jam_keluar,
            'toleransi_telat' => $request->toleransi_telat,    // Disimpan dalam satuan menit
        ]);

        return redirect()->route('admin.shift.index')->with('success', 'Shift baru berhasil disimpan!');
    }

    // HAPUS SHIFT DARI DATABASE
    public function destroy($id)
    {
        // Cari shift yang akan dihapus, kalau tidak ada → error 404
        $shift = Shift::findOrFail($id);

        // WAJIB hapus dulu semua jadwal kerja yang memakai shift ini
        // Karena shift_id di tabel jadwal_kerja adalah Foreign Key
        // Kalau shift dihapus duluan → error karena jadwal masih merujuk ke shift ini
        \App\Models\JadwalKerja::where('shift_id', $id)->delete();

        // Baru hapus shift-nya setelah semua jadwal terkait sudah dihapus
        $shift->delete();

        return back()->with('success', 'Shift berhasil dihapus!');
    }
}