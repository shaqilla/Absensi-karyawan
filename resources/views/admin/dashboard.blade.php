@extends('layouts.admin')

@section('content')
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Ringkasan Kehadiran Hari Ini</h1>

    <!-- Grid Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">Total Karyawan</p>
                    <p class="text-3xl font-black text-gray-800">{{ $totalKaryawan }}</p>
                </div>
                <i class="fas fa-users text-3xl text-blue-200"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">Hadir</p>
                    <p class="text-3xl font-black text-gray-800">{{ $hadirHariIni }}</p>
                </div>
                <i class="fas fa-check-circle text-3xl text-green-200"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">Telat</p>
                    <p class="text-3xl font-black text-gray-800">{{ $telatHariIni }}</p>
                </div>
                <i class="fas fa-clock text-3xl text-yellow-200"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">Izin Pending</p>
                    <p class="text-3xl font-black text-gray-800">{{ $pendingIzin }}</p>
                </div>
                <i class="fas fa-envelope text-3xl text-red-200"></i>
            </div>
        </div>
    </div>

    <!-- Tabel Activity -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-bold text-gray-800">Presensi Terbaru</h2>
            <button class="text-indigo-600 text-sm font-semibold hover:underline">Lihat Semua</button>
        </div>
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-400 text-xs uppercase">
                    <th class="p-4">Karyawan</th>
                    <th class="p-4">Waktu</th>
                    <th class="p-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($presensiTerbaru as $p)
                <tr>
                    <td class="p-4 flex items-center">
                        <div class="w-8 h-8 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold mr-3">
                            {{ substr($p->user->nama, 0, 1) }}
                        </div>
                        {{ $p->user->nama }}
                    </td>
                    <td class="p-4 font-mono text-gray-600">{{ date('H:i:s', strtotime($p->jam_masuk)) }}</td>
                    <td class="p-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $p->status == 'hadir' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                            {{ strtoupper($p->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="p-8 text-center text-gray-400 italic">Belum ada data hari ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection