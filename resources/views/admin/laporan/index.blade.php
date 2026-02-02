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
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-black text-gray-800 uppercase tracking-tighter">Laporan Kehadiran & Lembur</h1>
        <button type="button" onclick="window.print()" class="btn-print bg-gray-800 text-white px-6 py-2 rounded-xl font-bold hover:bg-gray-900 transition flex items-center">
            <i class="fas fa-print mr-2"></i> Cetak Ke PDF
        </button>
    </div>

    <!-- Filter Section -->
    <div class="filter-section bg-white p-6 rounded-3xl shadow-sm mb-6 border border-gray-100">
        <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex flex-wrap gap-6 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $start_date }}" class="w-full border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none border">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $end_date }}" class="w-full border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none border">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase text-xs tracking-widest">
                Filter Data
            </button>
        </form>
    </div>

    <!-- Tabel Laporan -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="p-4 text-[10px] font-black text-gray-400 uppercase">Karyawan</th>
                    <th class="p-4 text-[10px] font-black text-gray-400 uppercase text-center">Tanggal</th>
                    <th class="p-4 text-[10px] font-black text-gray-400 uppercase text-center">Masuk</th>
                    <th class="p-4 text-[10px] font-black text-gray-400 uppercase text-center">Pulang</th>
                    <th class="p-4 text-[10px] font-black text-gray-400 uppercase text-center">Status</th>
                    <th class="p-4 text-[10px] font-black text-gray-400 uppercase">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @foreach($laporans as $l)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-4 text-center">
                        <div class="text-left">
                            <p class="font-bold text-gray-800">{{ $l->user->nama }}</p>
                            <p class="text-[10px] text-gray-400 uppercase">{{ $l->user->karyawan->departemen->nama_departemen }}</p>
                        </div>
                    </td>
                    <td class="p-4 text-center text-gray-600">{{ date('d/m/Y', strtotime($l->tanggal)) }}</td>
                    <td class="p-4 text-center font-mono font-bold text-indigo-600">
                        {{ $l->jam_masuk ? date('H:i', strtotime($l->jam_masuk)) : '--:--' }}
                    </td>
                    <td class="p-4 text-center font-mono font-bold text-rose-600">
                        {{ $l->jam_keluar ? date('H:i', strtotime($l->jam_keluar)) : '--:--' }}
                    </td>
                    <td class="p-4 text-center">
                        <span class="px-3 py-1 rounded-full font-black text-[9px] uppercase 
                            {{ $l->status == 'hadir' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                            {{ $l->status }}
                        </span>
                    </td>
                    <td class="p-4">
                        <p class="text-xs text-gray-600 italic">{{ $l->keterangan ?? '-' }}</p>
                        @if(str_contains($l->keterangan, 'Lembur'))
                            <span class="inline-block mt-1 bg-orange-100 text-orange-600 text-[9px] font-black px-2 py-0.5 rounded uppercase">Verified Overtime</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection