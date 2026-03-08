<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        // Ambil semua jadwal beserta data karyawan dan shift-nya
        $jadwals = JadwalKerja::with(['user', 'shift'])->get();
        return view('admin.jadwal.index', compact('jadwals'));
    }

    public function create()
    {
        $karyawans = User::where('role', 'karyawan')->get();
        $shifts = Shift::all();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        return view('admin.jadwal.create', compact('karyawans', 'shifts', 'hari'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'shift_id' => 'required',
            'hari' => 'required|array', // Bisa pilih banyak hari sekaligus
        ]);

        foreach ($request->hari as $h) {
            JadwalKerja::updateOrCreate(
                ['user_id' => $request->user_id, 'hari' => $h], // Jika sudah ada, update. Jika belum, buat baru.
                ['shift_id' => $request->shift_id, 'status' => 'aktif']
            );
        }

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal kerja berhasil disetel!');
    }

    // 1. Menampilkan Halaman Edit Jadwal
    public function edit($id)
    {
        $jadwal = \App\Models\JadwalKerja::findOrFail($id);
        $karyawans = \App\Models\User::where('role', 'karyawan')->get();
        $shifts = \App\Models\Shift::all();
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        return view('admin.jadwal.edit', compact('jadwal', 'karyawans', 'shifts', 'hari'));
    }

    // 2. Proses Update Data Jadwal
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required',
            'shift_id' => 'required',
            'hari' => 'required',
            'status' => 'required',
        ]);

        $jadwal = \App\Models\JadwalKerja::findOrFail($id);
        $jadwal->update([
            'user_id' => $request->user_id,
            'shift_id' => $request->shift_id,
            'hari' => $request->hari,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal kerja berhasil diperbarui!');
    }

    public function destroy($id)
    {
        JadwalKerja::findOrFail($id)->delete();
        return back()->with('success', 'Jadwal berhasil dihapus');
    }
}
