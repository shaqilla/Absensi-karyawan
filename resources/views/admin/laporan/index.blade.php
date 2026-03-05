@extends('layouts.admin')

@section('content')
<style>
    /* CSS agar saat print, sidebar dan tombol filter hilang */
    @media print {

        aside,
        header,
        .filter-section,
        .btn-print,
        .mobile-helper {
            display: none !important;
        }

        main {
            margin: 0 !important;
            padding: 0 !important;
        }

        .bg-white {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>

<div class="w-full pb-10">
    <!-- HEADER SECTION: Responsif -->
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-8 gap-4 text-center md:text-left">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Rekap Laporan</h1>
            <p class="text-gray-500 text-xs md:text-sm italic font-medium">Monitoring status Hadir, Izin, Sakit, dan Alpha.</p>
        </div>
        <button type="button" onclick="window.print()" class="btn-print w-full md:w-auto bg-slate-800 text-white px-8 py-3 rounded-2xl font-black hover:bg-black transition flex items-center justify-center shadow-lg text-xs uppercase tracking-widest">
            <i class="fas fa-print mr-2"></i> Cetak Ke PDF
        </button>
    </div>

    <!-- Filter Section: Responsif (Tumpuk di HP) -->
    <div class="filter-section bg-white p-5 md:p-8 rounded-2xl md:rounded-[2rem] shadow-sm mb-8 border border-gray-100">
        <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 md:gap-6 lg:items-end">
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $start_date }}" class="w-full border-gray-200 rounded-xl p-3 md:p-4 text-sm focus:ring-2 focus:ring-indigo-500 outline-none border font-bold text-gray-700">
            </div>
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $end_date }}" class="w-full border-gray-200 rounded-xl p-3 md:p-4 text-sm focus:ring-2 focus:ring-indigo-500 outline-none border font-bold text-gray-700">
            </div>
            <button type="submit" class="w-full lg:w-auto bg-indigo-600 text-white px-10 py-3 md:py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase text-xs tracking-widest">
                Filter Laporan
            </button>
        </form>
    </div>

    <!-- Tabel Laporan: Responsif dengan Horizontal Scroll -->
    <div class="bg-white rounded-2xl md:rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[850px]">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Karyawan</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase text-center tracking-widest">Tanggal</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase text-center tracking-widest">Masuk</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase text-center tracking-widest">Pulang</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase text-center tracking-widest">Status</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($laporans as $l)
                    <tr class="hover:bg-gray-50/50 transition duration-200">
                        <td class="p-4 md:p-6">
                            <div class="flex flex-col">
                                <p class="font-black text-gray-800 uppercase tracking-tight text-xs md:text-sm">{{ $l->nama }}</p>
                                <p class="text-[9px] md:text-[10px] text-indigo-500 font-bold uppercase tracking-tighter">{{ $l->departemen }}</p>
                            </div>
                        </td>
                        <td class="p-4 md:p-6 text-center font-bold text-gray-600 text-xs md:text-sm">
                            {{ date('d/m/Y', strtotime($l->tanggal)) }}
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            <span class="font-mono font-black text-xs md:text-sm {{ $l->status == 'alpha' ? 'text-gray-300' : 'text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg' }}">
                                {{ $l->jam_masuk }}
                            </span>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            <span class="font-mono font-black text-xs md:text-sm {{ $l->status == 'alpha' ? 'text-gray-300' : 'text-rose-600 bg-rose-50 px-2 py-1 rounded-lg' }}">
                                {{ $l->jam_keluar }}
                            </span>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            @php
                            $statusColors = [
                            'hadir' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                            'telat' => 'bg-amber-50 text-amber-600 border-amber-100',
                            'sakit' => 'bg-purple-50 text-purple-600 border-purple-100',
                            'izin' => 'bg-blue-50 text-blue-600 border-blue-100',
                            'cuti' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                            'alpha' => 'bg-rose-50 text-rose-600 border-rose-100 font-black animate-pulse',
                            ];
                            $class = $statusColors[$l->status] ?? 'bg-gray-50 text-gray-600';
                            @endphp
                            <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border {{ $class }}">
                                {{ $l->status }}
                            </span>
                        </td>
                        <td class="p-4 md:p-6">
                            <p class="text-xs text-gray-500 italic truncate max-w-[150px]" title="{{ $l->keterangan }}">{{ $l->keterangan }}</p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-20 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-calendar-times text-gray-100 text-7xl mb-4"></i>
                                <p class="text-gray-400 font-black uppercase tracking-widest text-[10px]">Pilih tanggal untuk melihat data</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MOBILE HELPER: Memberitahu user kalau tabel bisa di-geser -->
    <div class="mobile-helper mt-4 md:hidden flex items-center justify-center bg-indigo-50 p-3 rounded-xl border border-indigo-100">
        <i class="fas fa-arrows-alt-h text-indigo-400 mr-2 text-xs"></i>
        <p class="text-[9px] text-indigo-700 font-black uppercase tracking-widest text-center">Geser tabel ke samping untuk detail lengkap</p>
    </div>
</div>
@endsection