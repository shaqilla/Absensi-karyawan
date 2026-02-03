@extends('layouts.admin')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Tambah User & Karyawan</h1>
            <p class="text-gray-500 text-sm italic">Daftarkan akun login dan profil pegawai baru.</p>
        </div>
        <a href="{{ route('admin.karyawan.index') }}" class="text-gray-500 hover:text-indigo-600 transition font-bold text-sm">
            <i class="fas fa-arrow-left mr-2"></i> KEMBALI
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-2xl shadow-sm">
            <p class="font-bold text-sm">Mohon perbaiki kesalahan berikut:</p>
            <ul class="list-disc ml-5 mt-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.karyawan.store') }}" method="POST" class="p-10">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- BAGIAN KIRI: INFORMASI AKUN -->
                <div class="space-y-6">
                    <h2 class="text-indigo-600 font-black text-xs uppercase tracking-[0.2em] border-b border-indigo-50 pb-3">Informasi Akun Login</h2>
                    
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Role Akses</label>
                        <select name="role" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700" required>
                            <option value="karyawan" {{ old('role') == 'karyawan' ? 'selected' : '' }}>Karyawan (Akses Absensi)</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin (Akses Full)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" placeholder="Contoh: John Doe" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Email Perusahaan</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" placeholder="email@perusahaan.com" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Password Login</label>
                        <input type="password" name="password" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" placeholder="Min. 6 Karakter" required>
                    </div>
                </div>

                <!-- BAGIAN KANAN: DETAIL PROFIL -->
                <div class="space-y-6">
                    <h2 class="text-indigo-600 font-black text-xs uppercase tracking-[0.2em] border-b border-indigo-50 pb-3">Detail Profil Pegawai</h2>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">NIP</label>
                            <input type="text" name="nip" value="{{ old('nip') }}" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" placeholder="123456" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" required>
                                <option value="laki-laki">Laki-laki</option>
                                <option value="perempuan">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Departemen</label>
                        <select name="departemen_id" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" required>
                            <option value="">-- Pilih Unit Kerja --</option>
                            @foreach($departemens as $d)
                                <option value="{{ $d->id }}" {{ old('departemen_id') == $d->id ? 'selected' : '' }}>{{ $d->nama_departemen }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan') }}" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" placeholder="Contoh: Staff IT" required>
                    </div>

                    <!-- TAMBAHAN KOLOM ALAMAT -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Alamat Lengkap</label>
                        <textarea name="alamat" rows="3" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" placeholder="Masukkan alamat tinggal saat ini..." required>{{ old('alamat') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex justify-end border-t border-gray-50 pt-10">
                <button type="submit" class="bg-indigo-600 text-white px-16 py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase text-sm tracking-widest">
                    Simpan User Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection