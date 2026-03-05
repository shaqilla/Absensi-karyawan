@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Edit Data Pegawai</h1>
            <p class="text-gray-500 text-xs md:text-sm italic">Perbarui informasi akun dan profil karyawan.</p>
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
        <form action="{{ route('admin.karyawan.update', $karyawan->id) }}" method="POST" class="p-6 md:p-10">
            @csrf
            @method('PUT')

            <!-- GRID: 1 KOLOM DI HP, 2 KOLOM DI LAPTOP -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

                <!-- BAGIAN KIRI: AKSES LOGIN -->
                <div class="space-y-5">
                    <h2 class="text-indigo-600 font-black text-[10px] md:text-xs uppercase tracking-[0.2em] border-b border-indigo-50 pb-3 flex items-center">
                        <i class="fas fa-key mr-2"></i> Informasi Login
                    </h2>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Role Akses</label>
                        <select name="role" class="w-full border-gray-200 rounded-xl p-3 md:p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 text-sm" required>
                            <option value="karyawan" {{ old('role', $karyawan->user->role) == 'karyawan' ? 'selected' : '' }}>Karyawan (Absensi)</option>
                            <option value="admin" {{ old('role', $karyawan->user->role) == 'admin' ? 'selected' : '' }}>Admin (Full Akses)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama', $karyawan->user->nama) }}" class="w-full border-gray-200 rounded-xl p-3 md:p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-sm" required>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Email Login</label>
                        <input type="email" name="email" value="{{ old('email', $karyawan->user->email) }}" class="w-full border-gray-200 rounded-xl p-3 md:p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-sm" required>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Password Baru</label>
                        <input type="password" name="password" class="w-full border-gray-200 rounded-xl p-3 md:p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-sm" placeholder="Kosongkan jika tidak ingin ganti">
                        <p class="text-[9px] text-gray-400 mt-1 italic">*Isi hanya jika ingin mereset password.</p>
                    </div>
                </div>

                <!-- BAGIAN KANAN: DETAIL PROFIL -->
                <div class="space-y-5">
                    <h2 class="text-indigo-600 font-black text-[10px] md:text-xs uppercase tracking-[0.2em] border-b border-indigo-50 pb-3 flex items-center">
                        <i class="fas fa-id-card mr-2"></i> Detail Profil
                    </h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">NIP</label>
                            <input type="text" name="nip" value="{{ old('nip', $karyawan->nip) }}" class="w-full border-gray-200 rounded-xl p-3 md:p-4 border outline-none font-bold text-sm" required>
                        </div>
                        <div>
                            <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Gender</label>
                            <select name="jenis_kelamin" class="w-full border-gray-200 rounded-xl p-3 md:p-4 border outline-none font-bold text-sm" required>
                                <option value="laki-laki" {{ old('jenis_kelamin', $karyawan->jenis_kelamin) == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="perempuan" {{ old('jenis_kelamin', $karyawan->jenis_kelamin) == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Departemen</label>
                        <select name="departemen_id" class="w-full border-gray-200 rounded-xl p-3 md:p-4 border outline-none font-bold text-sm" required>
                            @foreach($departemens as $d)
                            <option value="{{ $d->id }}" {{ old('departemen_id', $karyawan->departemen_id) == $d->id ? 'selected' : '' }}>{{ $d->nama_departemen }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan', $karyawan->jabatan) }}" class="w-full border-gray-200 rounded-xl p-3 md:p-4 border outline-none font-bold text-sm" required>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Alamat Lengkap</label>
                        <textarea name="alamat" rows="3" class="w-full border-gray-200 rounded-xl p-3 md:p-4 border outline-none font-bold text-sm" required>{{ old('alamat', $karyawan->alamat) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SIMPAN: FULL WIDTH DI HP -->
            <div class="mt-10 md:mt-12 flex justify-end border-t border-gray-50 pt-8">
                <button type="submit" class="w-full md:w-auto bg-slate-900 text-white px-16 py-4 rounded-2xl font-black hover:bg-black transition shadow-lg uppercase text-xs tracking-widest">
                    Update Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection