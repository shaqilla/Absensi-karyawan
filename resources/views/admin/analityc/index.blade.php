@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-black uppercase tracking-tighter text-slate-800">Analitik Helpdesk</h1>
        <p class="text-slate-500 text-sm">Dashboard performa operator & kepuasan pelapor</p>
    </div>

    <!-- Statistik Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Tiket</p>
                    <p class="text-3xl font-black text-indigo-600 mt-1">{{ $totalTickets }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-ticket-alt text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tiket Selesai</p>
                    <p class="text-3xl font-black text-emerald-600 mt-1">{{ $closedTickets }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Rata-rata Respon</p>
                    <p class="text-3xl font-black text-amber-600 mt-1">{{ $avgResponseTime }} <span class="text-sm">jam</span></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Rata-rata Rating</p>
                    <p class="text-3xl font-black text-purple-600 mt-1">{{ number_format($avgRating, 1) }} <span class="text-sm">/5</span></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-star text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Performa Operator (Poin 4 Ujikom) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">
                <i class="fas fa-chart-simple mr-2 text-indigo-500"></i> Performa Operator Helpdesk
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider">Operator</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider">Tiket Ditangani</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider">Rata-rata Respon</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider">Rata-rata Resolusi</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider">Rata-rata Rating</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider">Status SLA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($operatorPerformance as $op)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-indigo-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-user-tie text-indigo-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">{{ $op->name }}</p>
                                    <p class="text-[9px] text-slate-400 uppercase">{{ $op->role }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-slate-700">{{ $op->total_handled }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($op->avg_response_hours)
                                <span class="text-sm font-bold {{ $op->avg_response_hours <= 4 ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ number_format($op->avg_response_hours, 1) }} jam
                                </span>
                            @else
                                <span class="text-sm text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($op->avg_resolution_hours)
                                <span class="text-sm text-slate-700">{{ number_format($op->avg_resolution_hours, 1) }} jam</span>
                            @else
                                <span class="text-sm text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($op->avg_rating)
                                <div class="flex items-center gap-1">
                                    <span class="text-sm font-bold text-purple-600">{{ number_format($op->avg_rating, 1) }}</span>
                                    <div class="flex text-yellow-400 text-xs">
                                        @for($i=1; $i<=5; $i++)
                                            @if($i <= round($op->avg_rating)) ★ @else ☆ @endif
                                        @endfor
                                    </div>
                                </div>
                            @else
                                <span class="text-sm text-slate-400">Belum ada rating</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $slaMet = $op->sla_met_count ?? 0;
                                $slaTotal = $op->sla_total_count ?? 0;
                                $slaPercent = $slaTotal > 0 ? round(($slaMet / $slaTotal) * 100) : 0;
                            @endphp
                            @if($slaTotal > 0)
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $slaPercent }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-bold {{ $slaPercent >= 80 ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $slaPercent }}%
                                    </span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="fas fa-chart-line text-3xl mb-2 block"></i>
                            Belum ada data operator
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Distribusi Rating & SLA Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Rating Distribution -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">
                <i class="fas fa-star mr-2 text-yellow-500"></i> Distribusi Rating Pelapor
            </h4>
            <div class="space-y-3">
                @foreach($ratingDistribution as $score => $count)
                <div>
                    <div class="flex justify-between text-[10px] font-bold mb-1">
                        <span>{{ $score }} ★</span>
                        <span>{{ $count }} tiket</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-yellow-400 h-full rounded-full" style="width: {{ $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- SLA Summary -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">
                <i class="fas fa-hourglass-half mr-2 text-indigo-500"></i> Ringkasan SLA
            </h4>
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-4 bg-emerald-50 rounded-xl">
                    <p class="text-2xl font-black text-emerald-600">{{ $slaMetCount }}</p>
                    <p class="text-[9px] font-bold text-emerald-700 uppercase">SLA Terpenuhi</p>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-xl">
                    <p class="text-2xl font-black text-red-600">{{ $slaBreachedCount }}</p>
                    <p class="text-[9px] font-bold text-red-700 uppercase">SLA Terlewat</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection