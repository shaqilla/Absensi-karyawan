@extends('layouts.karyawan')

@section('content')
<div class="w-full pb-10">
    <!-- HEADER SECTION: Responsif (Tengah di HP, Kiri di Laptop) -->
    <div class="mb-8 text-center md:text-left">
        <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Jadwal Kerja Saya</h1>
        <p class="text-gray-500 text-xs md:text-sm italic">Daftar hari kerja dan jam operasional resmi Anda.</p>
    </div>

    <!-- TABEL AREA: Rounded besar agar tetap mewah -->
    <div class="bg-white rounded-2xl md:rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">

        <!-- KUNCI RESPONSIF: div overflow-x-auto ini wajib ada -->
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[750px]">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Hari</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Shift</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Jam Masuk</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Jam Pulang</th>
                        <th class="p-4 md:p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($jadwals as $j)
                    <tr class="hover:bg-indigo-50/30 transition duration-200">
                        <td class="p-4 md:p-6">
                            <span class="font-bold text-gray-700 capitalize italic text-sm md:text-base">{{ $j->hari }}</span>
                        </td>
                        <td class="p-4 md:p-6">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></div>
                                <span class="font-black text-indigo-600 uppercase text-xs md:text-sm tracking-tight">
                                    {{ $j->shift->nama_shift }}
                                </span>
                            </div>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            <span class="font-mono font-black text-gray-600 bg-slate-100 px-3 py-1.5 rounded-lg text-xs md:text-sm border border-slate-200">
                                {{ date('H:i', strtotime($j->shift->jam_masuk)) }}
                            </span>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            <span class="font-mono font-black text-gray-600 bg-slate-100 px-3 py-1.5 rounded-lg text-xs md:text-sm border border-slate-200">
                                {{ date('H:i', strtotime($j->shift->jam_keluar)) }}
                            </span>
                        </td>
                        <td class="p-4 md:p-6 text-center">
                            @if($j->status == 'aktif')
                            <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100 shadow-sm">
                                Wajib Masuk
                            </span>
                            @else
                            <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 border border-rose-100 shadow-sm">
                                Libur
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-20 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-calendar-times text-gray-100 text-7xl mb-4"></i>
                                <p class="text-gray-400 font-black uppercase tracking-widest text-[10px]">Jadwal belum disetel oleh Admin</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MOBILE HELPER & CATATAN -->
    <div class="mt-6 space-y-4">
        <!-- Notif geser (Hanya muncul di HP) -->
        <div class="md:hidden flex items-center justify-center bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
            <i class="fas fa-arrows-alt-h text-indigo-400 mr-2 text-xs"></i>
            <p class="text-[9px] text-gray-500 font-black uppercase tracking-widest">Geser tabel untuk detail jam</p>
        </div>

        <!-- Box Informasi -->
        <div class="p-6 bg-indigo-950 text-white rounded-[2rem] shadow-lg shadow-indigo-100 relative overflow-hidden group">
            <div class="relative z-10">
                <div class="flex items-center mb-2">
                    <i class="fas fa-info-circle text-indigo-400 mr-2"></i>
                    <h4 class="text-xs font-black uppercase tracking-widest">Pusat Informasi Jadwal</h4>
                </div>
                <p class="text-[11px] md:text-xs text-indigo-100 leading-relaxed opacity-80">
                    Jika terdapat ketidaksesuaian hari kerja atau jam operasional shift, harap segera melapor ke bagian <strong>HRD / Admin Zenclock</strong> untuk pembaharuan data.
                </p>
            </div>
            <!-- Dekorasi Ikon Belakang -->
            <i class="fas fa-calendar-alt absolute -right-4 -bottom-4 text-6xl text-white/5 transition-transform group-hover:scale-110 duration-500"></i>
        </div>
    </div>
</div>
@endsection