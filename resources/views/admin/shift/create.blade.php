@extends('layouts.admin')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-gray-800 uppercase tracking-tighter">Tambah Shift Kerja</h1>
        <a href="{{ route('admin.shift.index') }}" class="text-gray-500 hover:text-indigo-600 transition font-bold text-sm">
            <i class="fas fa-arrow-left mr-2"></i> KEMBALI
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.shift.store') }}" method="POST" class="p-10">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Info Dasar -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Shift</label>
                        <input type="text" name="nama_shift" placeholder="Contoh: Shift Pagi" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Toleransi Telat (Menit)</label>
                        <input type="number" name="toleransi_telat" placeholder="Contoh: 15" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" required>
                    </div>
                </div>

                <!-- Waktu -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Jam Masuk</label>
                        <input type="time" name="jam_masuk" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Jam Keluar</label>
                        <input type="time" name="jam_keluar" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold" required>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-12 py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase tracking-widest text-sm">
                    Simpan Shift
                </button>
            </div>
        </form>
    </div>
</div>
@endsection