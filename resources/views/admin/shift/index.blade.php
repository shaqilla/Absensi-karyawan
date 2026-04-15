@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div class="text-center md:text-left">
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Daftar Shift Kerja</h1>
            <p class="text-gray-500 text-xs md:text-sm italic">Tentukan jam operasional kantor di sini.</p>
        </div>
        <a href="{{ route('admin.shift.create') }}" class="w-full md:w-auto bg-indigo-600 text-white px-8 py-3 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase text-xs tracking-widest text-center">
            + Tambah Shift Baru
        </a>
    </div>

    <!-- NOTIFIKASI -->
    @if(session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 rounded-xl shadow-sm text-xs font-bold uppercase tracking-tight">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- TABEL AREA -->
    <div class="bg-white rounded-2xl md:rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="p-4 md:p-6 text-center w-20 text-[10px] font-black text-gray-400 uppercase tracking-widest">No</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Shift</th>
                        <th class="p-4 md:p-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Jam Masuk</th>
                        <th class="p-4 md:p-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Jam Keluar</th>
                        <th class="p-4 md:p-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Toleransi</th>
                        <th class="p-4 md:p-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($shifts as $index => $s)
                    <tr class="hover:bg-gray-50/50 transition duration-200">
                        <td class="p-4 md:p-6 text-center text-gray-400 font-mono text-xs">{{ $index + 1 }}</td>
                        <td class="p-4 md:p-6 font-black text-indigo-950 uppercase text-xs md:text-sm tracking-tight">
                            {{ $s->nama_shift }}
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            <span class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-lg font-mono font-black text-xs md:text-sm border border-emerald-100">
                                {{ date('H:i', strtotime($s->jam_masuk)) }}
                            </span>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            <span class="bg-rose-50 text-rose-600 px-3 py-1.5 rounded-lg font-mono font-black text-xs md:text-sm border border-rose-100">
                                {{ date('H:i', strtotime($s->jam_keluar)) }}
                            </span>
                        </td>
                        <td class="p-4 md:p-6 text-center text-xs font-bold text-gray-600">
                            {{ $s->toleransi_telat }} <span class="opacity-50 font-medium">Menit</span>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            <div class="flex justify-center items-center gap-2">
                                <!-- TOMBOL EDIT -->
                                <a href="{{ route('admin.shift.edit', $s->id) }}" class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-amber-100">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>

                                <!-- TOMBOL HAPUS -->
                                <form action="{{ route('admin.shift.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus shift ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-rose-100">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

    <!-- Responsif -->
    <div class="mt-4 md:hidden flex items-center justify-center bg-indigo-50 p-3 rounded-xl border border-indigo-100">
        <i class="fas fa-arrows-alt-h text-indigo-400 mr-2 text-xs"></i>
        <p class="text-[9px] text-indigo-700 font-black uppercase tracking-widest">Geser tabel ke samping untuk detail</p>
    </div>
</div>
@endsection
