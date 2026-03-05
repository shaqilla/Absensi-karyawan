@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    <!-- HEADER SECTION: Responsif (Tumpuk di HP, Menyamping di Laptop) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div class="text-center md:text-left">
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Jadwal Kerja Pegawai</h1>
            <p class="text-gray-500 text-xs md:text-sm italic">Hubungan pemetaan shift untuk setiap karyawan.</p>
        </div>
        <a href="{{ route('admin.jadwal.create') }}" class="w-full md:w-auto bg-indigo-600 text-white px-8 py-3 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase text-[10px] tracking-widest text-center">
            + Setel Jadwal Baru
        </a>
    </div>

    <!-- NOTIFIKASI -->
    @if(session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 rounded-xl shadow-sm text-xs font-bold uppercase tracking-tight">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- TABEL AREA -->
    <div class="bg-white rounded-2xl md:rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <!-- KUNCI RESPONSIF: Overflow X Auto agar bisa di-swipe di ponsel -->
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Karyawan</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Hari Kerja</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Nama Shift</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Estimasi Jam</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($jadwals as $j)
                    <tr class="hover:bg-gray-50/50 transition duration-200">
                        <td class="p-4 md:p-6">
                            <div class="flex items-center">
                                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-xs mr-3 border border-indigo-200 uppercase">
                                    {{ substr($j->user->nama ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-800 uppercase text-xs tracking-tight">{{ $j->user->nama ?? 'User Dihapus' }}</p>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">ID: #{{ $j->user_id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-tighter italic border border-slate-200">
                                {{ $j->hari }}
                            </span>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            <div class="inline-flex flex-col">
                                <span class="text-xs font-black text-indigo-600 uppercase">{{ $j->shift->nama_shift ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            <span class="font-mono font-bold text-gray-500 text-xs bg-gray-50 px-2 py-1 rounded border border-gray-100">
                                {{ date('H:i', strtotime($j->shift->jam_masuk)) }} - {{ date('H:i', strtotime($j->shift->jam_keluar)) }}
                            </span>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            <form action="{{ route('admin.jadwal.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center mx-auto shadow-sm border border-rose-100">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-20 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-calendar-times text-gray-100 text-7xl mb-4"></i>
                                <p class="text-gray-400 font-black uppercase tracking-widest text-[10px]">Belum ada jadwal yang disetel</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MOBILE HELPER -->
    <div class="mt-4 md:hidden flex items-center justify-center bg-indigo-50 p-3 rounded-xl border border-indigo-100">
        <i class="fas fa-arrows-alt-h text-indigo-400 mr-2 text-xs"></i>
        <p class="text-[9px] text-indigo-700 font-black uppercase tracking-widest">Geser ke samping untuk melihat aksi</p>
    </div>
</div>
@endsection