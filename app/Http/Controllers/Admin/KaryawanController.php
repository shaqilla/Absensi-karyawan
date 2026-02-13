<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    /**
     * 1. Menampilkan Khusus Karyawan
     */
    public function indexKaryawan(Request $request)
    {
        $departemenId = $request->departemen_id;
        $departemens = Departemen::all();

        // Query: Hanya ambil yang rolenya 'karyawan'
        $query = Karyawan::with(['user', 'departemen'])->whereHas('user', function($q) {
            $q->where('role', 'karyawan');
        });

        if ($departemenId) {
            $query->where('departemen_id', $departemenId);
        }

        $karyawans = $query->get();
        $totalFiltered = $karyawans->count();
        $title = "Data Karyawan"; // Judul dinamis untuk Blade

        return view('admin.karyawan.index', compact('karyawans', 'departemens', 'totalFiltered', 'title'));
    }

    /**
     * 2. Menampilkan Khusus Admin
     */
    public function indexAdmin()
    {
        $departemens = Departemen::all();

        // Query: Hanya ambil yang rolenya 'admin'
        $karyawans = Karyawan::with(['user', 'departemen'])->whereHas('user', function($q) {
            $q->where('role', 'admin');
        })->get();

        $totalFiltered = $karyawans->count();
        $title = "Data Administrator"; // Judul dinamis untuk Blade

        return view('admin.karyawan.index', compact('karyawans', 'departemens', 'totalFiltered', 'title'));
    }

    /**
     * Tampilan Form Tambah (Tetap satu form untuk semua role)
     */
    public function create()
    {
        $departemens = Departemen::all();
        return view('admin.karyawan.create', compact('departemens'));
    }

    /**
     * Proses Simpan User & Karyawan
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,karyawan',
            'nip' => 'required|unique:karyawans,nip',
            'jabatan' => 'required',
            'departemen_id' => 'required|exists:departemens,id',
            'alamat' => 'required|string',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            Karyawan::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'jabatan' => $request->jabatan,
                'departemen_id' => $request->departemen_id,
                'tanggal_masuk' => now(),
                'alamat' => $request->alamat,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            DB::commit();
            
            // Redirect sesuai role yang baru dibuat agar admin tidak bingung
            $route = ($request->role == 'admin') ? 'admin.users.admin' : 'admin.users.karyawan';
            return redirect()->route($route)->with('success', 'User berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $karyawan = Karyawan::with('user')->findOrFail($id);
        $departemens = Departemen::all();
        return view('admin.karyawan.edit', compact('karyawan', 'departemens'));
    }

    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $user = User::findOrFail($karyawan->user_id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nip' => 'required|unique:karyawans,nip,' . $karyawan->id,
            'role' => 'required|in:admin,karyawan',
            'departemen_id' => 'required|exists:departemens,id',
            'jabatan' => 'required',
            'alamat' => 'required|string',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
        ]);

        DB::beginTransaction();
        try {
            $user->nama = $request->nama;
            $user->email = $request->email;
            $user->role = $request->role;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            $karyawan->update([
                'nip' => $request->nip,
                'jabatan' => $request->jabatan,
                'departemen_id' => $request->departemen_id,
                'alamat' => $request->alamat,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            DB::commit();

            $route = ($request->role == 'admin') ? 'admin.users.admin' : 'admin.users.karyawan';
            return redirect()->route($route)->with('success', 'Data berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $user = User::findOrFail($karyawan->user_id);

        DB::beginTransaction();
        try {
            // Hapus data terkait dulu agar tidak error Foreign Key
            \App\Models\Presensi::where('user_id', $user->id)->delete();
            \App\Models\JadwalKerja::where('user_id', $user->id)->delete();
            \App\Models\Pengajuan::where('user_id', $user->id)->delete();

            $karyawan->delete();
            $user->delete();

            DB::commit();
            return back()->with('success', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}