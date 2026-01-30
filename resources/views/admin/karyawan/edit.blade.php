@extends('layouts.admin')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Edit User & Karyawan</h1>
        <a href="{{ route('admin.karyawan.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Menampilkan Error Validasi jika ada -->
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            <p class="font-bold">Mohon perbaiki kesalahan berikut:</p>
            <ul class="list-disc ml-5 mt-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Route diarahkan ke UPDATE dengan Method PUT -->
        <form action="{{ route('admin.karyawan.update', $karyawan->id) }}" method="POST" class="p-8">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Data Akun (Login) -->
                <div class="space-y-6">
                    <h2 class="text-lg font-bold text-indigo-600 border-b pb-2">Informasi Akun</h2>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Role Akses</label>
                        <select name="role" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" required>
                            <option value="karyawan" {{ $karyawan->user->role == 'karyawan' ? 'selected' : '' }}>Karyawan (Akses Absensi)</option>
                            <option value="admin" {{ $karyawan->user->role == 'admin' ? 'selected' : '' }}>Admin (Akses Full)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama', $karyawan->user->nama) }}" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $karyawan->user->email) }}" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
                        <input type="password" name="password" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" placeholder="Kosongkan jika tidak ingin ganti password">
                        <p class="text-[10px] text-gray-400 mt-1 italic">*Biarkan kosong jika tetap menggunakan password lama</p>
                    </div>
                </div>

                <!-- Data Karyawan -->
                <div class="space-y-6">
                    <h2 class="text-lg font-bold text-indigo-600 border-b pb-2">Detail Karyawan</h2>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">NIP (Nomor Induk Pegawai)</label>
                        <input type="text" name="nip" value="{{ old('nip', $karyawan->nip) }}" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Departemen</label>
                        <select name="departemen_id" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" required>
                            @foreach($departemens as $d)
                                <option value="{{ $d->id }}" {{ $karyawan->departemen_id == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama_departemen }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan', $karyawan->jabatan) }}" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin</label>
                        <div class="flex space-x-6 mt-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="jenis_kelamin" value="laki-laki" class="text-indigo-600" {{ $karyawan->jenis_kelamin == 'laki-laki' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Laki-laki</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="jenis_kelamin" value="perempuan" class="text-indigo-600" {{ $karyawan->jenis_kelamin == 'perempuan' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Perempuan</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                    Update Data User & Karyawan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection