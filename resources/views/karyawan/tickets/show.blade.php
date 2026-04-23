@extends('layouts.karyawan')
@section('content')
<div class="max-w-7xl mx-auto pb-10 text-black">
    
    <!-- BREADCRUMB & HEADER -->
    <div class="mb-6">
        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">My Tickets > Ticket #TKT-{{ $ticket->id }}</p>
        <h1 class="text-3xl font-black text-slate-800 tracking-tighter">Ticket #TKT-{{ $ticket->id }}: {{ $ticket->subject }}</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- SISI KIRI (60%): TIMELINE & METADATA -->
        <div class="lg:col-span-7 space-y-8">
            
            <!-- RESOLUTION TIMELINE -->
            <div class="bg-white p-10 rounded-[3rem] shadow-sm border border-slate-100">
                <h3 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-8">Resolution Timeline</h3>
                
                <div class="relative">
                    <div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-slate-100"></div>

                    <div class="relative flex items-start mb-10 pl-10">
                        <div class="absolute left-0 w-6 h-6 rounded-full bg-indigo-600 border-4 border-white shadow-sm z-10"></div>
                        <div>
                            <p class="text-sm font-black uppercase text-slate-800">Ticket Submitted</p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $ticket->created_at->format('M d, Y - H:i A') }}</p>
                        </div>
                    </div>

                    <div class="relative flex items-start mb-10 pl-10">
                        <div class="absolute left-0 w-6 h-6 rounded-full {{ $ticket->operator_id ? 'bg-indigo-600' : 'bg-slate-200' }} border-4 border-white shadow-sm z-10"></div>
                        <div>
                            <p class="text-sm font-black uppercase {{ $ticket->operator_id ? 'text-slate-800' : 'text-slate-300' }}">Assigned to Agent</p>
                            @if($ticket->operator_id)
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Petugas: {{ $ticket->operator->nama ?? 'Operator' }}</p>
                            @else
                                <p class="text-[10px] text-slate-300 font-bold uppercase italic">Menunggu Operator...</p>
                            @endif
                        </div>
                    </div>

                    <div class="relative flex items-start mb-10 pl-10">
                        <div class="absolute left-0 w-6 h-6 rounded-full {{ in_array($ticket->status, ['in-progress', 'closed']) ? 'bg-indigo-600 animate-pulse' : 'bg-slate-200' }} border-4 border-white shadow-sm z-10"></div>
                        <div>
                            <p class="text-sm font-black uppercase {{ in_array($ticket->status, ['in-progress', 'closed']) ? 'text-slate-800' : 'text-slate-300' }}">In Progress</p>
                            @if($ticket->status == 'in-progress')
                                <p class="text-[10px] text-indigo-500 font-bold uppercase italic">Operator sedang mengecek kendala Anda</p>
                            @endif
                        </div>
                    </div>

                    <div class="relative flex items-start pl-10">
                        <div class="absolute left-0 w-6 h-6 rounded-full {{ $ticket->status == 'closed' ? 'bg-emerald-500' : 'bg-slate-200' }} border-4 border-white shadow-sm z-10"></div>
                        <div>
                            <p class="text-sm font-black uppercase {{ $ticket->status == 'closed' ? 'text-emerald-600' : 'text-slate-300' }}">Resolved</p>
                            @if($ticket->status == 'closed')
                                <p class="text-[10px] text-emerald-400 font-bold uppercase">{{ $ticket->updated_at->format('M d, Y - H:i A') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase mb-1">Status Tiket</p>
                    <p class="text-sm font-black 
                        @if($ticket->status == 'open') text-yellow-600
                        @elseif($ticket->status == 'in-progress') text-indigo-600
                        @else text-emerald-600 @endif uppercase">
                        {{ $ticket->status }}
                    </p>
                </div>
                <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase mb-1">Prioritas</p>
                    <p class="text-sm font-black {{ $ticket->priority == 'High' ? 'text-rose-500' : ($ticket->priority == 'Mid' ? 'text-amber-500' : 'text-slate-800') }} uppercase">
                        {{ $ticket->priority }} Priority
                    </p>
                </div>
            </div>

            <!-- RATING SECTION - TAMBAHAN BARU -->
            @if($ticket->status != 'open' && !$ticket->rating)
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">
                    <i class="fas fa-star mr-2 text-yellow-500"></i> Beri Rating & Feedback
                </h4>
                <p class="text-xs text-slate-600 mb-4">Apakah solusi dari operator membantu? Berikan penilaian Anda.</p>
                
                <form action="{{ route('karyawan.tickets.rate', $ticket->id) }}" method="POST" id="ratingForm">
                    @csrf
                    <div class="flex items-center gap-2 mb-4">
                        <div class="rating-stars flex gap-1 text-3xl cursor-pointer">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="far fa-star text-slate-300 hover:text-yellow-400 transition" data-score="{{ $i }}"></i>
                            @endfor
                        </div>
                        <input type="hidden" name="score" id="ratingScore" required>
                    </div>
                    
                    <textarea name="feedback" rows="3" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl text-sm" placeholder="Tulis feedback (opsional)..."></textarea>
                    
                    <button type="submit" class="mt-4 bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-indigo-700 transition">
                        Kirim Rating
                    </button>
                </form>
            </div>
            @endif

            <!-- TAMPILKAN RATING YANG SUDAH DIBERIKAN -->
            @if($ticket->rating)
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">
                    <i class="fas fa-check-circle mr-2 text-emerald-500"></i> Rating Anda
                </h4>
                <div class="flex items-center gap-3">
                    <div class="text-2xl text-yellow-400">
                        @for($i=1; $i<=5; $i++)
                            @if($i <= $ticket->rating->score) ★ @else ☆ @endif
                        @endfor
                    </div>
                    <span class="text-sm font-bold text-slate-700">{{ $ticket->rating->score }}/5</span>
                </div>
                @if($ticket->rating->feedback)
                    <p class="mt-3 text-sm text-slate-600 bg-slate-50 p-3 rounded-xl italic">"{{ $ticket->rating->feedback }}"</p>
                @endif
            </div>
            @endif
        </div>

        <!-- SISI KANAN (40%): SUPPORT THREAD (CHAT) -->
        <div class="lg:col-span-5 flex flex-col h-full">
            <div class="bg-white rounded-[3rem] shadow-xl border border-slate-50 flex flex-col h-[600px] overflow-hidden">
                <div class="p-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-[10px] font-black uppercase text-slate-500 tracking-widest">Support Thread</h3>
                    @if($ticket->status != 'closed')
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    @else
                        <span class="text-[8px] font-bold text-emerald-600">SELESAI</span>
                    @endif
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-6 no-scrollbar">
                    
                    <!-- Pesan Awal (Description) -->
                    <div class="flex justify-end">
                        <div class="bg-slate-800 text-white p-4 rounded-t-2xl rounded-bl-2xl max-w-[85%] shadow-md">
                            <p class="text-xs">{{ $ticket->description }}</p>
                            <p class="text-[8px] mt-2 opacity-50 font-bold uppercase text-right">
                                {{ $ticket->reporter->nama ?? 'Anda' }} • {{ $ticket->created_at->format('H:i A') }}
                            </p>
                        </div>
                    </div>

                    @foreach($ticket->responses as $res)
                        @if(in_array($res->responder->role, ['operator', 'admin']))
                            <div class="flex justify-start">
                                <div class="bg-indigo-50 border border-indigo-100 text-slate-800 p-4 rounded-t-2xl rounded-br-2xl max-w-[85%]">
                                    <p class="text-xs">{{ $res->message }}</p>
                                    <p class="text-[8px] mt-2 text-indigo-400 font-bold uppercase">
                                        <i class="fas fa-headset"></i> {{ $res->responder->nama }} • {{ $res->created_at->format('H:i A') }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="flex justify-end">
                                <div class="bg-slate-800 text-white p-4 rounded-t-2xl rounded-bl-2xl max-w-[85%] shadow-md">
                                    <p class="text-xs">{{ $res->message }}</p>
                                    <p class="text-[8px] mt-2 opacity-50 font-bold uppercase text-right">
                                        ME • {{ $res->created_at->format('H:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- INPUT CHAT: Karyawan BISA REPLY (dikembalikan seperti semula) -->
                @if($ticket->status != 'closed')
                <div class="p-6 bg-white border-t border-slate-50">
                    <form action="{{ route('karyawan.tickets.reply', $ticket->id) }}" method="POST">
                        @csrf
                        <div class="relative">
                            <input type="text" name="message" class="w-full pl-6 pr-32 py-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-500/5 transition-all text-xs font-bold" placeholder="Tulis balasan...">
                            <button type="submit" class="absolute right-2 top-2 bottom-2 px-6 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all">Send <i class="fas fa-paper-plane ml-1"></i></button>
                        </div>
                    </form>
                </div>
                @else
                <div class="p-6 bg-emerald-50 text-center">
                    <p class="text-[10px] font-black text-emerald-600 uppercase">Tiket Selesai Dikerjakan</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    // Star Rating JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('.rating-stars i');
        const ratingInput = document.getElementById('ratingScore');
        
        if (!stars.length || !ratingInput) return;
        
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const score = this.getAttribute('data-score');
                ratingInput.value = score;
                
                stars.forEach((s, index) => {
                    if (index < score) {
                        s.className = 'fas fa-star text-yellow-400';
                    } else {
                        s.className = 'far fa-star text-slate-300';
                    }
                });
            });
            
            star.addEventListener('mouseenter', function() {
                const score = this.getAttribute('data-score');
                stars.forEach((s, index) => {
                    if (index < score) {
                        s.className = 'fas fa-star text-yellow-400';
                    } else {
                        s.className = 'far fa-star text-slate-300';
                    }
                });
            });
        });
        
        document.querySelector('.rating-stars')?.addEventListener('mouseleave', function() {
            const currentScore = ratingInput.value;
            stars.forEach((s, index) => {
                if (currentScore && index < currentScore) {
                    s.className = 'fas fa-star text-yellow-400';
                } else {
                    s.className = 'far fa-star text-slate-300';
                }
            });
        });
    });
</script>

<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection