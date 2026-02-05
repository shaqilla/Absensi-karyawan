@extends('layouts.admin')

@section('content')
<div class="w-full max-w-4xl">
    <div class="mb-10">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Konfigurasi Lokasi Kantor</h1>
        <p class="text-gray-500 text-sm italic">Tentukan titik koordinat GPS dan radius aman absensi.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-2xl shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.lokasi.update') }}" method="POST" class="p-10">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Gedung/Kantor</label>
                        <input type="text" name="nama_kantor" value="{{ $lokasi->nama_kantor ?? '' }}" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold" placeholder="Contoh: Gedung Zenclock Lt. 5" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Radius Aman (Meter)</label>
                        <input type="number" name="radius" value="{{ $lokasi->radius ?? '50' }}" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold" placeholder="Contoh: 50" required>
                        <p class="text-[9px] text-gray-400 mt-2 italic">*Karyawan tidak bisa absen jika jaraknya melebihi angka ini.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Latitude Kantor</label>
                        <input type="text" name="latitude" value="{{ $lokasi->latitude ?? '' }}" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-sm" placeholder="-6.12345678" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Longitude Kantor</label>
                        <input type="text" name="longitude" value="{{ $lokasi->longitude ?? '' }}" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-sm" placeholder="106.12345678" required>
                    </div>
                </div>

            </div>

            <div class="mt-10 flex flex-col md:flex-row items-center justify-between border-t border-gray-50 pt-10 gap-6">
                <div class="flex items-center text-amber-600 bg-amber-50 px-4 py-2 rounded-xl border border-amber-100">
                    <i class="fas fa-info-circle mr-3"></i>
                    <p class="text-[10px] font-bold uppercase tracking-tight">Gunakan titik koordinat dari Google Maps secara presisi.</p>
                </div>
                <button type="submit" class="bg-slate-900 text-white px-12 py-4 rounded-2xl font-black hover:bg-black transition shadow-lg uppercase text-sm tracking-widest w-full md:w-auto">
                    Simpan Lokasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection