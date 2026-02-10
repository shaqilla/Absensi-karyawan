@extends('layouts.karyawan')

@section('content')
<div class="w-full">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-800 tracking-tight uppercase">Dashboard Saya</h1>
        <p class="text-gray-500 text-sm">Informasi jadwal dan kehadiran hari ini.</p>
    </div>

    <!-- Grid Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

        <!-- KARTU 1: STATUS KEHADIRAN (Dibuat Detail Masuk & Pulang) -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Log Presensi Hari Ini</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Scan Masuk</span>
                    <span class="text-sm font-black {{ $presensiHariIni ? 'text-emerald-600' : 'text-gray-300' }}">
                        {{ $presensiHariIni ? date('H:i', strtotime($presensiHariIni->jam_masuk)) : '--:--' }}
                    </span>
                </div>
                <div class="flex justify-between items-center border-t pt-2">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Scan Pulang</span>
                    <span class="text-sm font-black {{ ($presensiHariIni && $presensiHariIni->jam_keluar) ? 'text-rose-600' : 'text-gray-300' }}">
                        {{ ($presensiHariIni && $presensiHariIni->jam_keluar) ? date('H:i', strtotime($presensiHariIni->jam_keluar)) : '--:--' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- KARTU 2: JADWAL KERJA (Tetap) -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 border-b pb-2">Jadwal Kerja Hari Ini</p>

            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-bold text-gray-500 uppercase">Jam Masuk</span>
                <span class="text-lg font-black text-indigo-600">
                    {{ $jadwalHariIni ? date('H:i', strtotime($jadwalHariIni->shift->jam_masuk)) : '--:--' }}
                </span>
            </div>

            <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-gray-500 uppercase">Jam Pulang</span>
                <span class="text-lg font-black text-rose-600">
                    {{ $jadwalHariIni ? date('H:i', strtotime($jadwalHariIni->shift->jam_keluar)) : '--:--' }}
                </span>
            </div>

            @if($jadwalHariIni)
            <p class="mt-4 text-[9px] bg-indigo-50 text-indigo-600 font-black px-2 py-1 rounded text-center uppercase tracking-widest">
                SHIFT: {{ $jadwalHariIni->shift->nama_shift }}
            </p>
            @else
            <p class="mt-4 text-[9px] bg-red-50 text-red-500 font-black px-2 py-1 rounded text-center uppercase tracking-widest">
                Tidak Ada Jadwal / Libur
            </p>
            @endif
        </div>

        <!-- KARTU 3: AKSI (DINAMIS: MASUK -> PULANG -> SELESAI) -->
        @if(!$presensiHariIni)
        <!-- 1. BELUM ABSEN MASUK -->
        <div class="bg-indigo-600 p-8 rounded-3xl shadow-xl flex items-center justify-between">
            <div class="text-white">
                <p class="text-white text-[10px] font-bold uppercase opacity-60 mb-2 tracking-widest">Waktunya Masuk</p>
                <a href="{{ route('karyawan.scan') }}" class="bg-white text-indigo-600 px-6 py-2 rounded-xl font-black text-xs hover:bg-indigo-50 transition shadow-lg inline-block">
                    SCAN QR MASUK
                </a>
            </div>
            <i class="fas fa-sign-in-alt text-white text-4xl opacity-20"></i>
        </div>
        @elseif($presensiHariIni && !$presensiHariIni->jam_keluar)
        <!-- 2. SUDAH MASUK TAPI BELUM PULANG -->
        <div class="bg-rose-600 p-8 rounded-3xl shadow-xl flex items-center justify-between">
            <div class="text-white">
                <p class="text-white text-[10px] font-bold uppercase opacity-60 mb-2 tracking-widest">Waktunya Pulang</p>
                <a href="{{ route('karyawan.scan') }}" class="bg-white text-rose-600 px-6 py-2 rounded-xl font-black text-xs hover:bg-rose-50 transition shadow-lg inline-block">
                    SCAN QR PULANG
                </a>
            </div>
            <i class="fas fa-sign-out-alt text-white text-4xl opacity-20"></i>
        </div>
        @else
        <!-- 3. SUDAH SELESAI SEMUA -->
        <div class="bg-emerald-600 p-8 rounded-3xl shadow-xl flex items-center justify-between">
            <div class="text-white">
                <p class="text-white text-[10px] font-bold uppercase opacity-60 mb-1 tracking-widest">Status</p>
                <p class="font-black text-lg leading-tight uppercase">Absensi Selesai</p>
                <p class="text-[10px] opacity-70 uppercase mt-1">Sampai jumpa besok!</p>
            </div>
            <i class="fas fa-check-double text-white text-4xl opacity-20"></i>
        </div>
        @endif
    </div>

    <!-- Tabel Riwayat (Tetap Sama) -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-100">
            <h2 class="font-bold text-gray-800 uppercase text-xs tracking-widest">Riwayat 7 Hari Terakhir</h2>
        </div>
        <table class="w-full text-left">
            <thead>
                <tr class="text-gray-400 text-[10px] font-black uppercase border-b">
                    <th class="p-6">Tanggal</th>
                    <th class="p-6">Jam Masuk</th>
                    <th class="p-6">Jam Pulang</th>
                    <th class="p-6 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($riwayat as $r)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-6 font-bold text-gray-700">{{ date('d F Y', strtotime($r->tanggal)) }}</td>
                    <td class="p-6 font-mono text-gray-500">{{ date('H:i', strtotime($r->jam_masuk)) }}</td>
                    <td class="p-6 font-mono text-gray-500">{{ $r->jam_keluar ? date('H:i', strtotime($r->jam_keluar)) : '--:--' }}</td>
                    <td class="p-6 text-center">
                        <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest 
                            {{ $r->status == 'hadir' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }}">
                            {{ $r->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-10 text-center text-gray-400 italic">Belum ada riwayat absensi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection