<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        // 'karyawan' merujuk ke fungsi karyawan() di model Pengajuan (User)
        // 'karyawan.karyawan' merujuk ke detail di model User (NIP, Jabatan, dll)
        $query = Pengajuan::with(['karyawan.karyawan']);

        if ($search) {
            $query->whereHas('karyawan', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status_approval', $status);
        }

        $pengajuans = Pengajuan::with(['karyawan.karyawan'])->orderBy('created_at', 'desc')->get();

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