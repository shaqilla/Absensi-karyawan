@extends('layouts.karyawan') <!-- Pastikan pakai layout lebar yang baru -->

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Form Pengajuan</h1>
        <p class="text-gray-500 text-sm">Ajukan Izin, Sakit, atau Lembur di sini.</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('karyawan.izin.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                
                <!-- Kolom Kiri -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Jenis Pengajuan</label>
                        <select name="jenis_pengajuan" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700" required>
                            <option value="izin">Izin (Keperluan Pribadi)</option>
                            <option value="sakit">Sakit (Butuh Istirahat)</option>
                            <option value="cuti">Cuti Tahunan</option>
                            <option value="lembur" class="text-indigo-600">Lembur (Perintah Atasan)</option> <!-- PILIHAN BARU -->
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Mulai Tanggal</label>
                            <input type="date" name="tanggal_mulai" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sampai Tanggal</label>
                            <input type="date" name="tanggal_selesai" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Alasan Lengkap</label>
                        <textarea name="alasan" rows="4" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Perintah lembur dari Manager IT untuk maintenance server..." required></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Lampiran Bukti (Opsional)</label>
                        <input type="file" name="lampiran" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                </div>
            </div>

            <div class="mt-10 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-12 py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase tracking-widest text-sm">
                    Kirim Pengajuan Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection