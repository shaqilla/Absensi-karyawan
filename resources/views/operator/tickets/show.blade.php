@extends($layout)

@section('content')
<div class="max-w-7xl mx-auto p-6 text-black">
    <!-- Header info -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter">Detail Tiket #{{ $ticket->id }}</h1>
            <p class="text-slate-500 text-sm italic">Status: <span class="font-bold text-indigo-600 uppercase">{{ $ticket->status }}</span></p>
        </div>
        @if($ticket->status !== 'closed')
        <form action="{{ route('operator.tickets.status', $ticket->id) }}" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="closed">
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg">Tutup Tiket</button>
        </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Panel Kiri: Chat History -->
        <div class="lg:col-span-3 flex flex-col h-[600px] bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="flex-1 overflow-y-auto p-8 space-y-6 no-scrollbar">
                <!-- Aduan Awal Karyawan -->
                <div class="flex justify-start">
                    <div class="max-w-[80%] bg-slate-100 p-5 rounded-[1.5rem] rounded-tl-none">
                        <p class="text-[10px] font-black text-indigo-500 uppercase mb-1">{{ $ticket->reporter->nama }} (PELAPOR)</p>
                        <p class="text-sm font-bold text-slate-800 mb-2">{{ $ticket->subject }}</p>
                        <p class="text-xs text-slate-600">{{ $ticket->description }}</p>
                    </div>
                </div>

                <!-- Balasan-Balasan -->
                @foreach($ticket->responses as $res)
                <div class="flex {{ $res->responder_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] {{ $res->responder_id == auth()->id() ? 'bg-indigo-600 text-white' : 'bg-white border text-slate-800' }} p-4 rounded-[1.5rem] {{ $res->responder_id == auth()->id() ? 'rounded-tr-none' : 'rounded-tl-none' }} shadow-sm">
                        <p class="text-[8px] font-black uppercase opacity-60 mb-1">{{ $res->responder->nama }}</p>
                        <p class="text-sm">{{ $res->message }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Form Balas -->
            <div class="p-6 border-t border-slate-50 bg-slate-50/50">
                <form action="{{ route('operator.tickets.reply', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="flex gap-4">
                        <textarea name="message" id="replyField" rows="1" class="flex-1 p-4 rounded-2xl border-none outline-none focus:ring-2 focus:ring-indigo-500 text-sm shadow-sm" placeholder="Tulis jawaban atau instruksi..." required></textarea>
                        <button type="submit" class="w-14 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel Kanan: Metadata & Suggestions -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Saran Jawaban (POIN 3) -->
            <div class="bg-slate-900 p-6 rounded-[2rem] text-white shadow-xl">
                <h4 class="text-xs font-black uppercase text-indigo-400 mb-4 tracking-widest">Saran Jawaban</h4>
                <div class="space-y-3">
                    @foreach($suggestions as $key => $val)
                    <button onclick="document.getElementById('replyField').value = '{{ $val }}'" class="w-full text-left p-3 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition">
                        <p class="text-[10px] font-black text-indigo-300">{{ $key }}</p>
                        <p class="text-[9px] opacity-60 line-clamp-1">{{ $val }}</p>
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- SLA Info -->
            <div class="bg-white p-6 rounded-[2rem] border shadow-sm">
                <h4 class="text-[10px] font-black uppercase text-slate-400 mb-4 tracking-widest">SLA Tracking</h4>
                <div class="space-y-4">
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase">Waktu Lapor</p>
                        <p class="text-xs font-bold">{{ $ticket->created_at->format('H:i') }} ({{ $ticket->created_at->diffForHumans() }})</p>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase">Prioritas</p>
                        <span class="text-[10px] font-black text-rose-600 uppercase">{{ $ticket->priority }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection@extends($layout)

@section('content')
<div class="max-w-7xl mx-auto p-6 text-black">
    <!-- Header info -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter">Detail Tiket #{{ $ticket->id }}</h1>
            <p class="text-slate-500 text-sm italic">Status: <span class="font-bold text-indigo-600 uppercase">{{ $ticket->status }}</span></p>
        </div>
        @if($ticket->status !== 'closed')
        <form action="{{ route('operator.tickets.status', $ticket->id) }}" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="closed">
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg">Tutup Tiket</button>
        </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Panel Kiri: Chat History -->
        <div class="lg:col-span-3 flex flex-col h-[600px] bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="flex-1 overflow-y-auto p-8 space-y-6 no-scrollbar">
                <!-- Aduan Awal Karyawan -->
                <div class="flex justify-start">
                    <div class="max-w-[80%] bg-slate-100 p-5 rounded-[1.5rem] rounded-tl-none">
                        <p class="text-[10px] font-black text-indigo-500 uppercase mb-1">{{ $ticket->reporter->nama }} (PELAPOR)</p>
                        <p class="text-sm font-bold text-slate-800 mb-2">{{ $ticket->subject }}</p>
                        <p class="text-xs text-slate-600">{{ $ticket->description }}</p>
                    </div>
                </div>

                <!-- Balasan-Balasan -->
                @foreach($ticket->responses as $res)
                <div class="flex {{ $res->responder_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] {{ $res->responder_id == auth()->id() ? 'bg-indigo-600 text-white' : 'bg-white border text-slate-800' }} p-4 rounded-[1.5rem] {{ $res->responder_id == auth()->id() ? 'rounded-tr-none' : 'rounded-tl-none' }} shadow-sm">
                        <p class="text-[8px] font-black uppercase opacity-60 mb-1">{{ $res->responder->nama }}</p>
                        <p class="text-sm">{{ $res->message }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Form Balas -->
            <div class="p-6 border-t border-slate-50 bg-slate-50/50">
                <form action="{{ route('operator.tickets.reply', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="flex gap-4">
                        <textarea name="message" id="replyField" rows="1" class="flex-1 p-4 rounded-2xl border-none outline-none focus:ring-2 focus:ring-indigo-500 text-sm shadow-sm" placeholder="Tulis jawaban atau instruksi..." required></textarea>
                        <button type="submit" class="w-14 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel Kanan: Metadata & Suggestions -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Saran Jawaban (POIN 3) -->
            <div class="bg-slate-900 p-6 rounded-[2rem] text-white shadow-xl">
                <h4 class="text-xs font-black uppercase text-indigo-400 mb-4 tracking-widest">Saran Jawaban</h4>
                <div class="space-y-3">
                    @foreach($suggestions as $key => $val)
                    <button onclick="document.getElementById('replyField').value = '{{ $val }}'" class="w-full text-left p-3 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition">
                        <p class="text-[10px] font-black text-indigo-300">{{ $key }}</p>
                        <p class="text-[9px] opacity-60 line-clamp-1">{{ $val }}</p>
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- SLA Info -->
            <div class="bg-white p-6 rounded-[2rem] border shadow-sm">
                <h4 class="text-[10px] font-black uppercase text-slate-400 mb-4 tracking-widest">SLA Tracking</h4>
                <div class="space-y-4">
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase">Waktu Lapor</p>
                        <p class="text-xs font-bold">{{ $ticket->created_at->format('H:i') }} ({{ $ticket->created_at->diffForHumans() }})</p>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase">Prioritas</p>
                        <span class="text-[10px] font-black text-rose-600 uppercase">{{ $ticket->priority }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection@extends($layout)

@section('content')
<div class="max-w-7xl mx-auto p-6 text-black">
    <!-- Header info -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter">Detail Tiket #{{ $ticket->id }}</h1>
            <p class="text-slate-500 text-sm italic">Status: <span class="font-bold text-indigo-600 uppercase">{{ $ticket->status }}</span></p>
        </div>
        @if($ticket->status !== 'closed')
        <form action="{{ route('operator.tickets.status', $ticket->id) }}" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="closed">
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg">Tutup Tiket</button>
        </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Panel Kiri: Chat History -->
        <div class="lg:col-span-3 flex flex-col h-[600px] bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="flex-1 overflow-y-auto p-8 space-y-6 no-scrollbar">
                <!-- Aduan Awal Karyawan -->
                <div class="flex justify-start">
                    <div class="max-w-[80%] bg-slate-100 p-5 rounded-[1.5rem] rounded-tl-none">
                        <p class="text-[10px] font-black text-indigo-500 uppercase mb-1">{{ $ticket->reporter->nama }} (PELAPOR)</p>
                        <p class="text-sm font-bold text-slate-800 mb-2">{{ $ticket->subject }}</p>
                        <p class="text-xs text-slate-600">{{ $ticket->description }}</p>
                    </div>
                </div>

                <!-- Balasan-Balasan -->
                @foreach($ticket->responses as $res)
                <div class="flex {{ $res->responder_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] {{ $res->responder_id == auth()->id() ? 'bg-indigo-600 text-white' : 'bg-white border text-slate-800' }} p-4 rounded-[1.5rem] {{ $res->responder_id == auth()->id() ? 'rounded-tr-none' : 'rounded-tl-none' }} shadow-sm">
                        <p class="text-[8px] font-black uppercase opacity-60 mb-1">{{ $res->responder->nama }}</p>
                        <p class="text-sm">{{ $res->message }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Form Balas -->
            <div class="p-6 border-t border-slate-50 bg-slate-50/50">
                <form action="{{ route('operator.tickets.reply', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="flex gap-4">
                        <textarea name="message" id="replyField" rows="1" class="flex-1 p-4 rounded-2xl border-none outline-none focus:ring-2 focus:ring-indigo-500 text-sm shadow-sm" placeholder="Tulis jawaban atau instruksi..." required></textarea>
                        <button type="submit" class="w-14 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel Kanan: Metadata & Suggestions -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Saran Jawaban (POIN 3) -->
            <div class="bg-slate-900 p-6 rounded-[2rem] text-white shadow-xl">
                <h4 class="text-xs font-black uppercase text-indigo-400 mb-4 tracking-widest">Saran Jawaban</h4>
                <div class="space-y-3">
                    @foreach($suggestions as $key => $val)
                    <button onclick="document.getElementById('replyField').value = '{{ $val }}'" class="w-full text-left p-3 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition">
                        <p class="text-[10px] font-black text-indigo-300">{{ $key }}</p>
                        <p class="text-[9px] opacity-60 line-clamp-1">{{ $val }}</p>
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- SLA Info -->
            <div class="bg-white p-6 rounded-[2rem] border shadow-sm">
                <h4 class="text-[10px] font-black uppercase text-slate-400 mb-4 tracking-widest">SLA Tracking</h4>
                <div class="space-y-4">
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase">Waktu Lapor</p>
                        <p class="text-xs font-bold">{{ $ticket->created_at->format('H:i') }} ({{ $ticket->created_at->diffForHumans() }})</p>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase">Prioritas</p>
                        <span class="text-[10px] font-black text-rose-600 uppercase">{{ $ticket->priority }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection