@extends('layouts.admin')

@section('content')
<style>
    /* CSS agar saat print, sidebar dan tombol filter hilang */
    @media print {
        aside, header, .filter-section, .btn-print {
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

<div class="w-full">
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Rekap Laporan Kehadiran</h1>
            <p class="text-gray-500 text-sm italic">Monitoring kehadiran dan lembur karyawan secara berkala.</p>
        </div>
        <button type="button" onclick="window.print()" class="btn-print bg-slate-800 text-white px-8 py-3 rounded-2xl font-black hover:bg-black transition flex items-center justify-center shadow-lg shadow-slate-200 text-xs uppercase tracking-widest">
            <i class="fas fa-print mr-2"></i> Cetak Ke PDF
        </button>
    </div>

    <!-- Filter Section -->
    <div class="filter-section bg-white p-8 rounded-[2rem] shadow-sm mb-8 border border-gray-100">
        <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex flex-col md:flex-row gap-6 items-end">
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $start_date }}" class="w-full border-gray-200 rounded-xl p-4 text-sm focus:ring-2 focus:ring-indigo-500 outline-none border font-bold text-gray-700">
            </div>
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $end_date }}" class="w-full border-gray-200 rounded-xl p-4 text-sm focus:ring-2 focus:ring-indigo-500 outline-none border font-bold text-gray-700">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase text-xs tracking-widest w-full md:w-auto">
                Filter Laporan
            </button>
        </form>
    </div>

    <!-- Tabel Laporan -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Karyawan</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase text-center tracking-widest">Tanggal</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase text-center tracking-widest">Masuk</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase text-center tracking-widest">Pulang</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase text-center tracking-widest">Status</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($laporans as $l)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-6">
                            <div class="flex flex-col">
                                <!-- PAKAI ?? UNTUK CEK APAKAH USER/KARYAWAN ADA -->
                                <p class="font-black text-gray-800 uppercase tracking-tight">{{ $l->user->nama ?? 'USER DIHAPUS' }}</p>
                                <p class="text-[10px] text-indigo-500 font-bold uppercase tracking-tighter">
                                    {{ $l->user->karyawan->departemen->nama_departemen ?? 'TANPA UNIT' }}
                                </p>
                            </div>
                        </td>
                        <td class="p-6 text-center font-bold text-gray-600">
                            {{ date('d/m/Y', strtotime($l->tanggal)) }}
                        </td>
                        <td class="p-6 text-center">
                            <span class="font-mono font-black text-indigo-600 bg-indigo-50 px-3 py-1 rounded-lg">
                                {{ $l->jam_masuk ? date('H:i', strtotime($l->jam_masuk)) : '--:--' }}
                            </span>
                        </td>
                        <td class="p-6 text-center">
                            <span class="font-mono font-black text-rose-600 bg-rose-50 px-3 py-1 rounded-lg">
                                {{ $l->jam_keluar ? date('H:i', strtotime($l->jam_keluar)) : '--:--' }}
                            </span>
                        </td>
                        <td class="p-6 text-center">
                            <span class="px-4 py-1.5 rounded-xl font-black text-[9px] uppercase tracking-widest border
                                {{ $l->status == 'hadir' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100' }}">
                                {{ $l->status }}
                            </span>
                        </td>
                        <td class="p-6">
                            <div class="flex flex-col gap-1">
                                <p class="text-xs text-gray-500 italic">{{ $l->keterangan ?? '-' }}</p>
                                @if($l->keterangan && str_contains(strtolower($l->keterangan), 'lembur'))
                                    <span class="w-fit bg-orange-100 text-orange-600 text-[8px] font-black px-2 py-0.5 rounded uppercase tracking-tighter border border-orange-200">
                                        Verified Overtime
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-20 text-center">
                            <i class="fas fa-folder-open text-gray-100 text-7xl mb-4"></i>
                            <p class="text-gray-400 font-black uppercase tracking-widest text-xs">Data Tidak Ditemukan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection