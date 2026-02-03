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
    public function index()
    {
        $karyawans = Karyawan::with(['user', 'departemen'])->get();
        return view('admin.karyawan.index', compact('karyawans'));
    }

    public function create()
    {
        $departemens = Departemen::all();
        return view('admin.karyawan.create', compact('departemens'));
    }

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
            'alamat' => 'required|string', // Validasi Alamat
            'alamat' => 'required|string', // Validasi Alamat
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
                'alamat' => $request->alamat ?? '-',
                'jenis_kelamin' => $request->jenis_kelamin ?? 'laki-laki',
            ]);

            DB::commit();
            return redirect()->route('admin.karyawan.index')->with('success', 'User berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    // --- TAMBAHKAN MULAI DARI SINI ---

    // 3. Menampilkan Halaman Edit
    public function edit($id)
    {
        $karyawan = Karyawan::with('user')->findOrFail($id);
        $departemens = Departemen::all();
        return view('admin.karyawan.edit', compact('karyawan', 'departemens'));
    }

    // 4. Memproses Perubahan Data (Update)
    public function update(Request $request, $id)
{
    $karyawan = Karyawan::findOrFail($id);
    $user = User::findOrFail($karyawan->user_id);

    // VALIDASI (PENTING: Perhatikan penulisan ignore ID-nya)
    $request->validate([
        'nama' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'nip' => 'required|unique:karyawans,nip,' . $karyawan->id,
        'role' => 'required|in:admin,karyawan',
        'departemen_id' => 'required|exists:departemens,id',
        'jabatan' => 'required',
        'alamat' => 'required|string', // Pastikan alamat divalidasi
        'jenis_kelamin' => 'required|in:laki-laki,perempuan',
        
    ]);

    DB::beginTransaction();
    try {
        // 1. Update Data User
        $user->nama = $request->nama;
        $user->email = $request->email;
        $user->role = $request->role; // Mengupdate Role
        
        // Update password jika diisi saja
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // 2. Update Data Karyawan
        $karyawan->update([
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
            'departemen_id' => $request->departemen_id,
            'alamat' => $request->alamat,
            'alamat' => $request->alamat, // Perbarui alamat di sini
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        DB::commit();
        
        // Redirect ke index dengan pesan sukses
        return redirect()->route('admin.karyawan.index')->with('success', 'Data Karyawan & Role berhasil diperbarui!');

    } catch (\Exception $e) {
        DB::rollback();
        // Kembali ke halaman sebelumnya dengan pesan error asli dari sistem
        return back()->withErrors(['system_error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
    }
}

    // 5. Menghapus Karyawan & Akun User
    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $user = User::findOrFail($karyawan->user_id);

        $karyawan->delete();
        $user->delete();

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan dan akun login berhasil dihapus!');
    }
}