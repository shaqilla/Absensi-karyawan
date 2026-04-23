@extends('layouts.admin') 
@section('content')
<div class="p-6">
    <h1 class="text-2xl font-black mb-6 uppercase tracking-tighter">Antrian Tiket Helpdesk</h1>

    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="p-4 text-[10px] font-black uppercase text-slate-400">ID Tiket</th>
                    <th class="p-4 text-[10px] font-black uppercase text-slate-400">Pelapor</th>
                    <th class="p-4 text-[10px] font-black uppercase text-slate-400">Subjek</th>
                    <th class="p-4 text-[10px] font-black uppercase text-slate-400">Prioritas</th>
                    <th class="p-4 text-[10px] font-black uppercase text-slate-400">Status</th>
                    <th class="p-4 text-[10px] font-black uppercase text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $t)
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="p-4 text-xs font-bold text-indigo-600">#TKT-{{ $t->id }}</td>
                    <td class="p-4 text-xs font-bold">{{ $t->reporter->nama }}</td>
                    <td class="p-4 text-xs font-medium">{{ $t->subject }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded text-[9px] font-black uppercase {{ $t->priority == 'High' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                            {{ $t->priority }}
                        </span>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded text-[9px] font-black uppercase bg-slate-100 text-slate-600">
                            {{ $t->status }}
                        </span>
                    </td>
                    <td class="p-4">
                        <a href="{{ route('operator.tickets.show', $t->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-[10px] font-black uppercase">Proses</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection