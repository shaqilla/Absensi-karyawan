@extends('layouts.karyawan')

@section('content')
<div class="w-full pb-10">
    <!-- HEADER SECTION: Responsif (Tumpuk di HP, Menyamping di Laptop) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div class="text-center md:text-left">
            <h1 class="text-2xl md:text-3xl font-black text-slate-800 uppercase tracking-tighter">Status Pengajuan</h1>
            <p class="text-slate-400 text-xs md:text-sm font-medium">Pantau hasil persetujuan Admin di sini.</p>
        </div>
        <!-- Tombol Tambah: Lebar penuh di HP -->
        <a href="{{ route('karyawan.izin.create') }}" class="w-full md:w-auto bg-indigo-600 text-white px-8 py-3 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase text-xs tracking-widest text-center">
            + Buat Izin Baru
        </a>
    </div>

    <!-- NOTIFIKASI -->
    @if(session('success'))
    <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 rounded-xl shadow-sm text-xs font-bold uppercase tracking-tight">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif

    <!-- TABEL AREA -->
    <div class="bg-white rounded-2xl md:rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">

        <!-- KUNCI RESPONSIF: div overflow-x-auto ini wajib ada -->
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="p-4 md:p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tgl Pengajuan</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Jenis</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Alasan</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Catatan Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($pengajuans as $p)
                    <tr class="hover:bg-slate-50/50 transition duration-200">
                        <td class="p-4 md:p-6">
                            <p class="text-sm font-bold text-slate-700">{{ date('d M Y', strtotime($p->created_at)) }}</p>
                            <p class="text-[9px] text-slate-400 uppercase tracking-tighter">{{ date('H:i', strtotime($p->created_at)) }} WIB</p>
                        </td>
                        <td class="p-4 md:p-6">
                            <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase {{ $p->jenis_pengajuan == 'lembur' ? 'bg-orange-100 text-orange-600 border border-orange-200' : 'bg-indigo-50 text-indigo-600 border border-indigo-100' }}">
                                {{ $p->jenis_pengajuan }}
                            </span>
                        </td>
                        <td class="p-4 md:p-6">
                            <p class="text-xs text-slate-500 max-w-xs truncate md:whitespace-normal" title="{{ $p->alasan }}">
                                {{ $p->alasan }}
                            </p>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            @php
                            $statusClasses = [
                            'pending' => 'bg-yellow-50 text-yellow-600 border-yellow-100',
                            'disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                            'ditolak' => 'bg-rose-50 text-rose-600 border-rose-100',
                            ];
                            $class = $statusClasses[$p->status_approval] ?? 'bg-slate-50 text-slate-600';
                            @endphp
                            <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border {{ $class }}">
                                {{ $p->status_approval }}
                            </span>
                        </td>
                        <td class="p-4 md:p-6">
                            <p class="text-xs text-slate-400 italic">
                                {{ $p->catatan_admin ?? 'Belum ada tanggapan' }}
                            </p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-20 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-history text-slate-100 text-7xl mb-4"></i>
                                <p class="text-slate-400 font-black uppercase tracking-widest text-[10px]">Belum ada riwayat pengajuan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MOBILE HELPER: Memberitahu user kalau tabel bisa di-geser -->
    <div class="mt-4 md:hidden flex items-center justify-center bg-indigo-50 p-3 rounded-xl border border-indigo-100">
        <i class="fas fa-arrows-alt-h text-indigo-400 mr-2 text-xs"></i>
        <p class="text-[9px] text-indigo-700 font-black uppercase tracking-widest">Geser tabel ke samping untuk detail</p>
    </div>
</div>
@endsection