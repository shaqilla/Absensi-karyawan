@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold mb-6">Persetujuan Izin/Sakit/Cuti</h1>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-gray-50 text-gray-400 text-xs uppercase font-bold">
                <th class="p-4">Karyawan</th>
                <th class="p-4">Jenis</th>
                <th class="p-4">Tanggal</th>
                <th class="p-4">Alasan</th>
                <th class="p-4 text-center">Status</th>
                <th class="p-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($pengajuans as $p)
            <tr class="text-sm">
                <td class="p-4 font-bold">{{ $p->karyawan->nama }}</td>
                <td class="p-4 capitalize">{{ $p->jenis_pengajuan }}</td>
                <td class="p-4">{{ $p->tanggal_mulai }} s/d {{ $p->tanggal_selesai }}</td>
                <td class="p-4">{{ $p->alasan }}</td>
                <td class="p-4 text-center">
                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase 
                        {{ $p->status_approval == 'pending' ? 'bg-yellow-100 text-yellow-600' : ($p->status_approval == 'disetujui' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600') }}">
                        {{ $p->status_approval }}
                    </span>
                </td>
                <td class="p-4 text-center">
                    @if($p->status_approval == 'pending')
                    <div class="flex justify-center space-x-2">
                        <form action="{{ route('admin.pengajuan.update', $p->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="disetujui">
                            <button class="bg-green-500 text-white px-3 py-1 rounded text-xs">Setujui</button>
                        </form>
                        <form action="{{ route('admin.pengajuan.update', $p->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="ditolak">
                            <button class="bg-red-500 text-white px-3 py-1 rounded text-xs">Tolak</button>
                        </form>
                    </div>
                    @else
                        <span class="text-gray-400 italic">Selesai</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection