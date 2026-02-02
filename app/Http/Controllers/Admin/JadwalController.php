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
        $jadwals = JadwalKerja::with(['user', 'shift'])->get();
        return view('admin.jadwal.index', compact('jadwals'));
    }

    public function create() {
        $users = User::where('role', 'karyawan')->get();
        $shifts = Shift::all();
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        return view('admin.jadwal.create', compact('users', 'shifts', 'hari'));
    }

    public function store(Request $request) {
        $request->validate([
            'user_id' => 'required',
            'shift_id' => 'required',
            'hari' => 'required',
            'status' => 'required',
        ]);

        JadwalKerja::create($request->all());
        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil disetel');
    }
}