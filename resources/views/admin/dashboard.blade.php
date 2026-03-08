@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div class="text-center md:text-left">
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Ringkasan Hari Ini</h1>
            <p class="text-gray-500 text-xs md:text-sm italic font-medium">Monitoring kehadiran karyawan secara real-time.</p>
        </div>
        <div class="flex justify-center">
            <span class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-indigo-100 shadow-sm">
                {{ now()->isoFormat('dddd, D MMMM YYYY') }}
            </span>
        </div>
    </div>

    <!-- GRID STATISTIK -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-10">
        <!-- Total Karyawan -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border-b-4 border-blue-500 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Total Pegawai</p>
                    <h3 class="text-3xl font-black text-gray-800">{{ $totalKaryawan }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Hadir -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border-b-4 border-emerald-500 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Hadir</p>
                    <h3 class="text-3xl font-black text-gray-800">{{ $hadirHariIni }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Telat -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border-b-4 border-amber-500 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Terlambat</p>
                    <h3 class="text-3xl font-black text-gray-800">{{ $telatHariIni }}</h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-clock text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Tidak Hadir -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border-b-4 border-rose-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Tidak Hadir</p>
                    <h3 class="text-3xl font-black text-gray-800">{{ $tidakHadir }}</h3>
                </div>
                <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-user-times text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL PRESENSI TERBARU -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 md:p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
            <div class="flex items-center">
                <div class="w-2 h-6 bg-indigo-600 rounded-full mr-3"></div>
                <h2 class="font-black text-gray-800 text-sm md:text-base uppercase tracking-widest">Aktivitas Presensi Terbaru</h2>
            </div>
            <i class="fas fa-history text-gray-300 hidden sm:block"></i>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="text-gray-400 text-[10px] font-black uppercase tracking-widest border-b border-gray-50">
                        <th class="p-6">Pegawai</th>
                        <th class="p-6 text-center">Waktu Scan</th>
                        <th class="p-6 text-center">Shift (History)</th>
                        <th class="p-6 text-center">Status</th>
                        <th class="p-6">Unit Kerja</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($presensiTerbaru as $p)
                    <tr class="hover:bg-gray-50/50 transition duration-200">
                        <td class="p-6">
                            <div class="flex items-center">
                                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-xs mr-3 border border-indigo-200 shadow-sm uppercase">
                                    {{ substr($p->user->nama ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-800 uppercase text-xs tracking-tight">{{ $p->user->nama ?? 'User Dihapus' }}</p>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">NIP: {{ $p->user->karyawan->nip ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="p-6 text-center font-mono font-black text-gray-500 text-xs">
                            <span class="bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                                {{ date('H:i', strtotime($p->jam_masuk)) }} <span class="font-medium opacity-50">WIB</span>
                            </span>
                        </td>
                        <!-- KOLOM SHIFT (SNAPSHOT) -->
                        <td class="p-6 text-center">
                            <span class="text-[10px] font-black text-indigo-500 bg-indigo-50 px-2 py-1 rounded-md uppercase border border-indigo-100">
                                {{ $p->shift->nama_shift ?? 'Umum' }}
                            </span>
                        </td>
                        <td class="p-6 text-center">
                            <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border
                                {{ $p->status == 'hadir' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100' }}">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="p-6">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">
                                {{ $p->user->karyawan->departemen->nama_departemen ?? 'General' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-20 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-100 text-7xl mb-4"></i>
                                <p class="text-gray-400 font-black uppercase tracking-widest text-[10px]">Belum ada aktivitas hari ini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection