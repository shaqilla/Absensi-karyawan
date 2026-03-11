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
    // 1. TAMPILKAN SEMUA DATA KARYAWAN
    public function index(Request $request)
    {
        $departemenId = $request->departemen_id;
        $departemens = Departemen::all();

        // Ditambahkan relasi 'supervisor' agar di tabel kelihatan siapa atasannya
        $query = Karyawan::with(['user', 'departemen', 'supervisor']);

        if ($departemenId) {
            $query->where('departemen_id', $departemenId);
        }

        $karyawans = $query->get();
        $totalFiltered = $karyawans->count();
        $title = "Data Karyawan & Admin";

        return view('admin.karyawan.index', compact('karyawans', 'departemens', 'totalFiltered', 'title'));
    }

    // 2. TAMPILKAN FORM TAMBAH KARYAWAN
    public function create()
    {
        $departemens = Departemen::all();
        // REVISI TO: Ambil semua user yang berpotensi jadi penilai (Atasan)
        $supervisors = User::orderBy('nama', 'asc')->get();

        return view('admin.karyawan.create', compact('departemens', 'supervisors'));
    }

    // 3. SIMPAN KARYAWAN BARU
    public function store(Request $request)
    {
        $request->validate([
            'nama'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:6',
            'role'          => 'required|in:admin,karyawan',
            'nip'           => 'required|unique:karyawans,nip',
            'jabatan'       => 'required',
            'departemen_id' => 'required|exists:departemens,id',
            'alamat'        => 'required|string',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'supervisor_id' => 'nullable|exists:users,id', // REVISI TO: Validasi ID Atasan
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'nama'     => $request->nama,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role,
            ]);

            Karyawan::create([
                'user_id'       => $user->id,
                'nip'           => $request->nip,
                'jabatan'       => $request->jabatan,
                'departemen_id' => $request->departemen_id,
                'tanggal_masuk' => now(),
                'alamat'        => $request->alamat,
                'jenis_kelamin' => $request->jenis_kelamin,
                'supervisor_id' => $request->supervisor_id, // REVISI TO: Simpan Atasannya
            ]);

            DB::commit();
            return redirect()->route('admin.karyawan.index')->with('success', 'User & Atasan berhasil disetel!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // 4. TAMPILKAN FORM EDIT
    public function edit($id)
    {
        $karyawan = Karyawan::with('user')->findOrFail($id);
        $departemens = Departemen::all();
        // REVISI TO: Ambil data penilai untuk dropdown edit
        $supervisors = User::where('id', '!=', $karyawan->user_id)->get(); // Jangan nilai diri sendiri

        return view('admin.karyawan.edit', compact('karyawan', 'departemens', 'supervisors'));
    }

    // 5. SIMPAN PERUBAHAN
    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $user = User::findOrFail($karyawan->user_id);

        $request->validate([
            'nama'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'nip'           => 'required|unique:karyawans,nip,' . $karyawan->id,
            'role'          => 'required|in:admin,karyawan',
            'departemen_id' => 'required|exists:departemens,id',
            'jabatan'       => 'required',
            'alamat'        => 'required|string',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'supervisor_id' => 'nullable|exists:users,id', // REVISI TO: Validasi Atasan
        ]);

        DB::beginTransaction();
        try {
            $user->nama  = $request->nama;
            $user->email = $request->email;
            $user->role  = $request->role;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            $karyawan->update([
                'nip'           => $request->nip,
                'jabatan'       => $request->jabatan,
                'departemen_id' => $request->departemen_id,
                'alamat'        => $request->alamat,
                'jenis_kelamin' => $request->jenis_kelamin,
                'supervisor_id' => $request->supervisor_id, // REVISI TO: Update Atasan
            ]);

            DB::commit();
            return redirect()->route('admin.karyawan.index')->with('success', 'Data berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // 6. HAPUS DATA
    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $user = User::findOrFail($karyawan->user_id);

        DB::beginTransaction();
        try {
            // REVISI TO: Jika admin ini dihapus, bawahan-bawahannya jadi gak punya atasan
            Karyawan::where('supervisor_id', $user->id)->update(['supervisor_id' => null]);

            \App\Models\Presensi::where('user_id', $user->id)->delete();
            \App\Models\JadwalKerja::where('user_id', $user->id)->delete();
            \App\Models\Pengajuan::where('user_id', $user->id)->delete();

            // Hapus data penilaian jika ada
            \App\Models\Assessment::where('evaluatee_id', $user->id)->orWhere('evaluator_id', $user->id)->delete();

            $karyawan->delete();
            $user->delete();

            DB::commit();
            return redirect()->route('admin.karyawan.index')->with('success', 'Data berhasil dihapus total!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
