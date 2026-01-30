@extends('layouts.admin')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Tambah User Baru</h1>
        <a href="{{ route('admin.karyawan.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
        </a>
    </div>

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
        <form action="{{ route('admin.karyawan.store') }}" method="POST" class="p-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Data Akun (Login) -->
                <div class="space-y-6">
                    <h2 class="text-lg font-bold text-indigo-600 border-b pb-2">Informasi Akun</h2>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Role Akses</label>
                        <select name="role" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" required>
                            <option value="karyawan" {{ old('role') == 'karyawan' ? 'selected' : '' }}>Karyawan (Akses Absensi)</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin (Akses Full)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" placeholder="Masukkan nama lengkap..." required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" placeholder="email@perusahaan.com" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" required>
                    </div>
                </div>

                <!-- Data Karyawan -->
                <div class="space-y-6">
                    <h2 class="text-lg font-bold text-indigo-600 border-b pb-2">Detail Karyawan</h2>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">NIP (Nomor Induk Pegawai)</label>
                        <input type="text" name="nip" value="{{ old('nip') }}" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" placeholder="Contoh: 123456" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Departemen</label>
                        <select name="departemen_id" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" required>
                            <option value="">-- Pilih Departemen --</option>
                            @foreach($departemens as $d)
                                <option value="{{ $d->id }}" {{ old('departemen_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama_departemen }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan') }}" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" placeholder="Contoh: Senior Manager" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin</label>
                        <div class="flex space-x-6 mt-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="jenis_kelamin" value="laki-laki" class="text-indigo-600" checked>
                                <span class="ml-2 text-sm text-gray-700">Laki-laki</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="jenis_kelamin" value="perempuan" class="text-indigo-600">
                                <span class="ml-2 text-sm text-gray-700">Perempuan</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                    Simpan Data User & Karyawan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection