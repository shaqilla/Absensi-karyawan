@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold mb-6">Laporan Kehadiran</h1>

<!-- Filter -->
<div class="bg-white p-6 rounded-xl shadow-sm mb-6">
    <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">DARI TANGGAL</label>
            <input type="date" name="start_date" value="{{ $start_date }}" class="border rounded-lg p-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">SAMPAI TANGGAL</label>
            <input type="date" name="end_date" value="{{ $end_date }}" class="border rounded-lg p-2 text-sm">
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-bold">Filter</button>
        <button type="button" onclick="window.print()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 font-bold">Cetak PDF</button>
    </form>
</div>

<!-- Tabel Laporan -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-gray-50 text-gray-400 text-xs uppercase font-bold">
                <th class="p-4">Tanggal</th>
                <th class="p-4">Karyawan</th>
                <th class="p-4">Jam Masuk</th>
                <th class="p-4">Status</th>
                <th class="p-4">Lokasi (Lat, Long)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm">
            @foreach($laporans as $l)
            <tr>
                <td class="p-4">{{ $l->tanggal }}</td>
                <td class="p-4 font-bold text-indigo-900">{{ $l->user->nama }}</td>
                <td class="p-4">{{ $l->jam_masuk }}</td>
                <td class="p-4">
                    <span class="px-2 py-1 rounded bg-green-100 text-green-600 font-bold text-[10px] uppercase">{{ $l->status }}</span>
                </td>
                <td class="p-4 text-xs text-gray-500">{{ $l->latitude }}, {{ $l->longitude }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection