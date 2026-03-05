@extends('layouts.karyawan')

@section('content')
<div class="w-full pb-10">
    <!-- HEADER: Responsif (Tumpuk di HP, Menyamping di Laptop) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 md:mb-8 gap-4">
        <div class="text-center md:text-left">
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Form Pengajuan</h1>
            <p class="text-gray-500 text-xs md:text-sm italic font-medium">Ajukan Izin, Sakit, Cuti, atau Lembur di sini.</p>
        </div>
        <!-- TOMBOL KEMBALI -->
        <a href="{{ route('karyawan.izin.index') }}" class="flex items-center justify-center bg-white border border-gray-200 text-gray-400 px-6 py-3 rounded-2xl font-bold hover:bg-gray-50 transition shadow-sm text-xs uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-2 text-indigo-500"></i> Kembali
        </a>
    </div>

    <!-- NOTIFIKASI ERROR -->
    @if ($errors->any())
    <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-700 p-4 mb-6 rounded-xl shadow-sm text-[10px] font-bold uppercase">
        <ul>
            @foreach ($errors->all() as $error)
            <li><i class="fas fa-times-circle mr-1"></i> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl md:rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('karyawan.izin.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-10">
            @csrf

            <!-- GRID: 1 Kolom di HP, 2 Kolom di Laptop -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16">

                <!-- KOLOM KIRI: JENIS & TANGGAL -->
                <div class="space-y-6">
                    <h2 class="text-indigo-600 font-black text-[10px] uppercase tracking-[0.2em] border-b border-indigo-50 pb-3 flex items-center">
                        <i class="fas fa-list-alt mr-2"></i> Kategori & Waktu
                    </h2>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Jenis Pengajuan</label>
                        <select name="jenis_pengajuan" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 text-sm md:text-base bg-gray-50" required>
                            <option value="izin" {{ old('jenis_pengajuan') == 'izin' ? 'selected' : '' }}>Izin (Keperluan Pribadi)</option>
                            <option value="sakit" {{ old('jenis_pengajuan') == 'sakit' ? 'selected' : '' }}>Sakit (Butuh Istirahat)</option>
                            <option value="cuti" {{ old('jenis_pengajuan') == 'cuti' ? 'selected' : '' }}>Cuti Tahunan</option>
                            <option value="lembur" {{ old('jenis_pengajuan') == 'lembur' ? 'selected' : '' }}>Lembur (Perintah Atasan)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Dari Tanggal</label>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-sm" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sampai Tanggal</label>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-sm" required>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: ALASAN & FILE -->
                <div class="space-y-6">
                    <h2 class="text-indigo-600 font-black text-[10px] uppercase tracking-[0.2em] border-b border-indigo-50 pb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i> Detail Keterangan
                    </h2>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Alasan Lengkap</label>
                        <textarea name="alasan" rows="4" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-sm" placeholder="Berikan penjelasan singkat mengenai pengajuan Anda..." required>{{ old('alasan') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Lampiran Bukti (Opsional)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-100 border-dashed rounded-xl bg-gray-50 hover:bg-indigo-50 transition duration-300">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-3"></i>
                                <div class="flex text-sm text-gray-600">
                                    <input type="file" name="lampiran" class="block w-full text-[10px] md:text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200">
                                </div>
                                <p class="text-[9px] text-gray-400 uppercase font-bold mt-2 tracking-widest">JPG, PNG up to 2MB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SIMPAN: Responsif (Full Width di HP) -->
            <div class="mt-10 md:mt-12 flex justify-end border-t border-gray-50 pt-8">
                <button type="submit" class="w-full md:w-auto bg-indigo-600 text-white px-12 py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase tracking-widest text-xs">
                    Kirim Pengajuan Sekarang <i class="fas fa-paper-plane ml-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection