@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Laporan Penilaian</h1>
            <p class="text-gray-400 text-sm italic font-medium lowercase">Rekap & grafik perkembangan sikap karyawan</p>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 px-4 py-3 rounded-xl mb-6 text-xs font-bold uppercase">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 mb-6 no-print">
        <form method="GET" action="{{ route('admin.assessment.report') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Filter Periode</label>
                <select name="period" onchange="this.form.submit()"
                    class="w-full border border-gray-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-bold text-gray-700 bg-gray-50">
                    <option value="">-- Semua Periode --</option>
                    @foreach($periods as $p)
                    <option value="{{ $p }}" {{ request('period') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            @if(request('period'))
            <a href="{{ route('admin.assessment.report') }}"
                class="bg-gray-100 text-gray-500 px-5 py-3 rounded-xl text-[10px] font-black uppercase hover:bg-gray-200 transition">
                <i class="fas fa-times mr-1"></i> Reset
            </a>
            @endif
        </form>
    </div>

    @if($assessments->isEmpty())
    {{-- Empty State --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-16 text-center text-gray-300">
        <i class="fas fa-inbox text-5xl mb-4 block opacity-20"></i>
        <p class="font-black text-sm uppercase tracking-widest mb-2">Belum Ada Data Penilaian</p>
        <p class="text-xs italic mb-6 text-gray-400">Silakan lakukan penilaian karyawan terlebih dahulu.</p>
        <a href="{{ route('admin.assessment.employees') }}"
            class="inline-block bg-indigo-600 text-white px-8 py-3 rounded-2xl font-black hover:bg-indigo-700 transition text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-100">
            <i class="fas fa-pen-nib mr-2"></i> Mulai Menilai
        </a>
    </div>
    @else

    {{-- Baris Atas: Radar Chart + Rata-rata Per Kategori --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

        {{-- Radar Chart --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 flex flex-col items-center">
            <h2 class="w-full text-sm font-black text-gray-800 uppercase tracking-widest mb-8 flex items-center">
                <i class="fas fa-chart-pie text-indigo-400 mr-3"></i> Grafik Radar Sikap
                @if(request('period'))
                <span class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-3 py-1 rounded-lg uppercase ml-3 border border-indigo-100">{{ request('period') }}</span>
                @endif
            </h2>
            <div style="height: 320px; width: 100%; position: relative;">
                <canvas id="radarChart"></canvas>
            </div>
        </div>

        {{-- Rata-rata Per Kategori --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8">
            <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest mb-8">
                <i class="fas fa-chart-bar text-indigo-400 mr-3"></i> Rata-rata per Kategori
            </h2>
            <div class="space-y-6">
                @foreach($avgPerCategory as $item)
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-black text-[10px] text-gray-500 uppercase tracking-widest">{{ $item['category'] }}</span>
                        <div class="text-right">
                            <span class="font-black text-sm {{ $item['average'] >= 4 ? 'text-emerald-600' : ($item['average'] >= 3 ? 'text-amber-500' : 'text-rose-600') }}">
                                {{ number_format($item['average'], 2) }} <span class="text-[10px] opacity-40">/ 5</span>
                            </span>
                        </div>
                    </div>
                    @php $width = ($item['average'] / 5) * 100; @endphp
                    <div class="w-full bg-gray-50 rounded-full h-3 border border-gray-100 overflow-hidden shadow-inner">
                        <div class="h-3 rounded-full transition-all duration-1000 ease-out
                            {{ $item['average'] >= 4 ? 'bg-emerald-500' : ($item['average'] >= 3 ? 'bg-amber-500' : 'bg-rose-500') }}"
                            style="width: {{ $width }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Timeline Riwayat Penilaian --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 mb-8">
        <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest mb-8">
            <i class="fas fa-history text-indigo-400 mr-3"></i> Timeline Evaluasi Terbaru
        </h2>
        <div class="space-y-6">
            @foreach($assessments as $assessment)
            @php $avg = $assessment->details->avg('score') ?? 0; @endphp
            <div class="flex flex-col md:flex-row gap-6 p-6 rounded-3xl hover:bg-indigo-50/30 transition-all border border-gray-50 hover:border-indigo-100">

                {{-- Avatar --}}
                <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl flex-shrink-0 border-2 border-white shadow-sm">
                    {{ strtoupper(substr($assessment->evaluatee->nama ?? '?', 0, 1)) }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap justify-between items-start gap-2 mb-3">
                        <div>
                            <div class="font-black text-sm uppercase text-gray-800 tracking-tight">{{ $assessment->evaluatee->nama ?? '-' }}</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $assessment->evaluatee->karyawan->jabatan ?? '-' }}</div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest border
                                {{ $avg >= 4 ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : ($avg >= 3 ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-rose-50 text-rose-600 border-rose-100') }}">
                                {{ number_format($avg, 1) }} / 5.0
                            </span>
                            <span class="text-[10px] text-gray-400 font-black italic">
                                {{ \Carbon\Carbon::parse($assessment->assessment_date)->format('d M Y') }}
                            </span>
                        </div>
                    </div>

                    {{-- Nilai per kategori --}}
                    <div class="flex flex-wrap gap-2">
                        @foreach($assessment->details as $detail)
                        <span class="bg-white text-gray-600 text-[9px] font-black uppercase px-3 py-1.5 rounded-lg border border-gray-100 shadow-sm">
                            {{ $detail->question->category->name ?? '-' }}:
                            <span class="text-amber-500 ml-1">
                                {{ $detail->score }}★
                            </span>
                        </span>
                        @endforeach
                    </div>

                    {{-- Catatan --}}
                    @if($assessment->general_notes && $assessment->general_notes != '-')
                    <div class="mt-4 bg-white/50 rounded-2xl p-4 text-xs text-gray-500 italic border border-dashed border-indigo-100">
                        "{{ \Illuminate\Support\Str::limit($assessment->general_notes, 150) }}"
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radarData = @json($avgPerCategory);

        if (radarData && radarData.length > 0) {
            const ctx = document.getElementById('radarChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: radarData.map(item => item.category),
                    datasets: [{
                        label: 'Skor Rata-rata',
                        data: radarData.map(item => item.average),
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 3,
                        pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                        pointBorderColor: '#fff',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            min: 0,
                            max: 5,
                            ticks: {
                                stepSize: 1,
                                display: false
                            },
                            pointLabels: {
                                font: {
                                    size: 10,
                                    weight: 'bold',
                                    family: 'sans-serif'
                                },
                                color: '#64748b'
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            angleLines: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>
@endsection 