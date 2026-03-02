<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::all();
        return view('admin.shift.index', compact('shifts'));
    }

    public function create()
    {
        return view('admin.shift.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi Ketat
        $request->validate([
            'nama_shift' => 'required|string|max:50',
            'jam_masuk' => 'required', // Format 00:00
            'jam_keluar' => 'required', // Format 00:00
            'toleransi_telat' => 'required|numeric|min:0',
        ]);

        // 2. Simpan Data
        Shift::create([
            'nama_shift' => $request->nama_shift,
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'toleransi_telat' => $request->toleransi_telat,
        ]);

        return redirect()->route('admin.shift.index')->with('success', 'Shift baru berhasil disimpan!');
    }

    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        // Hapus jadwal terkait dulu agar tidak error FK
        \App\Models\JadwalKerja::where('shift_id', $id)->delete();
        $shift->delete();

        return back()->with('success', 'Shift berhasil dihapus!');
    }
}