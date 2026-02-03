@extends('layouts.karyawan')

@section('content')
<div class="w-full">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Setting Profil</h1>
            <p class="text-gray-500 text-sm">Kelola dan periksa informasi data kepegawaian Anda.</p>
        </div>
        <div class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest">
            Employee Detail
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Card Foto & Dasar -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 text-center">
            <div class="w-32 h-32 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-indigo-50 shadow-inner">
                <i class="fas fa-user-tie text-5xl"></i>
            </div>
            <h2 class="text-xl font-black text-gray-800 uppercase">{{ $user->nama }}</h2>
            <p class="text-indigo-600 font-bold text-sm">{{ $user->karyawan->jabatan }}</p>
            <hr class="my-6 border-gray-50">
            <div class="text-left space-y-3">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-400 font-bold uppercase">Status Akun</span>
                    <span class="text-green-600 font-black uppercase">Aktif</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-400 font-bold uppercase">Role Akses</span>
                    <span class="text-gray-700 font-black uppercase">{{ $user->role }}</span>
                </div>
            </div>
        </div>

        <!-- Card Detail Informasi -->
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 bg-gray-50 border-b border-gray-100">
                <h3 class="font-black text-gray-800 text-xs uppercase tracking-widest">Detail Informasi Pegawai</h3>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Nomor Induk Pegawai (NIP)</label>
                    <p class="text-gray-800 font-bold border-b border-gray-50 pb-2">{{ $user->karyawan->nip }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Unit / Departemen</label>
                    <p class="text-gray-800 font-bold border-b border-gray-50 pb-2">{{ $user->karyawan->departemen->nama_departemen }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Email Perusahaan</label>
                    <p class="text-gray-800 font-bold border-b border-gray-50 pb-2 lowercase">{{ $user->email }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Jenis Kelamin</label>
                    <p class="text-gray-800 font-bold border-b border-gray-50 pb-2 capitalize">{{ $user->karyawan->jenis_kelamin }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Tanggal Masuk</label>
                    <p class="text-gray-800 font-bold border-b border-gray-50 pb-2">{{ date('d F Y', strtotime($user->karyawan->tanggal_masuk)) }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Alamat Tinggal</label>
                    <p class="text-gray-800 font-bold border-b border-gray-50 pb-2">{{ $user->karyawan->alamat ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection