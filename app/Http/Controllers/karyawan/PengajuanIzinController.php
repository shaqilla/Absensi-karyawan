<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanIzinController extends Controller
{
    /**
     * TAMPILAN 1: Riwayat Pengajuan (Agar karyawan tahu statusnya)
     */
    public function index()
    {
        $userId = Auth::id();
        
        // Mengambil semua pengajuan milik karyawan ini
        $pengajuans = Pengajuan::where('user_id', $userId)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('karyawan.izin_index', compact('pengajuans'));
    }

    /**
     * TAMPILAN 2: Form Pengajuan
     */
    public function create() 
    {
        return view('karyawan.izin');
    }

    /**
     * LOGIC: Simpan Pengajuan
     */
    public function store(Request $request) 
    {
        // 1. Validasi Input
        $request->validate([
            'jenis_pengajuan' => 'required|in:cuti,sakit,izin,lembur', 
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'lampiran' => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
        ]);

        // 2. Handle Upload File
        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Masuk ke folder public/uploads/lampiran agar bisa dilihat Admin
            $file->move(public_path('uploads/lampiran'), $fileName);
            $lampiranPath = $fileName; 
        }

        // 3. Simpan ke Database
        Pengajuan::create([
            'user_id' => Auth::id(),
            'jenis_pengajuan' => $request->jenis_pengajuan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'lampiran' => $lampiranPath,
            'status_approval' => 'pending'
        ]);

        // 4. Balik ke halaman riwayat (index) dengan pesan sukses
        return redirect()->route('karyawan.izin.index')->with('success', 'Pengajuan ' . $request->jenis_pengajuan . ' Anda telah dikirim dan menunggu persetujuan admin.');
    }
}