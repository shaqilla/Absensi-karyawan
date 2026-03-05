@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    <!-- HEADER: Responsif -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-10 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Setel Jadwal Kerja</h1>
            <p class="text-gray-500 text-xs md:text-sm italic font-medium">Hubungkan pegawai dengan waktu kerja yang tepat.</p>
        </div>
        <a href="{{ route('admin.jadwal.index') }}" class="flex items-center text-gray-400 hover:text-indigo-600 transition font-bold text-xs uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-2xl shadow-sm">
        <ul class="text-[10px] md:text-xs font-bold uppercase">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl md:rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.jadwal.store') }}" method="POST" class="p-6 md:p-12">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16">

                <!-- SISI KIRI: PEMILIHAN SUBJEK -->
                <div class="space-y-6">
                    <h2 class="text-indigo-600 font-black text-[10px] md:text-xs uppercase tracking-[0.2em] border-b border-indigo-50 pb-3 flex items-center">
                        <i class="fas fa-user-clock mr-2"></i> Pegawai & Shift
                    </h2>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Pegawai</label>
                        <select name="user_id" class="w-full border-gray-200 rounded-xl p-3 md:p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 text-sm md:text-base bg-gray-50" required>
                            <option value="">-- Cari Nama --</option>
                            @foreach($karyawans as $k)
                            <option value="{{ $k->id }}" {{ old('user_id') == $k->id ? 'selected' : '' }}>
                                {{ strtoupper($k->nama) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Jam Kerja (Shift)</label>
                        <select name="shift_id" class="w-full border-gray-200 rounded-xl p-3 md:p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 text-sm md:text-base bg-gray-50" required>
                            <option value="">-- Pilih Shift --</option>
                            @foreach($shifts as $s)
                            <option value="{{ $s->id }}" {{ old('shift_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_shift }} ({{ date('H:i', strtotime($s->jam_masuk)) }} - {{ date('H:i', strtotime($s->jam_keluar)) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="hidden lg:block p-6 bg-indigo-50 rounded-3xl border border-indigo-100">
                        <p class="text-[10px] text-indigo-400 font-bold uppercase mb-1">Tips:</p>
                        <p class="text-xs text-indigo-700 leading-relaxed italic">
                            Jadwal yang disetel akan otomatis muncul di Dashboard Karyawan sesuai dengan hari yang Anda pilih.
                        </p>
                    </div>
                </div>

                <!-- SISI KANAN: PEMILIHAN HARI -->
                <div class="space-y-6">
                    <h2 class="text-indigo-600 font-black text-[10px] md:text-xs uppercase tracking-[0.2em] border-b border-indigo-50 pb-3 flex items-center">
                        <i class="fas fa-calendar-check mr-2"></i> Pilih Hari Kerja
                    </h2>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'] as $h)
                        <label class="relative flex flex-col items-center justify-center p-4 border-2 border-gray-50 rounded-2xl cursor-pointer hover:bg-indigo-50 transition-all group has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
                            <input type="checkbox" name="hari[]" value="{{ $h }}" class="hidden peer">
                            <i class="fas fa-check-circle absolute top-2 right-2 text-indigo-600 opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                            <span class="text-[10px] font-black uppercase text-gray-400 peer-checked:text-indigo-600 tracking-tighter">{{ $h }}</span>
                        </label>
                        @endforeach
                    </div>

                    <div class="space-y-4 pt-4">
                        <label class="block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Status Jadwal</label>
                        <div class="flex gap-4">
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="status" value="aktif" class="w-4 h-4 text-indigo-600" checked>
                                <span class="ml-2 text-xs font-bold text-gray-600 uppercase">Wajib Masuk</span>
                            </label>
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="status" value="libur" class="w-4 h-4 text-indigo-600">
                                <span class="ml-2 text-xs font-bold text-gray-600 uppercase">Libur</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- TOMBOL SIMPAN -->
            <div class="mt-10 flex justify-end border-t border-gray-50 pt-15">
                <button type="submit" class="w-full md:w-auto bg-indigo-600 text-white px-16 py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase text-xs tracking-widest">
                    Simpan Jadwal Kerja
                </button>
            </div>
        </form>
    </div>
</div>
@endsection