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
    // 1. Daftar Karyawan & Filter
    public function index(Request $request)
    {
        $departemenId = $request->departemen_id;
        $departemens = Departemen::all();
        $query = Karyawan::with(['user', 'departemen']);

        if ($departemenId) {
            $query->where('departemen_id', $departemenId);
        }

        $karyawans = $query->get();
        $totalFiltered = $karyawans->count();

        return view('admin.karyawan.index', compact('karyawans', 'departemens', 'totalFiltered'));
    }

    // 2. Tampilkan Form Tambah
    public function create()
    {
        $departemens = Departemen::all();
        return view('admin.karyawan.create', compact('departemens'));
    }

    // 3. Proses Simpan User & Karyawan
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
            return redirect()->route('admin.karyawan.index')->with('success', 'User berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    // 4. Tampilkan Form Edit
    public function edit($id)
    {
        $karyawan = Karyawan::with('user')->findOrFail($id);
        $departemens = Departemen::all();
        return view('admin.karyawan.edit', compact('karyawan', 'departemens'));
    }

    // 5. Proses Update Data
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
            // Update User
            $user->nama = $request->nama;
            $user->email = $request->email;
            $user->role = $request->role;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            // Update Karyawan
            $karyawan->update([
                'nip' => $request->nip,
                'jabatan' => $request->jabatan,
                'departemen_id' => $request->departemen_id,
                'alamat' => $request->alamat,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            DB::commit();
            return redirect()->route('admin.karyawan.index')->with('success', 'Data Karyawan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // 6. Hapus Data (SUDAH DIPERBAIKI)
    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $user = User::findOrFail($karyawan->user_id);

        DB::beginTransaction();
        try {
            // Matikan pengecekan foreign key
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $karyawan->delete();
            $user->delete();

            // Hidupkan kembali
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::commit();
            return redirect()->route('admin.karyawan.index')->with('success', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return redirect()->route('admin.karyawan.index')->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}