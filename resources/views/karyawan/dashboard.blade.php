@extends('layouts.karyawan')

@section('content')
<div class="w-full pb-10 text-black">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 text-center md:text-left">
        <div>
            <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tighter text-indigo-950">Dashboard Saya</h1>
            <p class="text-gray-500 text-xs md:text-sm italic">Status kehadiran dan riwayat mingguan.</p>
        </div>
        <div class="flex justify-center">
            <span class="bg-white text-indigo-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-indigo-100 shadow-sm">
                {{ now()->isoFormat('dddd, D MMMM YYYY') }}
            </span>
        </div>
    </div>

    <!-- HERO SECTION DOMPET INTEGRITAS -->
    <div class="bg-indigo-900 rounded-[2.5rem] p-8 text-white mb-10 shadow-2xl relative overflow-hidden border-4 border-indigo-800">
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-wallet text-indigo-300 text-xs"></i>
                <p class="text-indigo-200 text-[10px] font-black uppercase tracking-[0.2em]">Saldo Poin Integritas</p>
            </div>
            <div class="flex items-end gap-3">
                <h1 class="text-5xl font-black tracking-tighter">{{ auth()->user()->currentPoints() }}</h1>
                <p class="text-indigo-300 font-black mb-1 uppercase text-xs tracking-widest">Points</p>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('karyawan.wallet.index') }}" class="bg-white text-indigo-900 hover:bg-indigo-50 px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-lg flex items-center">
                    <i class="fas fa-store mr-2"></i> Tukar Poin
                </a>
                <a href="{{ route('karyawan.wallet.index') }}" class="bg-indigo-800/50 hover:bg-indigo-800 px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition border border-indigo-700 flex items-center">
                    <i class="fas fa-exchange-alt mr-2 text-indigo-400"></i> Riwayat Poin
                </a>
            </div>
        </div>
        <i class="fas fa-coins absolute -right-10 -bottom-10 text-[15rem] opacity-10 rotate-12"></i>
    </div>


    <!-- GRID STATISTIK -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <!-- Log Presensi: DI SINI PERBAIKANNYA -->
        <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-gray-100 transition-all hover:shadow-md">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 border-b pb-2">Log Hari Ini</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Masuk</span>
                    {{-- Hanya tampilkan jam kalau status BUKAN alpha --}}
                    <span class="text-sm font-black {{ ($presensiHariIni && $presensiHariIni->status != 'alpha') ? 'text-emerald-600' : 'text-gray-300' }}">
                        {{ ($presensiHariIni && $presensiHariIni->status != 'alpha') ? date('H:i', strtotime($presensiHariIni->jam_masuk)) : '--:--' }}
                    </span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-50 pt-2">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Pulang</span>
                    {{-- Hanya tampilkan jam kalau status BUKAN alpha dan jam_keluar ada --}}
                    <span class="text-sm font-black {{ ($presensiHariIni && $presensiHariIni->jam_keluar && $presensiHariIni->status != 'alpha') ? 'text-rose-600' : 'text-gray-300' }}">
                        {{ ($presensiHariIni && $presensiHariIni->jam_keluar && $presensiHariIni->status != 'alpha') ? date('H:i', strtotime($presensiHariIni->jam_keluar)) : '--:--' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Jadwal Shift -->
        <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-gray-100">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 border-b pb-2">Shift Kerja</p>
            <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase">Jadwal</span>
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

        <div class="relative overflow-hidden h-full min-h-[140px]">
            @if($isAlpha || ($presensiHariIni && $presensiHariIni->status == 'alpha'))
                <div class="bg-slate-900 p-6 md:p-8 rounded-[2rem] shadow-xl flex items-center justify-between h-full border-b-4 border-rose-600 text-white">
                    <div>
                        <p class="text-rose-500 text-[10px] font-black uppercase mb-1">Status</p>
                        <h4 class="font-black text-xl leading-tight uppercase">TIDAK MASUK KERJA<br>(ALPHA)</h4>
                    </div>
                    <i class="fas fa-user-times text-4xl opacity-20"></i>
                </div>
            @elseif($isWaiting)
                <div class="bg-amber-50 p-6 md:p-8 rounded-[2rem] shadow-sm border border-dashed border-amber-200 flex items-center justify-between h-full text-amber-600">
                    <div><h4 class="font-black text-sm uppercase">BELUM WAKTUNYA<br>ABSEN</h4></div>
                    <i class="fas fa-hourglass-start text-2xl opacity-30 animate-pulse"></i>
                </div>
            @elseif(!$presensiHariIni)
                <div class="bg-indigo-600 p-6 md:p-8 rounded-[2rem] shadow-xl flex flex-col justify-center h-full hover:bg-indigo-700 transition">
                    <a href="{{ route('karyawan.scan') }}" class="bg-white text-indigo-600 py-4 rounded-2xl font-black text-center text-xs uppercase tracking-widest shadow-lg">SCAN MASUK</a>
                </div>
            @elseif($presensiHariIni && !$presensiHariIni->jam_keluar && $presensiHariIni->status != 'alpha')
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

        <!-- GRID STATISTIK HELPDESK (Tambahan buat Operator) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 text-black">
            <div class="bg-indigo-600 p-6 rounded-[2rem] text-white shadow-xl">
                <p class="text-[10px] font-black uppercase opacity-60 mb-1">Tiket Menunggu</p>
                <h3 class="text-3xl font-black">{{ $ticketsOpen ?? 0 }}</h3>
                <p class="text-[9px] mt-2 font-bold uppercase tracking-widest italic text-indigo-200">Butuh Respon Segera</p>
            </div>
            
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Sedang Diproses</p>
                <h3 class="text-3xl font-black text-slate-800">{{ $ticketsInProgress ?? 0 }}</h3>
                <p class="text-[9px] mt-2 font-bold uppercase text-indigo-500">In-Progress</p>
            </div>

            <div class="bg-slate-900 p-6 rounded-[2rem] text-white shadow-xl">
                <p class="text-[10px] font-black uppercase opacity-60 mb-1">Total Aduan</p>
                <h3 class="text-3xl font-black">{{ $totalTickets ?? 0 }}</h3>
                <p class="text-[9px] mt-2 font-bold uppercase text-slate-400 italic">Kumulatif Sistem</p>
            </div>
        </div>
    </div>

    <!-- TABEL RIWAYAT -->
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
                        <td class="p-6 text-center font-mono font-bold {{ $r->status == 'alpha' ? 'text-gray-300' : 'text-slate-600' }}">
                            {{ ($r->status == 'alpha') ? '--:--' : date('H:i', strtotime($r->jam_masuk)) }}
                        </td>
                        <td class="p-6 text-center font-mono font-bold {{ $r->status == 'alpha' ? 'text-gray-300' : 'text-slate-600' }}">
                            {{ ($r->status == 'alpha' || !$r->jam_keluar) ? '--:--' : date('H:i', strtotime($r->jam_keluar)) }}
                        </td>
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