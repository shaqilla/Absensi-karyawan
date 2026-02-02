@extends('layouts.admin')

@section('content')
<div class="w-full">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-black text-gray-800 uppercase">Manajemen Shift</h1>
        <a href="{{ route('admin.shift.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-bold hover:bg-indigo-700">
            + Tambah Shift
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr class="text-gray-400 text-[10px] font-black uppercase">
                    <th class="p-6">Nama Shift</th>
                    <th class="p-6">Jam Masuk</th>
                    <th class="p-6">Jam Keluar</th>
                    <th class="p-6">Toleransi (Menit)</th>
                    <th class="p-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($shifts as $s)
                <tr class="text-sm">
                    <td class="p-6 font-bold">{{ $s->nama_shift }}</td>
                    <td class="p-6 text-indigo-600 font-mono">{{ $s->jam_masuk }}</td>
                    <td class="p-6 text-gray-600 font-mono">{{ $s->jam_keluar }}</td>
                    <td class="p-6">{{ $s->toleransi_telat }} Menit</td>
                    <td class="p-6 text-center">
                        <form action="{{ route('admin.shift.destroy', $s->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection