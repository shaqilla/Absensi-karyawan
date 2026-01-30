<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanIzinController extends Controller
{
    public function create() {
        return view('karyawan.izin');
    }

    public function store(Request $request) {
        $request->validate([
            'jenis_pengajuan' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'alasan' => 'required',
            'lampiran' => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('public/lampiran');
            $lampiranPath = str_replace('public/', '', $lampiranPath);
        }

        Pengajuan::create([
            'user_id' => Auth::id(),
            'jenis_pengajuan' => $request->jenis_pengajuan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'lampiran' => $lampiranPath,
            'status_approval' => 'pending'
        ]);

        return redirect()->route('karyawan.dashboard')->with('success', 'Pengajuan berhasil dikirim!');
    }
}