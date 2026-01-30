@extends('layouts.presensi')

@section('content')
<div class="w-full max-w-md">
    <!-- Header Profil -->
    <div class="bg-indigo-900 p-6 rounded-b-3xl shadow-lg text-white mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-indigo-200 text-sm">Selamat Bekerja,</p>
                <h1 class="text-xl font-bold uppercase">{{ auth()->user()->nama }}</h1>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-indigo-200"><i class="fas fa-sign-out-alt"></i></button>
            </form>
        </div>
    </div>

    <!-- Status Hari Ini -->
    <div class="px-4 mb-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Status Hari Ini</p>
                <h2 class="text-lg font-black {{ $presensiHariIni ? 'text-green-600' : 'text-red-500' }}">
                    {{ $presensiHariIni ? 'SUDAH ABSEN' : 'BELUM ABSEN' }}
                </h2>
                @if($presensiHariIni)
                    <p class="text-xs text-gray-500">Jam: {{ date('H:i', strtotime($presensiHariIni->jam_masuk)) }} WIB</p>
                @endif
            </div>
            @if(!$presensiHariIni)
                <a href="{{ route('karyawan.scan') }}" class="bg-indigo-600 text-white p-4 rounded-full shadow-lg animate-bounce">
                    <i class="fas fa-camera"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- Menu Cepat -->
    <div class="px-4 grid grid-cols-2 gap-4 mb-8">
        <a href="{{ route('karyawan.izin.create') }}" class="bg-white p-4 rounded-2xl shadow-sm border text-center">
            <i class="fas fa-envelope-open-text text-indigo-600 text-2xl mb-2"></i>
            <p class="text-xs font-bold text-gray-700">Ajukan Izin</p>
        </a>
        <a href="#" class="bg-white p-4 rounded-2xl shadow-sm border text-center">
            <i class="fas fa-calendar-alt text-orange-500 text-2xl mb-2"></i>
            <p class="text-xs font-bold text-gray-700">Jadwal Kerja</p>
        </a>
    </div>

    <!-- Riwayat Terakhir -->
    <div class="px-4">
        <h3 class="font-bold text-gray-800 mb-3 px-1">Riwayat Terakhir</h3>
        <div class="space-y-3">
            @forelse($riwayat as $r)
            <div class="bg-white p-4 rounded-xl shadow-sm border flex justify-between items-center">
                <div>
                    <p class="font-bold text-sm text-gray-800">{{ date('d M Y', strtotime($r->tanggal)) }}</p>
                    <p class="text-[10px] text-gray-400 font-mono">{{ $r->jam_masuk }}</p>
                </div>
                <span class="text-[10px] font-bold px-2 py-1 rounded-md bg-green-100 text-green-600 uppercase">
                    {{ $r->status }}
                </span>
            </div>
            @empty
            <p class="text-center text-gray-400 text-sm italic">Belum ada riwayat.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection