@extends('layouts.admin')

@section('content')
<div class="w-full pb-6">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-2">
        <div>
            <h1 class="text-xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Tambah Shift</h1>
            <p class="text-gray-500 text-[10px] md:text-sm italic font-medium">Pilih kategori shift dan atur jam operasional.</p>
        </div>
        <a href="{{ route('admin.shift.index') }}" class="text-gray-400 hover:text-indigo-600 transition font-bold text-[10px] uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded-xl shadow-sm">
        <ul class="text-[10px] font-bold uppercase">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl md:rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.shift.store') }}" method="POST" class="p-5 md:p-10">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-12">

                <!-- SISI KIRI: KATEGORI -->
                <div class="space-y-5">
                    <h2 class="text-indigo-600 font-black text-[10px] uppercase tracking-[0.2em] border-b border-indigo-50 pb-2 flex items-center">
                        <i class="fas fa-tag mr-2"></i> Kategori Shift
                    </h2>

                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Pilih Nama Shift</label>
                        <!-- GANTI DARI INPUT KE SELECT (DROPDOWN) -->
                        <select name="nama_shift" class="w-full border-gray-200 rounded-xl p-3 md:p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 text-sm md:text-base bg-gray-50" required>
                            <option value="">-- Pilih Shift --</option>
                            <option value="Shift Pagi" {{ old('nama_shift') == 'Shift Pagi' ? 'selected' : '' }}>Shift Pagi</option>
                            <option value="Shift Siang" {{ old('nama_shift') == 'Shift Siang' ? 'selected' : '' }}>Shift Siang</option>
                            <option value="Shift Malam" {{ old('nama_shift') == 'Shift Malam' ? 'selected' : '' }}>Shift Malam</option>
                            <option value="Shift Lembur" {{ old('nama_shift') == 'Shift Lembur' ? 'selected' : '' }}>Shift Lembur</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Toleransi Telat (Menit)</label>
                        <input type="number" name="toleransi_telat" value="{{ old('toleransi_telat', 0) }}"
                            class="w-full border-gray-200 rounded-xl p-3 md:p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 text-sm md:text-base bg-gray-50"
                            placeholder="Contoh: 15" required>
                    </div>
                </div>

                <!-- SISI KANAN: WAKTU -->
                <div class="space-y-5">
                    <h2 class="text-indigo-600 font-black text-[10px] uppercase tracking-[0.2em] border-b border-indigo-50 pb-2 flex items-center">
                        <i class="fas fa-clock mr-2"></i> Pengaturan Jam
                    </h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="relative">
                            <label class="block text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1 text-center">Masuk</label>
                            <input type="time" name="jam_masuk" value="{{ old('jam_masuk') }}"
                                onclick="this.showPicker()"
                                class="w-full border-2 border-emerald-50 rounded-xl p-3 md:p-4 focus:border-emerald-500 border outline-none font-black text-lg md:text-xl text-emerald-700 bg-emerald-50/30 cursor-pointer text-center"
                                required>
                        </div>
                        <div class="relative">
                            <label class="block text-[9px] font-black text-rose-500 uppercase tracking-widest mb-1 text-center">Pulang</label>
                            <input type="time" name="jam_keluar" value="{{ old('jam_keluar') }}"
                                onclick="this.showPicker()"
                                class="w-full border-2 border-rose-50 rounded-xl p-3 md:p-4 focus:border-rose-500 border outline-none font-black text-lg md:text-xl text-rose-700 bg-rose-50/30 cursor-pointer text-center"
                                required>
                        </div>
                    </div>

                    <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100 flex items-start">
                        <i class="fas fa-exclamation-circle text-amber-500 mt-1 mr-3 text-xs"></i>
                        <p class="text-[9px] text-amber-800 font-bold leading-relaxed uppercase">
                            Pastikan jam operasional sudah benar. Data ini akan menjadi acuan deteksi "Terlambat" dan "Pulang Awal".
                        </p>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SIMPAN: Dibuat lebih mepet ke atas (mt-8) -->
            <div class="mt-8 md:mt-12 flex justify-end border-t border-gray-50 pt-6">
                <button type="submit" class="w-full md:w-auto bg-slate-900 text-white px-16 py-4 rounded-2xl font-black hover:bg-black transition shadow-lg text-xs uppercase tracking-widest">
                    Simpan Shift
                </button>
            </div>
        </form>
    </div>
</div>
@endsection