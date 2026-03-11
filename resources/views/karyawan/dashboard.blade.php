@extends('layouts.karyawan')

@section('content')
<div class="w-full pb-10">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 text-center md:text-left">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Dashboard Saya</h1>
            <p class="text-gray-500 text-xs md:text-sm italic">Pantau kehadiran dan jadwal kerja Anda hari ini.</p>
        </div>
        <div class="flex justify-center">
            <span class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-indigo-100 shadow-sm">
                {{ now()->isoFormat('dddd, D MMMM YYYY') }}
            </span>
        </div>
    </div>

    <!-- GRID STATISTIK -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8 mb-10">
        <!-- KARTU 1: LOG PRESENSI HARI INI -->
        <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col justify-center transition-all hover:shadow-md">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 border-b pb-2">Log Presensi Hari Ini</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Scan Masuk</span>
                    <span class="text-sm font-black {{ $presensiHariIni ? ($presensiHariIni->status == 'telat' ? 'text-amber-500' : 'text-emerald-600') : 'text-gray-300' }}">
                        {{ $presensiHariIni ? date('H:i', strtotime($presensiHariIni->jam_masuk)) . ' WIB' : '--:--' }}
                    </span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-50 pt-3">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Scan Pulang</span>
                    <span class="text-sm font-black {{ ($presensiHariIni && $presensiHariIni->jam_keluar) ? 'text-rose-600' : 'text-gray-300' }}">
                        {{ ($presensiHariIni && $presensiHariIni->jam_keluar) ? date('H:i', strtotime($presensiHariIni->jam_keluar)) . ' WIB' : '--:--' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- KARTU 2: JADWAL SHIFT -->
        <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-gray-100 transition-all hover:shadow-md">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 border-b pb-2">Jadwal Shift Anda</p>
            <div class="flex justify-between items-center mb-3">
                <span class="text-[10px] font-bold text-gray-500 uppercase">Wajib Masuk</span>
                <span class="text-lg font-black text-indigo-600 tracking-tighter font-mono">
                    {{ $jadwalHariIni ? date('H:i', strtotime($jadwalHariIni->shift->jam_masuk)) : '--:--' }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-bold text-gray-500 uppercase">Wajib Pulang</span>
                <span class="text-lg font-black text-rose-500 tracking-tighter font-mono">
                    {{ $jadwalHariIni ? date('H:i', strtotime($jadwalHariIni->shift->jam_keluar)) : '--:--' }}
                </span>
            </div>
            @if($jadwalHariIni)
            <p class="mt-4 text-[9px] bg-indigo-50 text-indigo-600 font-black px-2 py-1 rounded-lg text-center uppercase tracking-widest border border-indigo-100">
                {{ $jadwalHariIni->shift->nama_shift }} (Toleransi: {{ $jadwalHariIni->shift->toleransi_telat }}m)
            </p>
            @else
            <p class="mt-4 text-[9px] bg-rose-50 text-rose-500 font-black px-2 py-1 rounded-lg text-center uppercase tracking-widest">Tidak Ada Jadwal / Libur</p>
            @endif
        </div>

        <!-- KARTU 3: AKSI DINAMIS -->
        <div class="relative overflow-hidden h-full min-h-[140px]">
            @if($isAlpha)
            <div class="bg-slate-900 p-6 md:p-8 rounded-[2rem] shadow-xl flex items-center justify-between h-full border-b-4 border-rose-600">
                <div class="text-white">
                    <p class="text-rose-500 text-[10px] font-black uppercase mb-1 tracking-widest">Waktu Berakhir</p>
                    <h4 class="font-black text-xl leading-tight uppercase tracking-tighter">TIDAK MASUK<br>(ALPHA)</h4>
                </div>
                <i class="fas fa-user-times text-white text-4xl opacity-20"></i>
            </div>
            @elseif($isWaiting)
            <div class="bg-amber-50 p-6 md:p-8 rounded-[2rem] shadow-sm border border-dashed border-amber-200 flex items-center justify-between h-full text-amber-600">
                <div>
                    <p class="text-amber-500 text-[9px] font-black uppercase mb-1 tracking-widest">Sistem Standby</p>
                    <h4 class="font-black text-sm uppercase leading-tight">JAM KERJA<br>BELUM DIMULAI</h4>
                </div>
                <i class="fas fa-hourglass-start text-2xl opacity-30 animate-pulse"></i>
            </div>
            @elseif(!$presensiHariIni)
            <div class="bg-indigo-600 p-6 md:p-8 rounded-[2rem] shadow-xl flex flex-col justify-center h-full hover:bg-indigo-700 transition duration-300">
                <p class="text-indigo-200 text-[10px] font-black uppercase mb-3 tracking-widest">Siap Bekerja?</p>
                <a href="{{ route('karyawan.scan') }}" class="bg-white text-indigo-600 py-4 rounded-2xl font-black text-center text-xs uppercase tracking-widest shadow-lg hover:scale-105 transition-transform active:scale-95">
                    SCAN QR MASUK <i class="fas fa-camera ml-2"></i>
                </a>
            </div>
            @elseif($presensiHariIni && !$presensiHariIni->jam_keluar)
            <div class="bg-rose-600 p-6 md:p-8 rounded-[2rem] shadow-xl flex flex-col justify-center h-full hover:bg-rose-700 transition duration-300">
                <p class="text-rose-200 text-[10px] font-black uppercase mb-3 tracking-widest text-center">Tugas Selesai?</p>
                <a href="{{ route('karyawan.scan') }}" class="bg-white text-rose-600 py-4 rounded-2xl font-black text-center text-xs uppercase tracking-widest shadow-lg hover:scale-105 transition-transform active:scale-95">
                    SCAN QR PULANG <i class="fas fa-sign-out-alt ml-2"></i>
                </a>
            </div>
            @else
            <div class="bg-emerald-600 p-6 md:p-8 rounded-[2rem] shadow-xl flex items-center justify-between h-full text-white">
                <h4 class="font-black text-xl leading-tight uppercase tracking-tighter italic">"Sesi Selesai,<br>Sampai Jumpa!"</h4>
                <i class="fas fa-check-double text-4xl opacity-20"></i>
            </div>
            @endif
        </div>
    </div>

    <!-- TABEL RIWAYAT -->
    <div class="bg-white rounded-2xl md:rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-black text-gray-800 text-xs uppercase tracking-widest italic">Riwayat 7 Hari Terakhir</h2>
            <i class="fas fa-history text-gray-300"></i>
        </div>
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="text-gray-400 text-[10px] font-black uppercase tracking-widest border-b border-gray-50">
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
                            <span class="font-bold text-gray-700 text-sm tracking-tight">{{ date('d F Y', strtotime($r->tanggal)) }}</span>
                        </td>
                        <td class="p-6 text-center font-mono font-black text-xs text-slate-500">{{ date('H:i', strtotime($r->jam_masuk)) }}</td>
                        <td class="p-6 text-center font-mono font-black text-xs text-slate-500">{{ $r->jam_keluar ? date('H:i', strtotime($r->jam_keluar)) : '--:--' }}</td>
                        <td class="p-6 text-center">
                            <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border
                                {{ $r->status == 'hadir' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 
                                   ($r->status == 'telat' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-rose-50 text-rose-600 border-rose-100') }}">
                                {{ $r->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-20 text-center text-gray-300 uppercase font-black text-xs">Belum Ada Riwayat</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- LOAD LIBRARY CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('radarChart').getContext('2d');

        // Data dummy untuk demo (Nanti bisa dipanggil dari Controller)
        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['Kedisiplinan', 'Kerja Sama', 'Komunikasi', 'Tanggung Jawab', 'Inisiatif'],
                datasets: [{
                    label: 'Skor Performa',
                    data: [4.5, 4, 3.5, 5, 3.8], // Contoh nilai skala 1-5
                    fill: true,
                    backgroundColor: 'rgba(79, 70, 229, 0.2)',
                    borderColor: 'rgb(79, 70, 229)',
                    pointBackgroundColor: 'rgb(79, 70, 229)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(79, 70, 229)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: 5,
                        ticks: {
                            stepSize: 1,
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
@endsection