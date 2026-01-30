<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanController extends Controller
{
    public function index()
    {
        // Ambil pengajuan yang belum di-acc (pending) di urutan atas
        $pengajuans = Pengajuan::with('karyawan')->orderBy('status_approval', 'asc')->get();
        return view('admin.pengajuan.index', compact('pengajuans'));
    }

    public function updateStatus(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $pengajuan->update([
            'status_approval' => $request->status, // 'disetujui' atau 'ditolak'
            'approved_by' => Auth::id(),
            'catatan_admin' => $request->catatan
        ]);

        return back()->with('success', 'Status pengajuan berhasil diperbarui!');
    }
}