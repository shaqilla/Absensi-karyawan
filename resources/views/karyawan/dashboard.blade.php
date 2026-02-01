@extends('layouts.karyawan')

@section('content')
<div class="w-full">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-800 tracking-tight">DASHBOARD SAYA</h1>
        <p class="text-gray-500">Pantau kehadiran dan status absensi Anda hari ini.</p>
    </div>

    <!-- Grid Statistik (Lebar) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Status Hari Ini</p>
            <h3 class="text-2xl font-bold {{ $presensiHariIni ? 'text-green-600' : 'text-red-500' }}">
                {{ $presensiHariIni ? 'SUDAH HADIR' : 'BELUM ABSEN' }}
            </h3>
        </div>

        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Waktu Masuk</p>
            <h3 class="text-2xl font-bold text-gray-800">
                {{ $presensiHariIni ? date('H:i', strtotime($presensiHariIni->jam_masuk)) . ' WIB' : '--:--' }}
            </h3>
        </div>

        <div class="bg-indigo-600 p-8 rounded-3xl shadow-xl flex items-center justify-between">
            <div>
                <p class="text-white text-[10px] font-bold uppercase opacity-60 mb-1">Aksi</p>
                <a href="{{ route('karyawan.scan') }}" class="text-white font-black border-b-2 border-white pb-1 hover:text-indigo-200 transition">
                    SCAN QR SEKARANG <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <i class="fas fa-fingerprint text-white text-4xl opacity-20"></i>
        </div>
    </div>

    <!-- Tabel Riwayat (Sangat Lebar) -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-100">
            <h2 class="font-bold text-gray-800 uppercase text-xs tracking-widest">Riwayat 7 Hari Terakhir</h2>
        </div>
        <table class="w-full text-left">
            <thead>
                <tr class="text-gray-400 text-[10px] font-black uppercase border-b">
                    <th class="p-6">Tanggal</th>
                    <th class="p-6">Jam Masuk</th>
                    <th class="p-6">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($riwayat as $r)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-6 font-bold text-gray-700">{{ date('d F Y', strtotime($r->tanggal)) }}</td>
                    <td class="p-6 font-mono text-gray-500">{{ date('H:i:s', strtotime($r->jam_masuk)) }}</td>
                    <td class="p-6">
                        <span class="px-4 py-1 rounded-full text-[10px] font-black uppercase bg-green-100 text-green-600">
                            {{ $r->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="p-10 text-center text-gray-400 italic">Belum ada riwayat absensi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection