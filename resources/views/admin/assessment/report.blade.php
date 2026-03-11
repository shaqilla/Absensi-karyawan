@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Laporan Penilaian</h1>
            <p class="text-gray-400 text-sm italic">Rekap & grafik perkembangan sikap karyawan</p>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 mb-6">
        <form method="GET" action="{{ route('admin.assessment.report') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">Filter Periode</label>
                <select name="period" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-black min-w-[180px]">
                    <option value="">-- Semua Periode --</option>
                    @foreach($periods as $p)
                    <option value="{{ $p }}" {{ request('period') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            @if(request('period'))
            <a href="{{ route('admin.assessment.report') }}"
                class="bg-gray-100 text-gray-500 px-5 py-3 rounded-xl text-xs font-black uppercase hover:bg-gray-200 transition">
                <i class="fas fa-times mr-1"></i> Reset
            </a>
            @endif
        </form>
    </div>

    @if($assessments->isEmpty())
    {{-- Empty State --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-16 text-center text-gray-400">
        <i class="fas fa-inbox text-5xl mb-4 block"></i>
        <p class="font-bold text-lg mb-2">Belum Ada Data Penilaian</p>
        <p class="text-sm">Silakan lakukan penilaian karyawan terlebih dahulu.</p>
        <a href="{{ route('admin.assessment.employees') }}" 
           class="inline-block mt-6 bg-indigo-600 text-white px-8 py-4 rounded-2xl font-bold hover:bg-indigo-700 transition text-xs uppercase tracking-widest">
            <i class="fas fa-plus mr-2"></i> Nilai Karyawan
        </a>
    </div>
@else

    {{-- Baris Atas: Radar Chart + Rata-rata Per Kategori --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Radar Chart --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
            <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight mb-6">
                <i class="fas fa-spider text-indigo-400 mr-2"></i> Grafik Radar Sikap
                @if(request('period'))
                <span class="bg-indigo-100 text-indigo-600 text-[10px] font-black px-3 py-1 rounded-full uppercase ml-2">{{ request('period') }}</span>
                @endif
            </h2>
            <div class="flex justify-center">
                <canvas id="radarChart" style="max-width: 320px; max-height: 320px;"></canvas>
            </div>
        </div>

        {{-- Rata-rata Per Kategori --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
            <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight mb-6">
                <i class="fas fa-chart-bar text-indigo-400 mr-2"></i> Rata-rata per Kategori
            </h2>
            <div class="space-y-4">
                @foreach($avgPerCategory as $item)
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-sm text-gray-700 uppercase">{{ $item['category'] }}</span>
                        <div class="text-right">
                            <span class="font-black text-sm {{ $item['average'] >= 4 ? 'text-green-600' : ($item['average'] >= 3 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ number_format($item['average'], 2) }} / 5
                            </span>
                            <span class="text-[10px] text-gray-400 ml-2">({{ $item['total'] }}x)</span>
                        </div>
                    </div>
                    @php $width = ($item['average'] / 5) * 100; @endphp
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all duration-700
                            {{ $item['average'] >= 4 ? 'bg-green-500' : ($item['average'] >= 3 ? 'bg-yellow-500' : 'bg-red-500') }}"
                            style="width: {{ $width }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Timeline Riwayat Penilaian --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 mb-6">
        <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight mb-6">
            <i class="fas fa-history text-indigo-400 mr-2"></i> Riwayat Penilaian
        </h2>
        <div class="space-y-4">
            @foreach($assessments as $assessment)
            @php $avg = $assessment->details->avg('score') ?? 0; @endphp
            <div class="flex gap-4 p-4 rounded-2xl hover:bg-gray-50 transition border border-gray-100">

                {{-- Avatar --}}
                <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center font-black text-lg flex-shrink-0">
                    {{ strtoupper(substr($assessment->user->nama ?? '?', 0, 1)) }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap justify-between items-start gap-2">
                        <div>
                            <div class="font-black text-sm uppercase text-gray-800">{{ $assessment->user->nama ?? '-' }}</div>
                            <div class="text-[10px] text-gray-400 uppercase">{{ $assessment->user->karyawan->jabatan ?? '-' }}</div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                {{ $avg >= 4 ? 'bg-green-100 text-green-600' : ($avg >= 3 ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') }}">
                                ★ {{ number_format($avg, 1) }} / 5
                            </span>
                            <span class="text-[10px] text-gray-400 font-semibold">
                                {{ \Carbon\Carbon::parse($assessment->assessment_date)->format('d M Y') }}
                            </span>
                        </div>
                    </div>

                    {{-- Periode --}}
                    <div class="mt-1">
                        <span class="bg-indigo-50 text-indigo-500 text-[10px] font-black px-2 py-0.5 rounded-lg uppercase">
                            {{ $assessment->period }}
                        </span>
                    </div>

                    {{-- Nilai per kategori --}}
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($assessment->details as $detail)
                        <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-1 rounded-lg">
                            {{ $detail->question->category->name ?? '-' }}:
                            @for($s = 1; $s <= 5; $s++)
                                <span class="{{ $detail->score >= $s ? 'text-yellow-500' : 'text-gray-300' }}">★</span>
                            @endfor
                        </span>
                        @endforeach
                    </div>

                    {{-- Catatan --}}
                    @if($assessment->general_notes && $assessment->general_notes != '-')
                    <div class="mt-2 bg-gray-50 rounded-xl px-3 py-2 text-xs text-gray-500 italic border-l-4 border-indigo-200">
                        "{{ \Illuminate\Support\Str::limit($assessment->general_notes, 120) }}"
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Tabel Ringkas --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight">Tabel Ringkasan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-black">
                <thead>
                    <tr class="bg-gray-50 border-b text-[10px] font-black text-gray-400 uppercase">
                        <th class="p-5">#</th>
                        <th class="p-5">Karyawan</th>
                        <th class="p-5">Periode</th>
                        <th class="p-5">Rata-rata</th>
                        <th class="p-5">Catatan</th>
                        <th class="p-5">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($assessments as $i => $assessment)
                    @php $avg = $assessment->details->avg('score') ?? 0; @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-5 text-sm text-gray-400 font-bold">{{ $i + 1 }}</td>
                        <td class="p-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center font-black text-sm flex-shrink-0">
                                    {{ strtoupper(substr($assessment->user->nama ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-sm uppercase">{{ $assessment->user->nama ?? '-' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $assessment->user->karyawan->jabatan ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-5 text-sm font-semibold text-gray-600">{{ $assessment->period }}</td>
                        <td class="p-5">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                {{ $avg >= 4 ? 'bg-green-100 text-green-600' : ($avg >= 3 ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') }}">
                                ★ {{ number_format($avg, 1) }} / 5
                            </span>
                        </td>
                        <td class="p-5 text-xs text-gray-500 max-w-xs">
                            {{ $assessment->general_notes && $assessment->general_notes != '-' ? \Illuminate\Support\Str::limit($assessment->general_notes, 60) : '-' }}
                        </td>
                        <td class="p-5 text-xs text-gray-400 font-semibold">
                            {{ \Carbon\Carbon::parse($assessment->assessment_date)->format('d M Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @endif
</div>

{{-- Chart.js untuk Radar Chart --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const radarData = @json($avgPerCategory);

if (radarData.length > 0) {
    const ctx = document.getElementById('radarChart').getContext('2d');
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: radarData.map(item => item.category),
            datasets: [{
                label: 'Rata-rata Nilai',
                data: radarData.map(item => item.average),
                backgroundColor: 'rgba(99, 102, 241, 0.15)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                pointRadius: 5,
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    min: 0,
                    max: 5,
                    ticks: { stepSize: 1, display: false },
                    pointLabels: { font: { size: 11, weight: 'bold' } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}
</script>
@endsection