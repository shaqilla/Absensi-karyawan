@extends('layouts.admin')

@section('content')
<div class="w-full">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Persetujuan Izin & Lembur</h1>
        <p class="text-gray-500 text-sm italic">Kelola permohonan ketidakhadiran dan lembur karyawan.</p>
    </div>

    <!-- FITUR SEARCH & FILTER -->
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 mb-8">
        <form action="{{ route('admin.pengajuan.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-4 top-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama karyawan..." class="w-full pl-12 pr-4 py-3 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 border outline-none font-medium">
            </div>
            <div class="w-full md:w-48">
                <select name="status" class="w-full border-gray-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-600">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-black hover:bg-indigo-700 transition uppercase text-xs tracking-widest">Cari</button>
            <a href="{{ route('admin.pengajuan.index') }}" class="bg-gray-100 text-gray-500 px-6 py-3 rounded-xl font-bold text-center hover:bg-gray-200 transition uppercase text-xs tracking-widest flex items-center justify-center">Reset</a>
        </form>
    </div>

    <!-- TABEL DATA -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left">
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
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="p-6">
                        <!-- PERBAIKAN: Langsung panggil nama dari relasi karyawan (User) -->
                        <p class="font-black text-gray-800 uppercase text-sm leading-none mb-1">{{ $p->karyawan->nama }}</p>
                        <!-- PERBAIKAN: Ambil jabatan dari profile karyawan yang nempel di User -->
                        <p class="text-[10px] text-indigo-500 font-bold uppercase tracking-tighter italic">
                            {{ $p->karyawan->karyawan->jabatan ?? 'Staf' }}
                        </p>
                    </td>
                    <td class="p-6 text-center">
                        <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase {{ $p->jenis_pengajuan == 'lembur' ? 'bg-orange-100 text-orange-600' : 'bg-indigo-50 text-indigo-600' }}">
                            {{ $p->jenis_pengajuan }}
                        </span>
                    </td>
                    <td class="p-6 text-center">
                        <p class="text-xs font-bold text-gray-600 leading-none">{{ date('d/m/Y', strtotime($p->tanggal_mulai)) }}</p>
                        <p class="text-[9px] text-gray-400 mt-1 uppercase font-medium">s/d {{ date('d/m/Y', strtotime($p->tanggal_selesai)) }}</p>
                    </td>
                    
                    <td class="p-6 text-center">
                        @if($p->lampiran)
                            <!-- PERBAIKAN: Path file diarahkan ke uploads/lampiran -->
                            <a href="{{ asset('uploads/lampiran/' . $p->lampiran) }}" target="_blank" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-black text-[10px] uppercase border-b-2 border-indigo-100 transition">
                                <i class="fas fa-file-image mr-1"></i> Lihat File
                            </a>
                        @else
                            <span class="text-gray-300 text-[10px] font-bold uppercase italic">Tidak Ada</span>
                        @endif
                    </td>

                    <td class="p-6 text-center">
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase 
                            {{ $p->status_approval == 'pending' ? 'bg-yellow-100 text-yellow-600' : ($p->status_approval == 'disetujui' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600') }}">
                            {{ $p->status_approval }}
                        </span>
                    </td>
                    <td class="p-6">
                        <div class="flex justify-center gap-2">
                            @if($p->status_approval == 'pending')
                                <form action="{{ route('admin.pengajuan.update', $p->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="disetujui">
                                    <button class="bg-green-600 text-white px-4 py-2 rounded-xl text-[9px] font-black uppercase hover:bg-green-700 shadow-lg shadow-green-100 transition active:scale-95">Setujui</button>
                                </form>
                                <form action="{{ route('admin.pengajuan.update', $p->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="ditolak">
                                    <button class="bg-rose-600 text-white px-4 py-2 rounded-xl text-[9px] font-black uppercase hover:bg-rose-700 shadow-lg shadow-rose-100 transition active:scale-95">Tolak</button>
                                </form>
                            @else
                                <div class="text-center">
                                    <p class="text-gray-400 text-[9px] font-black uppercase tracking-widest italic leading-none">Selesai</p>
                                    <p class="text-[8px] text-gray-300 uppercase mt-1">{{ date('d/m/y', strtotime($p->updated_at)) }}</p>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-20 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-inbox text-gray-100 text-7xl mb-4"></i>
                            <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Data Pengajuan Kosong</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection