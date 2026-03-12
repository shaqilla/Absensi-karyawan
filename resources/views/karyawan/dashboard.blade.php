@extends('layouts.karyawan')

@section('content')
<div class="w-full pb-10 text-black">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 text-center md:text-left">
        <div>
            <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tighter">Dashboard Saya</h1>
            <p class="text-gray-500 text-xs md:text-sm italic">Status kehadiran dan riwayat mingguan.</p>
        </div>
        <div class="flex justify-center">
            <span class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-indigo-100 shadow-sm">
                {{ now()->isoFormat('dddd, D MMMM YYYY') }}
            </span>
        </div>
    </div>

    <!-- STATISTIK GRID -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <!-- Log Presensi -->
        <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-gray-100 transition-all hover:shadow-md">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 border-b pb-2">Log Hari Ini</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Masuk</span>
                    <span class="text-sm font-black {{ $presensiHariIni ? 'text-emerald-600' : 'text-gray-300' }}">
                        {{ $presensiHariIni ? date('H:i', strtotime($presensiHariIni->jam_masuk)) : '--:--' }}
                    </span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-50 pt-2">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Pulang</span>
                    <span class="text-sm font-black {{ ($presensiHariIni && $presensiHariIni->jam_keluar) ? 'text-rose-600' : 'text-gray-300' }}">
                        {{ ($presensiHariIni && $presensiHariIni->jam_keluar) ? date('H:i', strtotime($presensiHariIni->jam_keluar)) : '--:--' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Jadwal Shift -->
        <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-gray-100">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 border-b pb-2">Shift Kerja</p>
            <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] font-bold text-gray-500 uppercase">Jadwal</span>
                <span class="text-lg font-black text-indigo-600 font-mono">
                    {{ $jadwalHariIni ? date('H:i', strtotime($jadwalHariIni->shift->jam_masuk)) : '--:--' }}
                </span>
            </div>
            @if($jadwalHariIni)
                <p class="mt-4 text-[9px] bg-indigo-50 text-indigo-600 font-black px-2 py-1 rounded-lg text-center uppercase border border-indigo-100">{{ $jadwalHariIni->shift->nama_shift }}</p>
            @else
                <p class="mt-4 text-[9px] bg-rose-50 text-rose-500 font-black px-2 py-1 rounded-lg text-center uppercase tracking-widest italic">Tidak Ada Jadwal</p>
            @endif
        </div>

        <!-- AKSI DINAMIS -->
        <div class="relative overflow-hidden h-full min-h-[140px]">
            @if($isAlpha)
                <div class="bg-slate-900 p-8 rounded-[2rem] shadow-xl flex items-center justify-between h-full border-b-4 border-rose-600 text-white">
                    <div>
                        <p class="text-rose-500 text-[10px] font-black uppercase mb-1">Status</p>
                        <h4 class="font-black text-xl leading-tight uppercase">TIDAK MASUK<br>(ALPHA)</h4>
                    </div>
                    <i class="fas fa-user-times text-4xl opacity-20"></i>
                </div>
            @elseif($isWaiting)
                <div class="bg-amber-50 p-8 rounded-[2rem] shadow-sm border border-dashed border-amber-200 flex items-center justify-between h-full text-amber-600">
                    <div><h4 class="font-black text-sm uppercase">BELUM WAKTUNYA<br>ABSEN</h4></div>
                    <i class="fas fa-hourglass-start text-2xl opacity-30 animate-pulse"></i>
                </div>
            @elseif(!$presensiHariIni)
                <div class="bg-indigo-600 p-8 rounded-[2rem] shadow-xl flex flex-col justify-center h-full hover:bg-indigo-700 transition">
                    <a href="{{ route('karyawan.scan') }}" class="bg-white text-indigo-600 py-4 rounded-2xl font-black text-center text-xs uppercase tracking-widest shadow-lg">SCAN MASUK</a>
                </div>
            @elseif($presensiHariIni && !$presensiHariIni->jam_keluar)
                <div class="bg-rose-600 p-8 rounded-[2rem] shadow-xl flex flex-col justify-center h-full hover:bg-rose-700 transition">
                    <a href="{{ route('karyawan.scan') }}" class="bg-white text-rose-600 py-4 rounded-2xl font-black text-center text-xs uppercase tracking-widest shadow-lg">SCAN PULANG</a>
                </div>
            @else
                <div class="bg-emerald-600 p-8 rounded-[2rem] shadow-xl flex items-center justify-between h-full text-white">
                    <h4 class="font-black text-lg leading-tight uppercase">ABSENSI SELESAI</h4>
                    <i class="fas fa-check-double text-4xl opacity-20"></i>
                </div>
            @endif
        </div>
    </div>

    <!-- TABEL RIWAYAT (DATA ASLI + ALPHA) -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-black text-gray-800 text-xs uppercase tracking-widest italic">Riwayat 7 Hari Terakhir</h2>
            <i class="fas fa-history text-gray-300"></i>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 text-[10px] font-black uppercase tracking-widest border-b">
                        <th class="p-6">Tanggal</th>
                        <th class="p-6 text-center">Masuk</th>
                        <th class="p-6 text-center">Pulang</th>
                        <th class="p-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($riwayat as $r)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-6">
                            <span class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($r->tanggal)->format('d F Y') }}</span>
                        </td>
                        <td class="p-6 text-center font-mono font-bold {{ $r->status == 'alpha' ? 'text-gray-300' : 'text-slate-600' }}">{{ $r->jam_masuk }}</td>
                        <td class="p-6 text-center font-mono font-bold {{ $r->status == 'alpha' ? 'text-gray-300' : 'text-slate-600' }}">{{ $r->jam_keluar }}</td>
                        <td class="p-6 text-center">
                            @php
                                $color = [
                                    'hadir' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                    'telat' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    'alpha' => 'bg-rose-50 text-rose-600 border-rose-200 font-black animate-pulse'
                                ][$r->status] ?? 'bg-gray-50 text-gray-400';
                            @endphp
                            <span class="px-4 py-1 rounded-lg text-[9px] font-black uppercase border {{ $color }}">
                                {{ $r->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-20 text-center text-gray-300 uppercase font-black text-xs tracking-widest">Data Kosong</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection