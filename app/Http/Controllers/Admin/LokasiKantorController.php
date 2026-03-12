<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LokasiKantor;
use Illuminate\Http\Request;

class LokasiKantorController extends Controller
{

    // TAMPILKAN DATA LOKASI KANTOR
    public function index()
    {
        // Ambil baris pertama saja karena lokasi kantor hanya 1
        // first() = ambil 1 data, bukan semua (beda dengan get() yang ambil semua)
        $lokasi = LokasiKantor::first();

        return view('admin.lokasi.index', compact('lokasi'));
    }


    // UPDATE DATA LOKASI KANTOR

    public function update(Request $request)
    {
        // Validasi semua input wajib diisi
        $request->validate([
            'nama_kantor' => 'required',
            'latitude'    => 'required',            // Koordinat garis lintang
            'longitude'   => 'required',            // Koordinat garis bujur
            'radius'      => 'required|numeric',    // Radius dalam meter, harus angka
        ]);

        // updateOrCreate dengan ID yang dikunci ke 1
        // Kalau baris ID 1 sudah ada → update datanya
        // Kalau belum ada → buat baru dengan ID 1
        // Tujuan: memastikan database selalu hanya punya 1 data lokasi kantor
        LokasiKantor::updateOrCreate(
            ['id' => 1],
            [
                'nama_kantor' => $request->nama_kantor,
                'latitude'    => $request->latitude,
                'longitude'   => $request->longitude,
                'radius'      => $request->radius,  // Radius absensi dalam meter
            ]
        );

        // Kembali ke halaman sebelumnya dengan pesan sukses
        return back()->with('success', 'Lokasi kantor berhasil diperbarui!');
    }
}
