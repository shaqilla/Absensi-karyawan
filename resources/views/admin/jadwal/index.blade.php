@extends('layouts.admin')

@section('content')
<div class="w-full">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Jadwal Kerja Karyawan</h1>
            <p class="text-gray-500 text-sm">Menghubungkan karyawan dengan shift kerja mereka.</p>
        </div>
        <a href="{{ route('admin.jadwal.create') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg uppercase text-xs">
            + Setel Jadwal Baru
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-gray-400 text-[10px] font-black uppercase tracking-widest">
                    <th class="p-6">Karyawan</th>
                    <th class="p-6">Hari</th>
                    <th class="p-6">Nama Shift</th>
                    <th class="p-6">Jam Kerja</th>
                    <th class="p-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($jadwals as $j)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-6">
                        <p class="font-black text-indigo-900 uppercase">{{ $j->user->nama }}</p>
                        <p class="text-[10px] text-gray-400">ID User: #{{ $j->user_id }}</p>
                    </td>
                    <td class="p-6 font-bold text-gray-600">{{ $j->hari }}</td>
                    <td class="p-6">
                        <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-lg text-[10px] font-black uppercase">
                            {{ $j->shift->nama_shift }}
                        </span>
                    </td>
                    <td class="p-6 font-mono text-sm text-gray-500">
                        {{ date('H:i', strtotime($j->shift->jam_masuk)) }} - {{ date('H:i', strtotime($j->shift->jam_keluar)) }}
                    </td>
                    <td class="p-6 text-center">
                        <form action="{{ route('admin.jadwal.destroy', $j->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-rose-500 hover:text-rose-700"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection