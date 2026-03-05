@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 text-center md:text-left">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Persetujuan Izin & Lembur</h1>
            <p class="text-gray-500 text-xs md:text-sm italic font-medium">Kelola permohonan ketidakhadiran dan lembur karyawan.</p>
        </div>
        <div class="flex justify-center">
             <span class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-indigo-100">
                Total: {{ $pengajuans->count() }} Permohonan
            </span>
        </div>
    </div>

    <!-- FITUR SEARCH & FILTER: Responsif (Tumpuk di HP) -->
    <div class="bg-white p-5 md:p-6 rounded-2xl md:rounded-[2rem] shadow-sm border border-gray-100 mb-8">
        <form action="{{ route('admin.pengajuan.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4">
            <!-- Search Nama -->
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama karyawan..." 
                       class="w-full pl-12 pr-4 py-3 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 border outline-none font-medium text-sm">
            </div>
            
            <!-- Filter Status -->
            <div class="w-full lg:w-48">
                <select name="status" class="w-full border-gray-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-600 text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 lg:flex-none bg-indigo-600 text-white px-8 py-3 rounded-xl font-black hover:bg-indigo-700 transition uppercase text-xs tracking-widest shadow-md shadow-indigo-100">
                    Cari
                </button>
                <a href="{{ route('admin.pengajuan.index') }}" class="flex-1 lg:flex-none bg-gray-100 text-gray-500 px-6 py-3 rounded-xl font-bold text-center hover:bg-gray-200 transition uppercase text-xs tracking-widest flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- TABEL DATA: Responsif dengan Horizontal Scroll -->
    <div class="bg-white rounded-2xl md:rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Karyawan</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Jenis</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Tanggal</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Lampiran</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pengajuans as $p)
                    <tr class="hover:bg-gray-50/50 transition duration-200">
                        <td class="p-6">
                            <div class="flex items-center">
                                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-xs mr-3 border border-indigo-200 uppercase">
                                    {{ substr($p->karyawan->nama ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-800 uppercase text-xs leading-none mb-1">{{ $p->karyawan->nama ?? 'User Dihapus' }}</p>
                                    <p class="text-[10px] text-indigo-500 font-bold uppercase tracking-tighter italic leading-none">
                                        {{ $p->karyawan->karyawan->jabatan ?? 'Staf' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="p-6 text-center">
                            <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase {{ $p->jenis_pengajuan == 'lembur' ? 'bg-orange-100 text-orange-600 border border-orange-200' : 'bg-indigo-50 text-indigo-600 border border-indigo-100' }}">
                                {{ $p->jenis_pengajuan }}
                            </span>
                        </td>
                        <td class="p-6 text-center">
                            <p class="text-xs font-bold text-gray-600 leading-none">{{ date('d/m/Y', strtotime($p->tanggal_mulai)) }}</p>
                            <p class="text-[9px] text-gray-400 mt-1 uppercase font-medium">s/d {{ date('d/m/Y', strtotime($p->tanggal_selesai)) }}</p>
                        </td>
                        
                        <td class="p-6 text-center">
                            @if($p->lampiran)
                                <a href="{{ asset('uploads/lampiran/' . $p->lampiran) }}" target="_blank" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-black text-[10px] uppercase border-b-2 border-indigo-100 transition">
                                    <i class="fas fa-file-image mr-1"></i> Lihat File
                                </a>
                            @else
                                <span class="text-gray-300 text-[10px] font-bold uppercase italic tracking-tighter">Tidak Ada</span>
                            @endif
                        </td>

                        <td class="p-6 text-center">
                            <span class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border
                                {{ $p->status_approval == 'pending' ? 'bg-yellow-50 text-yellow-600 border-yellow-200' : ($p->status_approval == 'disetujui' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200') }}">
                                {{ $p->status_approval }}
                            </span>
                        </td>
                        <td class="p-6">
                            <div class="flex justify-center gap-2">
                                @if($p->status_approval == 'pending')
                                    <form action="{{ route('admin.pengajuan.update', $p->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="disetujui">
                                        <button class="bg-emerald-500 text-white px-4 py-2 rounded-xl text-[9px] font-black uppercase hover:bg-emerald-600 shadow-md shadow-emerald-100 transition active:scale-95">Setujui</button>
                                    </form>
                                    <form action="{{ route('admin.pengajuan.update', $p->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="ditolak">
                                        <button class="bg-rose-600 text-white px-4 py-2 rounded-xl text-[9px] font-black uppercase hover:bg-rose-700 shadow-md shadow-rose-100 transition active:scale-95">Tolak</button>
                                    </form>
                                @else
                                    <div class="text-center opacity-60">
                                        <p class="text-gray-400 text-[9px] font-black uppercase tracking-widest italic leading-none">Selesai</p>
                                        <p class="text-[8px] text-gray-300 uppercase mt-1">{{ date('d/m/y', strtotime($p->updated_at)) }}</p>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-20 text-center text-gray-400 font-black uppercase tracking-widest text-[10px]">
                            <i class="fas fa-inbox text-gray-100 text-7xl mb-4 block"></i>
                            Data Pengajuan Kosong
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- HELPER UNTUK MOBILE -->
    <div class="mt-4 md:hidden flex items-center justify-center bg-indigo-50 p-3 rounded-xl border border-indigo-100">
        <i class="fas fa-arrows-alt-h text-indigo-400 mr-2 text-xs"></i>
        <p class="text-[9px] text-indigo-700 font-black uppercase tracking-widest">Geser ke samping untuk melihat aksi</p>
    </div>
</div>
@endsection