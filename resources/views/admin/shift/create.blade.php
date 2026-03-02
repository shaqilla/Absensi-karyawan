@extends('layouts.admin')

@section('content')
<div class="w-full relative">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Tambah Shift Kerja</h1>
            <p class="text-gray-500 text-sm">Klik pada kotak jam untuk mengatur waktu.</p>
        </div>
        <a href="{{ route('admin.shift.index') }}" class="bg-white border border-gray-200 text-gray-400 px-6 py-3 rounded-2xl font-bold hover:bg-gray-50 transition text-xs uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-visible relative z-30"> <!-- Overflow visible & z-30 -->
        <form action="{{ route('admin.shift.store') }}" method="POST" class="p-10">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- INFORMASI SHIFT -->
                <div class="space-y-6">
                    <h2 class="text-indigo-600 font-black text-xs uppercase tracking-[0.2em] border-b pb-3">1. Detail Shift</h2>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Shift</label>
                        <input type="text" name="nama_shift" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 bg-gray-50" placeholder="Pagi / Siang / Malam" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Toleransi (Menit)</label>
                        <input type="number" name="toleransi_telat" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 bg-gray-50" value="0" required>
                    </div>
                </div>

                <!-- WAKTU -->
                <div class="space-y-6">
                    <h2 class="text-indigo-600 font-black text-xs uppercase tracking-[0.2em] border-b pb-3">2. Waktu Operasional</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- JAM MASUK -->
                        <div class="relative group">
                            <label class="block text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2">Jam Masuk</label>
                            <input type="time" name="jam_masuk"
                                {{-- PERBAIKAN: Format jam dipaksa H:i (tanpa detik) agar Firefox mau nampilin data --}}
                                value="{{ isset($shift) ? date('H:i', strtotime($shift->jam_masuk)) : old('jam_masuk') }}"
                                {{-- PERBAIKAN: Tambah onclick showPicker agar diklik dimanapun kotaknya, jam muncul --}}
                                onclick="event.preventDefault(); this.showPicker();"
                                class="w-full border-2 border-emerald-100 rounded-2xl p-4 font-black text-2xl text-emerald-700 bg-emerald-50 outline-none focus:border-emerald-500 relative z-30 cursor-pointer block appearance-none"
                                required>
                        </div>

                        <!-- JAM KELUAR -->
                        <div class="relative group">
                            <label class="block text-[10px] font-black text-rose-600 uppercase tracking-widest mb-2">Jam Keluar</label>
                            <input type="time" name="jam_keluar"
                                {{-- PERBAIKAN: Format jam dipaksa H:i agar tidak muncul titik-titik di Laptop --}}
                                value="{{ isset($shift) ? date('H:i', strtotime($shift->jam_keluar)) : old('jam_keluar') }}"
                                onclick="event.preventDefault(); this.showPicker();"
                                class="w-full border-2 border-rose-100 rounded-2xl p-4 font-black text-2xl text-rose-700 bg-rose-50 outline-none focus:border-rose-500 relative z-30 cursor-pointer block appearance-none"
                                required>
                        </div>
                    </div>
                    <p class="text-[9px] text-gray-400 italic mt-2">*Tips: Klik pada angka jam untuk memunculkan pilihan waktu.</p>
                </div>
            </div>

            <div class="mt-12 flex justify-end">
                <button type="submit" class="bg-indigo-950 text-white px-16 py-5 rounded-3xl font-black hover:bg-black transition shadow-2xl uppercase text-xs tracking-[0.2em]">
                    Simpan Shift
                </button>
            </div>
        </form>
    </div>
</div>
@endsection