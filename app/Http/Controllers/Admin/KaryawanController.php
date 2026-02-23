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
     * 1. Menampilkan Semua User (Admin & Karyawan) dalam satu tabel
     */
    public function index(Request $request)
    {
        $departemenId = $request->departemen_id;
        $departemens = Departemen::all();

        // Query mengambil semua data karyawan tanpa memandang role
        $query = Karyawan::with(['user', 'departemen']);

        // Tetap pertahankan fitur filter departemen
        if ($departemenId) {
            $query->where('departemen_id', $departemenId);
        }

        $karyawans = $query->get();
        $totalFiltered = $karyawans->count();
        $title = "Data Karyawan & Admin"; 

        return view('admin.karyawan.index', compact('karyawans', 'departemens', 'totalFiltered', 'title'));
    }

    /**
     * 2. Tampilkan Form Tambah
     */
    public function create()
    {
        $departemens = Departemen::all();
        return view('admin.karyawan.create', compact('departemens'));
    }

    /**
     * 3. Proses Simpan User & Karyawan
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
            
            // Redirect kembali ke halaman index tunggal
            return redirect()->route('admin.karyawan.index')->with('success', 'User berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * 4. Tampilkan Form Edit
     */
    public function edit($id)
    {
        $karyawan = Karyawan::with('user')->findOrFail($id);
        $departemens = Departemen::all();
        return view('admin.karyawan.edit', compact('karyawan', 'departemens'));
    }

    /**
     * 5. Proses Update Data
     */
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
            // Update User login
            $user->nama = $request->nama;
            $user->email = $request->email;
            $user->role = $request->role;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            // Update Detail Karyawan
            $karyawan->update([
                'nip' => $request->nip,
                'jabatan' => $request->jabatan,
                'departemen_id' => $request->departemen_id,
                'alamat' => $request->alamat,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            DB::commit();

            return redirect()->route('admin.karyawan.index')->with('success', 'Data berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * 6. Hapus User & Karyawan Beserta Semua Data Terkait
     */
    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $user = User::findOrFail($karyawan->user_id);

        DB::beginTransaction();
        try {
            // Hapus semua data yang nyangkut di tabel lain agar tidak error Foreign Key
            \App\Models\Presensi::where('user_id', $user->id)->delete();
            \App\Models\JadwalKerja::where('user_id', $user->id)->delete();
            \App\Models\Pengajuan::where('user_id', $user->id)->delete();

            $karyawan->delete();
            $user->delete();

            DB::commit();
            return redirect()->route('admin.karyawan.index')->with('success', 'Data berhasil dihapus total!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}