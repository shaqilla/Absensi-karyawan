@extends('layouts.karyawan')

@section('content')
<div class="w-full">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Jadwal Kerja Saya</h1>
        <p class="text-gray-500 text-sm">Daftar hari kerja dan jam operasional Anda.</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-gray-400 text-[10px] font-black uppercase tracking-widest">
                    <th class="p-6">Hari</th>
                    <th class="p-6">Nama Shift</th>
                    <th class="p-6 text-center">Jam Masuk</th>
                    <th class="p-6 text-center">Jam Pulang</th>
                    <th class="p-6 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($jadwals as $j)
                <tr class="hover:bg-indigo-50/30 transition">
                    <td class="p-6 font-bold text-gray-700 capitalize italic">{{ $j->hari }}</td>
                    <td class="p-6 font-black text-indigo-600 uppercase">{{ $j->shift->nama_shift }}</td>
                    <td class="p-6 text-center font-mono font-bold text-gray-600">
                        {{ date('H:i', strtotime($j->shift->jam_masuk)) }}
                    </td>
                    <td class="p-6 text-center font-mono font-bold text-gray-600">
                        {{ date('H:i', strtotime($j->shift->jam_keluar)) }}
                    </td>
                    <td class="p-6 text-center">
                        @if($j->status == 'aktif')
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase bg-green-100 text-green-600 border border-green-200">
                                Wajib Masuk
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase bg-red-100 text-red-600 border border-red-200">
                                Libur
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-10 text-center text-gray-400 italic">
                        Jadwal kerja belum disetel oleh Admin.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 p-6 bg-indigo-50 rounded-2xl border border-indigo-100">
        <p class="text-xs text-indigo-700 leading-relaxed">
            <i class="fas fa-info-circle mr-1"></i> 
            <strong>Catatan:</strong> Jika terdapat ketidaksesuaian jadwal, harap segera hubungi bagian HRD atau Admin sistem.
        </p>
    </div>
</div>
@endsection