<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;   // Untuk enkripsi password
use Illuminate\Support\Facades\DB;     // Untuk transaksi database

class KaryawanController extends Controller
{
    // =========================================
    // 1. TAMPILKAN SEMUA DATA KARYAWAN
    // =========================================
    public function index(Request $request)
    {
        // Ambil filter departemen dari URL (kalau ada)
        // Contoh: /karyawan?departemen_id=2
        $departemenId = $request->departemen_id;

        // Ambil semua departemen untuk pilihan filter dropdown
        $departemens = Departemen::all();

        // Query dasar: ambil semua karyawan beserta relasi user & departemen
        $query = Karyawan::with(['user', 'departemen']);

        // Kalau ada filter departemen, tambahkan kondisi where
        if ($departemenId) {
            $query->where('departemen_id', $departemenId);
        }

        // Eksekusi query dan simpan hasilnya
        $karyawans = $query->get();

        // Hitung total data yang ditampilkan (setelah filter)
        $totalFiltered = $karyawans->count();

        $title = "Data Karyawan & Admin";

        return view('admin.karyawan.index', compact('karyawans', 'departemens', 'totalFiltered', 'title'));
    }

    // =========================================
    // 2. TAMPILKAN FORM TAMBAH KARYAWAN BARU
    // =========================================
    public function create()
    {
        // Ambil semua departemen untuk pilihan dropdown di form
        $departemens = Departemen::all();
        return view('admin.karyawan.create', compact('departemens'));
    }

    // =========================================
    // 3. SIMPAN KARYAWAN BARU KE DATABASE
    // =========================================
    public function store(Request $request)
    {
        // Validasi semua input dari form
        $request->validate([
            'nama'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',     // Email tidak boleh duplikat
            'password'      => 'required|min:6',                        // Password minimal 6 karakter
            'role'          => 'required|in:admin,karyawan',            // Role hanya boleh admin atau karyawan
            'nip'           => 'required|unique:karyawans,nip',         // NIP tidak boleh duplikat
            'jabatan'       => 'required',
            'departemen_id' => 'required|exists:departemens,id',        // Departemen harus ada di database
            'alamat'        => 'required|string',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
        ]);

        // Mulai transaksi database
        // Tujuan: kalau salah satu gagal, semua dibatalkan (tidak ada data nanggung)
        DB::beginTransaction();
        try {
            // LANGKAH 1: Buat akun login di tabel users
            $user = User::create([
                'nama'     => $request->nama,
                'email'    => $request->email,
                'password' => Hash::make($request->password), // Password dienkripsi dulu sebelum disimpan
                'role'     => $request->role,
            ]);

            // LANGKAH 2: Buat data detail karyawan di tabel karyawans
            // user_id diambil dari user yang baru dibuat di atas
            Karyawan::create([
                'user_id'       => $user->id,
                'nip'           => $request->nip,
                'jabatan'       => $request->jabatan,
                'departemen_id' => $request->departemen_id,
                'tanggal_masuk' => now(),   // Otomatis isi tanggal hari ini
                'alamat'        => $request->alamat,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            // Kalau semua berhasil → commit (simpan permanen ke database)
            DB::commit();

            return redirect()->route('admin.karyawan.index')->with('success', 'User berhasil ditambahkan!');

        } catch (\Exception $e) {
            // Kalau ada error → rollback (batalkan semua perubahan)
            DB::rollback();
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    // =========================================
    // 4. TAMPILKAN FORM EDIT KARYAWAN
    // =========================================
    public function edit($id)
    {
        // Cari data karyawan beserta user-nya, kalau tidak ada → error 404
        $karyawan = Karyawan::with('user')->findOrFail($id);

        // Ambil semua departemen untuk dropdown
        $departemens = Departemen::all();

        return view('admin.karyawan.edit', compact('karyawan', 'departemens'));
    }

    // =========================================
    // 5. SIMPAN PERUBAHAN DATA KARYAWAN
    // =========================================
    public function update(Request $request, $id)
    {
        // Cari data karyawan dan user terkait
        $karyawan = Karyawan::findOrFail($id);
        $user = User::findOrFail($karyawan->user_id);

        $request->validate([
            'nama'          => 'required|string|max:255',
            // unique:users,email,{$user->id} = boleh pakai email yang sama SELAMA itu emailnya sendiri
            'email'         => 'required|email|unique:users,email,' . $user->id,
            // Sama seperti email, NIP boleh sama kalau itu NIP-nya sendiri
            'nip'           => 'required|unique:karyawans,nip,' . $karyawan->id,
            'role'          => 'required|in:admin,karyawan',
            'departemen_id' => 'required|exists:departemens,id',
            'jabatan'       => 'required',
            'alamat'        => 'required|string',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
        ]);

        DB::beginTransaction();
        try {
            // Update data akun login (tabel users)
            $user->nama  = $request->nama;
            $user->email = $request->email;
            $user->role  = $request->role;

            // Password hanya diupdate kalau diisi (kalau kosong, biarkan password lama)
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            // Update data detail karyawan (tabel karyawans)
            $karyawan->update([
                'nip'           => $request->nip,
                'jabatan'       => $request->jabatan,
                'departemen_id' => $request->departemen_id,
                'alamat'        => $request->alamat,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            DB::commit();

            return redirect()->route('admin.karyawan.index')->with('success', 'Data berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // =========================================
    // 6. HAPUS KARYAWAN BESERTA SEMUA DATA TERKAIT
    // =========================================
    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $user = User::findOrFail($karyawan->user_id);

        DB::beginTransaction();
        try {
            // Hapus dulu semua data di tabel lain yang berelasi dengan user ini
            // Wajib dihapus duluan karena ada Foreign Key (kalau tidak → error)
            \App\Models\Presensi::where('user_id', $user->id)->delete();    // Hapus data presensi
            \App\Models\JadwalKerja::where('user_id', $user->id)->delete(); // Hapus data jadwal
            \App\Models\Pengajuan::where('user_id', $user->id)->delete();   // Hapus data pengajuan izin

            // Baru hapus data karyawan dan user-nya
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