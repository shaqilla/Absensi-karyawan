@extends('layouts.admin')

@section('content')
<div class="w-full">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-black text-gray-800 uppercase">Jadwal Kerja Karyawan</h1>
        <a href="{{ route('admin.jadwal.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-bold hover:bg-indigo-700">
            + Setel Jadwal
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr class="text-gray-400 text-[10px] font-black uppercase">
                    <th class="p-6">Karyawan</th>
                    <th class="p-6">Hari</th>
                    <th class="p-6">Shift</th>
                    <th class="p-6">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($jadwals as $j)
                <tr class="text-sm">
                    <td class="p-6 font-bold uppercase">{{ $j->user->nama }}</td>
                    <td class="p-6 capitalize">{{ $j->hari }}</td>
                    <td class="p-6 text-indigo-600">{{ $j->shift->nama_shift }} ({{ $j->shift->jam_masuk }})</td>
                    <td class="p-6">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black {{ $j->status == 'aktif' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            {{ strtoupper($j->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection