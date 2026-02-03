@extends('layouts.admin')

@section('content')
<div class="w-full">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight uppercase">Daftar Shift Kerja</h1>
            <p class="text-gray-500 text-sm italic">Tentukan jam operasional kantor di sini.</p>
        </div>
        <a href="{{ route('admin.shift.create') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase text-xs tracking-widest">
            + Tambah Shift Baru
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-gray-400 text-[10px] font-black uppercase tracking-[0.2em]">
                    <th class="p-6 text-center w-20">No</th>
                    <th class="p-6">Nama Shift</th>
                    <th class="p-6 text-center">Jam Masuk</th>
                    <th class="p-6 text-center">Jam Keluar</th>
                    <th class="p-6 text-center">Toleransi</th>
                    <th class="p-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($shifts as $index => $s)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-6 text-center text-gray-400 font-mono">{{ $index + 1 }}</td>
                    <td class="p-6 font-black text-indigo-900 uppercase">{{ $s->nama_shift }}</td>
                    <td class="p-6 text-center font-mono text-indigo-600 font-bold bg-indigo-50/30">{{ date('H:i', strtotime($s->jam_masuk)) }}</td>
                    <td class="p-6 text-center font-mono text-rose-600 font-bold bg-rose-50/30">{{ date('H:i', strtotime($s->jam_keluar)) }}</td>
                    <td class="p-6 text-center text-gray-600">{{ $s->toleransi_telat }} Menit</td>
                    <td class="p-6 text-center">
                        <form action="{{ route('admin.shift.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus shift ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-rose-500 hover:text-rose-700 p-2 rounded-lg hover:bg-rose-50 transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection