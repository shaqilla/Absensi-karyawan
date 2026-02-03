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
        $request->validate([
            'nama_shift' => 'required|string|max:50',
            'jam_masuk' => 'required',
            'jam_keluar' => 'required',
            'toleransi_telat' => 'required|numeric|min:0',
        ]);

        Shift::create($request->all());

        return redirect()->route('admin.shift.index')->with('success', 'Shift baru berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        Shift::findOrFail($id)->delete();
        return back()->with('success', 'Shift berhasil dihapus!');
    }
}