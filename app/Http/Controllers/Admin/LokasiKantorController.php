<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LokasiKantor;
use Illuminate\Http\Request;

class LokasiKantorController extends Controller
{
    public function index()
    {
        // Ambil data kantor pertama (karena biasanya kantor cuma 1)
        $lokasi = LokasiKantor::first();
        return view('admin.lokasi.index', compact('lokasi'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_kantor' => 'required',
            'latitude'    => 'required',
            'longitude'   => 'required',
            'radius'      => 'required|numeric',
        ]);

        // Simpan atau Update data
        LokasiKantor::updateOrCreate(
            ['id' => 1], // Selalu update baris ID 1
            [
                'nama_kantor' => $request->nama_kantor,
                'latitude'    => $request->latitude,
                'longitude'   => $request->longitude,
                'radius'      => $request->radius,
            ]
        );

        return back()->with('success', 'Lokasi kantor berhasil diperbarui!');
    }
}