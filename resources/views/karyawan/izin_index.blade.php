@extends('layouts.karyawan')

@section('content')
<div class="w-full">
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Status Pengajuan</h1>
            <p class="text-slate-400 text-sm font-medium">Pantau hasil persetujuan Admin di sini.</p>
        </div>
        <!-- Tombol untuk ke halaman Form Tambah -->
        <a href="{{ route('karyawan.izin.create') }}" class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase text-xs tracking-widest">
            + Buat Izin Baru
        </a>
    </div>

    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tgl Pengajuan</th>
                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Jenis</th>
                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Alasan</th>
                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Catatan Admin</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($pengajuans as $p)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="p-6">
                        <p class="text-sm font-bold text-slate-700">{{ date('d M Y', strtotime($p->created_at)) }}</p>
                    </td>
                    <td class="p-6">
                        <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase {{ $p->jenis_pengajuan == 'lembur' ? 'bg-orange-100 text-orange-600' : 'bg-indigo-50 text-indigo-600' }}">
                            {{ $p->jenis_pengajuan }}
                        </span>
                    </td>
                    <td class="p-6">
                        <p class="text-xs text-slate-500 max-w-xs">{{ $p->alasan }}</p>
                    </td>
                    <td class="p-6 text-center">
                        <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest
                            {{ $p->status_approval == 'pending' ? 'bg-yellow-50 text-yellow-600 border border-yellow-100' : 
                               ($p->status_approval == 'disetujui' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100') }}">
                            {{ $p->status_approval }}
                        </span>
                    </td>
                    <td class="p-6 text-xs text-slate-400 italic">
                        {{ $p->catatan_admin ?? 'Tidak ada catatan' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-20 text-center">
                        <i class="fas fa-history text-slate-200 text-6xl mb-4"></i>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Belum ada pengajuan izin.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection