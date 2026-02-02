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
        // PERBAIKAN VALIDASI: Tambahkan 'lembur'
        $request->validate([
            'jenis_pengajuan' => 'required|in:cuti,sakit,izin,lembur', // Pastikan lembur masuk di sini
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'lampiran' => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            // Gunakan nama file yang unik agar tidak tertimpa
            $fileName = time() . '_' . $request->file('lampiran')->getClientOriginalName();
            $request->file('lampiran')->move(public_path('uploads/lampiran'), $fileName);
            $lampiranPath = $fileName;
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

        return redirect()->route('karyawan.dashboard')->with('success', 'Pengajuan ' . $request->jenis_pengajuan . ' berhasil dikirim!');
    }
}