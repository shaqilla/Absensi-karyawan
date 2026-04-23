@extends($layout) {{-- Pake variable layout dari Controller --}}

@section('content')
<div class="w-full pb-10 text-black">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter">
                {{ in_array(auth()->user()->role, ['admin', 'operator']) ? 'Antrian Tiket Helpdesk' : 'Tiket Saya' }}
            </h1>
            <p class="text-slate-500 text-sm">
                {{ in_array(auth()->user()->role, ['admin', 'operator']) ? 'Kelola dan respon kendala dari karyawan.' : 'Kelola semua permohonan dukungan teknis Anda.' }}
            </p>
        </div>

        {{-- CUMA KARYAWAN yang bisa liat tombol Buat Tiket --}}
        @if(auth()->user()->role == 'karyawan')
            <a href="{{ route('karyawan.tickets.create') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg">
                <i class="fas fa-plus mr-2"></i> Buat Tiket Baru
            </a>
        @endif
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl"><i class="fas fa-ticket-alt"></i></div>
            <div>
                <h3 class="text-2xl font-black">{{ $tickets->count() }}</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Tiket</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl"><i class="fas fa-sync"></i></div>
            <div>
                <h3 class="text-2xl font-black">{{ $tickets->where('status', 'in-progress')->count() }}</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sedang Diproses</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl"><i class="fas fa-check-circle"></i></div>
            <div>
                <h3 class="text-2xl font-black">{{ $tickets->where('status', 'closed')->count() }}</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Selesai</p>
            </div>
        </div>
    </div>

    <!-- LIST TIKET -->
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50 flex gap-4 overflow-x-auto no-scrollbar">
            <button class="text-[10px] font-black uppercase text-indigo-600 border-b-2 border-indigo-600 pb-1 whitespace-nowrap">Semua Tiket</button>
            <button class="text-[10px] font-black uppercase text-slate-400 hover:text-indigo-600 whitespace-nowrap">Menunggu Balasan</button>
            <button class="text-[10px] font-black uppercase text-slate-400 hover:text-indigo-600 whitespace-nowrap">Sudah Selesai</button>
        </div>
        
        <div class="divide-y divide-slate-50">
            @forelse($tickets as $t)
            <div class="p-8 hover:bg-slate-50 transition flex flex-col md:flex-row items-center justify-between group gap-6">
                
                <!-- SISI KIRI: INFO MASALAH -->
                <div class="flex-1 w-full">
                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                        <span class="text-[10px] font-black text-indigo-500 uppercase">#TKT-{{ $t->id }}</span>
                        
                        {{-- Status Badge --}}
                        @php
                            $statusColor = [
                                'open' => 'bg-blue-50 text-blue-600',
                                'in-progress' => 'bg-amber-50 text-amber-600',
                                'closed' => 'bg-emerald-50 text-emerald-600'
                            ];
                        @endphp
                        <span class="{{ $statusColor[$t->status] ?? 'bg-slate-50' }} px-2 py-0.5 rounded font-black text-[9px] uppercase">
                            {{ $t->status }}
                        </span>

                        <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded font-black text-[9px] uppercase">{{ $t->priority }}</span>
                        
                        {{-- Nama Pelapor (Cuma muncul di sisi Admin/Operator) --}}
                        @if(in_array(auth()->user()->role, ['admin', 'operator']))
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Oleh: {{ $t->reporter->nama ?? 'User' }}</span>
                        @endif
                    </div>
                    
                    <h4 class="font-black text-slate-800 text-lg group-hover:text-indigo-600 transition">{{ $t->subject }}</h4>
                    <p class="text-xs text-slate-400 mt-1 italic">
                        Dibuat {{ $t->created_at->diffForHumans() }} 
                        @if($t->operator)
                         • Ditangani oleh <b class="text-indigo-500">{{ $t->operator->nama }}</b>
                        @endif
                    </p>
                </div>

                <!-- SISI TENGAH: KOTAK PROGRES -->
                <div class="flex flex-col items-end min-w-[200px] w-full md:w-auto">
                    @php
                        $step = 1; 
                        $label = "SUBMITTED";
                        
                        if($t->status == 'closed') {
                            $step = 4;
                            $label = "RESOLVED";
                        } elseif($t->status == 'in-progress') {
                            $step = 3;
                            $label = "ON PROGRESS";
                        } elseif($t->operator_id) {
                            $step = 2;
                            $label = "ASSIGNED";
                        }
                    @endphp

                    <div class="flex items-center justify-between w-full mb-2">
                        <span class="text-[10px] font-black text-slate-800 uppercase tracking-widest">{{ $label }}</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Step {{ $step }} of 4</span>
                    </div>

                    <div class="flex gap-1.5 w-full">
                        @for($i = 1; $i <= 4; $i++)
                            <div class="h-1.5 flex-1 rounded-full {{ $i <= $step ? ($step == 4 ? 'bg-emerald-500' : 'bg-indigo-600') : 'bg-slate-200' }}"></div>
                        @endfor
                    </div>
                </div>

                <!-- SISI KANAN: AKSI -->
                <div class="flex items-center gap-4">
                    {{-- Route dinamis berdasarkan Role login --}}
                    @php
                        $detailRoute = in_array(auth()->user()->role, ['admin', 'operator']) 
                                       ? route('operator.tickets.show', $t->id) 
                                       : route('karyawan.tickets.show', $t->id);
                    @endphp

                    <a href="{{ $detailRoute }}" class="px-6 py-3 rounded-2xl bg-slate-100 text-slate-600 font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition shadow-sm flex items-center gap-2">
                        Detail <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="p-20 text-center">
                <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-3xl flex items-center justify-center text-3xl mx-auto mb-4">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3 class="font-black text-slate-400 uppercase text-sm tracking-widest">Belum ada tiket</h3>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection