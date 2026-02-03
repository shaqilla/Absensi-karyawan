@extends('layouts.admin')

@section('content')
<div class="w-full">
    <div class="mb-10">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Setting Profil Administrator</h1>
        <p class="text-gray-500 text-sm italic">Informasi akun dan profil jabatan Anda di sistem.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Card Kiri -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-10 text-center">
            <div class="w-40 h-40 bg-indigo-100 text-indigo-600 rounded-[2.5rem] flex items-center justify-center mx-auto mb-6 border-4 border-indigo-50 shadow-inner">
                <i class="fas fa-user-shield text-6xl"></i>
            </div>
            <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tight">{{ $user->nama }}</h2>
            <p class="text-red-500 font-black text-xs uppercase tracking-[0.2em] mt-2">Level: Super Admin</p>
            
            <div class="mt-10 p-6 bg-gray-50 rounded-3xl text-left space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-black text-gray-400 uppercase">Login Email</span>
                    <span class="text-xs font-bold text-gray-700">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-200 pt-4">
                    <span class="text-[10px] font-black text-gray-400 uppercase">Jabatan</span>
                    <span class="text-xs font-bold text-gray-700">{{ $user->karyawan->jabatan ?? 'Administrator' }}</span>
                </div>
            </div>
        </div>

        <!-- Detail Information -->
        <div class="lg:col-span-2 bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-black text-gray-800 text-xs uppercase tracking-widest">Detail Informasi Akun</h3>
                <i class="fas fa-id-card text-gray-300"></i>
            </div>
            <div class="p-10 grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-1">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Nomor Induk Admin (NIP)</label>
                    <p class="text-lg font-bold text-gray-800">{{ $user->karyawan->nip ?? 'N/A' }}</p>
                </div>
                <div class="space-y-1">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Departemen Pengelola</label>
                    <p class="text-lg font-bold text-gray-800">{{ $user->karyawan->departemen->nama_departemen ?? 'Management' }}</p>
                </div>
                <div class="space-y-1">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis Kelamin</label>
                    <p class="text-lg font-bold text-gray-800 capitalize">{{ $user->karyawan->jenis_kelamin ?? '-' }}</p>
                </div>
                <div class="space-y-1">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Terdaftar Sejak</label>
                    <p class="text-lg font-bold text-gray-800">{{ date('d F Y', strtotime($user->created_at)) }}</p>
                </div>
            </div>
            
            <div class="p-10 bg-indigo-50/50 border-t border-indigo-50">
                <p class="text-[10px] text-indigo-400 font-bold uppercase mb-2 italic">Catatan Sistem:</p>
                <p class="text-xs text-indigo-900 leading-relaxed">Akun Administrator memiliki hak akses penuh untuk mengelola data karyawan, menyetujui izin/lembur, dan melihat laporan seluruh departemen.</p>
            </div>
        </div>
    </div>
</div>
@endsection