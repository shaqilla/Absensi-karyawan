<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index() {
        // Ambil semua jadwal beserta data karyawan dan shift-nya
        $jadwals = JadwalKerja::with(['user', 'shift'])->get();
        return view('admin.jadwal.index', compact('jadwals'));
    }

    public function create() {
        $karyawans = User::where('role', 'karyawan')->get();
        $shifts = Shift::all();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        return view('admin.jadwal.create', compact('karyawans', 'shifts', 'hari'));
    }

    public function store(Request $request) {
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

    public function destroy($id) {
        JadwalKerja::findOrFail($id)->delete();
        return back()->with('success', 'Jadwal berhasil dihapus');
    }
}