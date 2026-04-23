@extends('layouts.admin')

@section('content')
<div class="w-full pb-10 text-black">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Tambah Data Pegawai</h1>
            <p class="text-gray-500 text-xs md:text-sm italic font-medium">Daftarkan akun dan profil karyawan baru ke sistem.</p>
        </div>
        <a href="{{ route('admin.karyawan.index') }}" class="flex items-center text-gray-400 hover:text-indigo-600 transition font-bold text-xs uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- NOTIFIKASI ERROR -->
    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-xl shadow-sm">
        <ul class="list-disc ml-5 text-[10px] md:text-xs font-bold uppercase">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl md:rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.karyawan.store') }}" method="POST" class="p-6 md:p-10">
            @csrf

            <!-- GRID: 1 KOLOM DI HP, 2 KOLOM DI LAPTOP -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

                <!-- BAGIAN KIRI: AKSES LOGIN -->
                <div class="space-y-5">
                    <h2 class="text-indigo-600 font-black text-[10px] md:text-xs uppercase tracking-[0.2em] border-b border-indigo-50 pb-3 flex items-center">
                        <i class="fas fa-key mr-2"></i> Informasi Akun & Login
                    </h2>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Role / Jabatan Sistem</label>
                        <select name="role" class="w-full border-gray-100 rounded-2xl p-4 bg-gray-50 font-black text-indigo-600 focus:ring-4 focus:ring-indigo-500/10 outline-none transition cursor-pointer" required>
                            <option value="karyawan">KARYAWAN </option>
                            <option value="operator">OPERATOR </option>
                            <option value="pimpinan">PIMPINAN </option>
                            <option value="admin">ADMINISTRATOR </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" class="w-full border-gray-100 rounded-xl p-3 md:p-4 bg-gray-50 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-sm" placeholder="Masukkan nama sesuai KTP" required>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Email Login</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border-gray-100 rounded-xl p-3 md:p-4 bg-gray-50 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-sm" placeholder="nama@email.com" required>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Password</label>
                        <input type="password" name="password" class="w-full border-gray-100 rounded-xl p-3 md:p-4 bg-gray-50 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-sm" placeholder="Minimal 8 karakter" required>
                    </div>
                </div>

                <!-- BAGIAN KANAN: DETAIL PROFIL -->
                <div class="space-y-5">
                    <h2 class="text-indigo-600 font-black text-[10px] md:text-xs uppercase tracking-[0.2em] border-b border-indigo-50 pb-3 flex items-center">
                        <i class="fas fa-id-card mr-2"></i> Detail Personel
                    </h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">NIP / ID</label>
                            <input type="text" name="nip" value="{{ old('nip') }}" class="w-full border-gray-200 rounded-xl p-3 md:p-4 border outline-none font-bold text-sm" placeholder="Nomor Induk" required>
                        </div>
                        <div>
                            <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Gender</label>
                            <select name="jenis_kelamin" class="w-full border-gray-200 rounded-xl p-3 md:p-4 border outline-none font-bold text-sm" required>
                                <option value="laki-laki" {{ old('jenis_kelamin') == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="perempuan" {{ old('jenis_kelamin') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Departemen / Unit</label>
                        <select name="departemen_id" class="w-full border-gray-200 rounded-xl p-3 md:p-4 border outline-none font-bold text-sm" required>
                            <option value="">-- Pilih Departemen --</option>
                            @foreach($departemens as $d)
                            <option value="{{ $d->id }}" {{ old('departemen_id') == $d->id ? 'selected' : '' }}>{{ $d->nama_departemen }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan') }}" class="w-full border-gray-200 rounded-xl p-3 md:p-4 border outline-none font-bold text-sm" placeholder="Contoh: Staff IT" required>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Alamat Tinggal</label>
                        <textarea name="alamat" rows="3" class="w-full border-gray-200 rounded-xl p-3 md:p-4 border outline-none font-bold text-sm" placeholder="Alamat lengkap saat ini..." required>{{ old('alamat') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SIMPAN -->
            <div class="mt-10 md:mt-12 flex justify-end border-t border-gray-50 pt-8">
                <button type="submit" class="w-full md:w-auto bg-indigo-600 text-white px-16 py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 uppercase text-xs tracking-widest active:scale-95">
                    Simpan User Baru <i class="fas fa-save ml-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
