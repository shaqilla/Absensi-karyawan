@extends('layouts.karyawan')

@section('content')
<div class="w-full pb-10">
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Laporan Saya</h1>
            <p class="text-gray-500 text-sm italic">Rekapitulasi kehadiran Anda per bulan.</p>
        </div>

        <!-- Filter Bulan & Tahun -->
        <form action="{{ route('karyawan.laporan.index') }}" method="GET" class="flex gap-2">
            <select name="bulan" class="border-gray-200 rounded-xl text-xs font-bold uppercase p-2 focus:ring-indigo-500 border">
                @for($m=1; $m<=12; $m++)
                    <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == $m ? 'selected' : '' }}>
                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                    @endfor
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-black uppercase">Cari</button>
        </form>
    </div>

    <!-- 1. RINGKASAN KARTU (STATISTIK PRIBADI) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <div class="bg-emerald-50 p-6 rounded-[2rem] border border-emerald-100 text-center transition-transform hover:scale-105">
            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-1">Tepat Waktu</p>
            <h4 class="text-3xl font-black text-emerald-700">{{ $stats['hadir'] }}</h4>
            <p class="text-[8px] font-bold text-emerald-400 uppercase italic">Hari</p>
        </div>
        <div class="bg-amber-50 p-6 rounded-[2rem] border border-amber-100 text-center transition-transform hover:scale-105">
            <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-1">Terlambat</p>
            <h4 class="text-3xl font-black text-amber-700">{{ $stats['telat'] }}</h4>
            <p class="text-[8px] font-bold text-amber-400 uppercase italic">Hari</p>
        </div>
        <div class="bg-blue-50 p-6 rounded-[2rem] border border-blue-100 text-center transition-transform hover:scale-105">
            <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1">Izin/Sakit</p>
            <h4 class="text-3xl font-black text-blue-700">{{ $stats['izin'] + $stats['sakit'] }}</h4>
            <p class="text-[8px] font-bold text-blue-400 uppercase italic">Hari</p>
        </div>
        <div class="bg-indigo-900 p-6 rounded-[2rem] text-center shadow-xl shadow-indigo-100 transition-transform hover:scale-105 text-white">
            <p class="text-[9px] font-black text-indigo-300 uppercase tracking-widest mb-1">Total Kerja</p>
            <h4 class="text-3xl font-black text-white">{{ $stats['hadir'] + $stats['telat'] }}</h4>
            <p class="text-[8px] font-bold text-indigo-400 uppercase italic">Sesi</p>
        </div>
    </div>

    <!-- 2. TABEL DETAIL KEHADIRAN -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-black text-gray-800 text-xs uppercase tracking-widest">Detail Log Harian</h2>
            <span class="text-[10px] font-bold text-gray-400 italic">Bulan: {{ date('F Y', mktime(0,0,0, $bulan, 1, $tahun)) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 text-[10px] font-black uppercase tracking-widest border-b border-gray-50">
                        <th class="p-6">Tanggal</th>
                        <th class="p-6 text-center">Jam Masuk</th>
                        <th class="p-6 text-center">Jam Pulang</th>
                        <th class="p-6 text-center">Status</th>
                        <th class="p-6">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($laporans as $l)
                    <tr class="hover:bg-indigo-50/30 transition duration-150">
                        <td class="p-6">
                            <p class="font-bold text-gray-700 text-sm">{{ date('d F Y', strtotime($l->tanggal)) }}</p>
                        </td>
                        <td class="p-6 text-center">
                            <span class="font-mono font-black text-indigo-600 bg-indigo-50 px-3 py-1 rounded-lg text-xs">
                                {{ date('H:i', strtotime($l->jam_masuk)) }}
                            </span>
                        </td>
                        <td class="p-6 text-center font-mono font-bold text-gray-400">
                            {{ $l->jam_keluar ? date('H:i', strtotime($l->jam_keluar)) : '--:--' }}
                        </td>
                        <td class="p-6 text-center">
                            <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-widest border
                                {{ $l->status == 'hadir' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100' }}">
                                {{ $l->status }}
                            </span>
                        </td>
                        <td class="p-6">
                            <p class="text-xs text-gray-400 italic">{{ $l->keterangan ?? '-' }}</p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-20 text-center">
                            <p class="text-gray-300 font-black uppercase tracking-widest text-xs italic">Belum ada data di bulan ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection