@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    <!-- HEADER SECTION: Responsif (Tumpuk di HP, Menyamping di Laptop) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div class="text-center md:text-left">
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Absensi Manual</h1>
            <p class="text-gray-500 text-xs md:text-sm italic font-medium">Input kehadiran darurat tanpa melalui QR Code.</p>
        </div>
        <!-- TOMBOL KEMBALI -->
        <a href="{{ route('admin.dashboard') }}" class="w-full md:w-auto flex items-center justify-center bg-white border border-gray-200 text-gray-400 px-6 py-3 rounded-2xl font-bold hover:bg-gray-50 transition text-xs uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-2 text-indigo-500"></i> Dashboard
        </a>
    </div>

    <!-- NOTIFIKASI ERROR -->
    @if(session('error'))
    <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-700 p-4 mb-8 rounded-xl shadow-sm text-xs font-bold uppercase">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
    </div>
    @endif

    <div class="bg-white rounded-2xl md:rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.presensi.store_manual') }}" method="POST" class="p-6 md:p-12">
            @csrf

            <!-- GRID: 1 Kolom di HP, 2 Kolom di Laptop -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16">

                <!-- SISI KIRI: DATA PEGAWAI -->
                <div class="space-y-6">
                    <h2 class="text-indigo-600 font-black text-[10px] uppercase tracking-[0.2em] border-b border-indigo-50 pb-3 flex items-center">
                        <i class="fas fa-user-circle mr-2"></i> Pilih Subjek
                    </h2>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Karyawan</label>
                        <select name="user_id" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 text-sm md:text-base bg-gray-50" required>
                            <option value="">-- Cari Nama --</option>
                            @foreach($karyawans as $k)
                            <option value="{{ $k->id }}" {{ old('user_id') == $k->id ? 'selected' : '' }}>
                                {{ strtoupper($k->nama) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tanggal Presensi</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}"
                            class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-sm bg-gray-50" required>
                    </div>
                </div>

                <!-- SISI KANAN: STATUS & ALASAN -->
                <div class="space-y-6">
                    <h2 class="text-indigo-600 font-black text-[10px] uppercase tracking-[0.2em] border-b border-indigo-50 pb-3 flex items-center">
                        <i class="fas fa-tasks mr-2"></i> Detail Kehadiran
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Shift Kerja</label>
                            <select name="shift_id" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-sm bg-gray-50" required>
                                @foreach($shifts as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_shift }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Status</label>
                            <select name="status" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-sm bg-gray-50" required>
                                <option value="hadir">HADIR (TEPAT WAKTU)</option>
                                <option value="telat">TERLAMBAT</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Alasan Manual (Audit Trail)</label>
                        <textarea name="keterangan" rows="3" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-sm bg-gray-50" placeholder="Jelaskan mengapa absen dilakukan manual..." required>{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SIMPAN: Responsif Full Width di HP -->
            <div class="mt-10 md:mt-12 flex justify-end border-t border-gray-50 pt-8">
                <button type="submit" class="w-full md:w-auto bg-slate-900 text-white px-12 py-4 rounded-2xl font-black hover:bg-black transition shadow-lg shadow-slate-200 uppercase text-xs tracking-widest">
                    Simpan Data Manual <i class="fas fa-save ml-2 opacity-50"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection