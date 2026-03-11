@extends('layouts.karyawan')

@section('content')
<div class="w-full pb-10">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">
                <i class="fas fa-chart-line text-indigo-600 mr-3"></i> Rapor Performa
            </h1>
            <p class="text-gray-400 text-sm italic">{{ $targetUser->nama ?? auth()->user()->nama }}</p>
        </div>
        <a href="{{ route('karyawan.dashboard') }}"
            class="bg-gray-100 text-gray-500 px-6 py-2 rounded-xl font-bold hover:bg-gray-200 transition text-xs uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    {{-- Filter Periode --}}
    @if(isset($periods) && $periods->isNotEmpty())
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 mb-6">
        <form method="GET" action="{{ route('karyawan.rapor') }}" class="flex flex-wrap items-end gap-4">
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
            <a href="{{ route('karyawan.rapor') }}"
                class="bg-gray-100 text-gray-500 px-5 py-3 rounded-xl text-xs font-black uppercase hover:bg-gray-200 transition">
                <i class="fas fa-times mr-1"></i> Reset
            </a>
            @endif
        </form>
    </div>
    @endif

    @if(!isset($assessments) || $assessments->isEmpty())
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-16 text-center text-gray-400">
        <i class="fas fa-inbox text-5xl mb-4 block"></i>
        <p class="font-bold text-lg mb-2">Belum Ada Penilaian</p>
        <p class="text-sm">Anda belum memiliki riwayat penilaian dari atasan.</p>
    </div>
    @else

    {{-- Info Card --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 text-white rounded-[2rem] p-6 shadow-lg">
            <p class="text-indigo-200 text-xs font-black uppercase mb-2">Total Penilaian</p>
            <p class="text-4xl font-black">{{ $stats['total_assessments'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
            <p class="text-gray-400 text-xs font-black uppercase mb-2">Rata-rata Keseluruhan</p>
            <p class="text-4xl font-black text-indigo-600">{{ $stats['average_all'] ?? 0 }}</p>
            <div class="flex gap-0.5 mt-2">
                @for($i = 1; $i <= 5; $i++)
                <span class="text-xl {{ ($stats['average_all'] ?? 0) >= $i ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                @endfor
            </div>
        </div>
        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
            <p class="text-gray-400 text-xs font-black uppercase mb-2">Nilai Terbaru</p>
            <p class="text-4xl font-black text-emerald-600">{{ $stats['latest_score'] ?? 0 }}</p>
            <p class="text-gray-400 text-xs mt-2">{{ $assessments->first()->period ?? '-' }}</p>
        </div>
    </div>

    {{-- Grafik --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        @if(isset($avgPerCategory) && count($avgPerCategory) > 0)
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
            <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight mb-4">
                <i class="fas fa-spider text-indigo-400 mr-2"></i> Grafik Kompetensi
            </h2>
            <canvas id="radarChart" style="max-height: 300px;"></canvas>
        </div>
        @endif
        @if(isset($monthlyData) && count($monthlyData) > 0)
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
            <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight mb-4">
                <i class="fas fa-chart-line text-indigo-400 mr-2"></i> Perkembangan Nilai
            </h2>
            <canvas id="progressChart" style="max-height: 300px;"></canvas>
        </div>
        @endif
    </div>

    {{-- Rata-rata per Kategori --}}
    @if(isset($avgPerCategory) && count($avgPerCategory) > 0)
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 mb-6">
        <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight mb-6">
            <i class="fas fa-chart-bar text-indigo-400 mr-2"></i> Rata-rata per Kategori
        </h2>
        <div class="space-y-4">
            @foreach($avgPerCategory as $item)
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="font-bold text-sm text-gray-700 uppercase">{{ $item['category'] }}</span>
                    <span class="font-black text-sm {{ $item['average'] >= 4 ? 'text-green-600' : ($item['average'] >= 3 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ number_format($item['average'], 2) }} / 5
                    </span>
                </div>
                @php $width = ($item['average'] / 5) * 100; @endphp
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="h-3 rounded-full {{ $item['average'] >= 4 ? 'bg-green-500' : ($item['average'] >= 3 ? 'bg-yellow-500' : 'bg-red-500') }}"
                        style="width: {{ $width }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Riwayat Penilaian --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight">
                <i class="fas fa-history text-indigo-400 mr-2"></i> Riwayat Penilaian
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b text-[10px] font-black text-gray-400 uppercase">
                        <th class="p-5">Periode</th>
                        <th class="p-5">Penilai</th>
                        <th class="p-5 text-center">Rata-rata</th>
                        <th class="p-5 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($assessments as $assessment)
                    @php $avg = $assessment->details->avg('score') ?? 0; @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-5">
                            <span class="font-bold text-sm">{{ $assessment->period }}</span>
                            <div class="text-[10px] text-gray-400">
                                {{ \Carbon\Carbon::parse($assessment->assessment_date)->format('d M Y') }}
                            </div>
                        </td>
                        <td class="p-5">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center font-black text-sm">
                                    {{ strtoupper(substr($assessment->evaluator->nama ?? '?', 0, 1)) }}
                                </div>
                                <span class="font-medium text-sm">{{ $assessment->evaluator->nama ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="p-5 text-center">
                            <span class="font-black text-lg {{ $avg >= 4 ? 'text-green-600' : ($avg >= 3 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ number_format($avg, 1) }}
                            </span>
                            <span class="text-xs text-gray-400">/5</span>
                        </td>
                        <td class="p-5 text-center">
                            <button onclick="lihatDetail({{ $assessment->id }})"
                                class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-indigo-100 transition">
                                <i class="fas fa-eye mr-1"></i> Lihat
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @endif
</div>

{{-- DATA PENILAIAN UNTUK MODAL (hidden, diparse JS) --}}
<script id="assessmentJson" type="application/json">
@php
$assessmentData = $assessments->map(function($a) {
    $avg = $a->details->avg('score') ?? 0;

    // Group detail per kategori
    $categories = [];
    foreach ($a->details as $detail) {
        $catName = $detail->question->category->name ?? 'Lainnya';
        if (!isset($categories[$catName])) {
            $categories[$catName] = ['name' => $catName, 'questions' => []];
        }
        $categories[$catName]['questions'][] = [
            'question' => $detail->question->question ?? '-',
            'score'    => $detail->score,
        ];
    }

    return [
        'id'        => $a->id,
        'period'    => $a->period,
        'date'      => \Carbon\Carbon::parse($a->assessment_date)->format('d M Y'),
        'evaluator' => $a->evaluator->nama ?? '-',
        'avg'       => round($avg, 1),
        'notes'     => ($a->general_notes && $a->general_notes != '-') ? $a->general_notes : '',
        'categories'=> array_values($categories),
    ];
});
echo json_encode($assessmentData);
@endphp
</script>

{{-- MODAL DETAIL --}}
<div id="modalDetail" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="tutupModal()"></div>
    <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-lg z-10 overflow-hidden">

        {{-- Header Modal --}}
        <div class="bg-indigo-600 p-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-indigo-200 text-[10px] font-black uppercase tracking-widest mb-1">Detail Penilaian</p>
                    <h3 id="modalPeriod" class="text-xl font-black uppercase"></h3>
                    <p id="modalMeta" class="text-indigo-200 text-xs mt-1"></p>
                </div>
                <button onclick="tutupModal()" class="text-indigo-200 hover:text-white transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <span class="text-3xl font-black" id="modalAvg"></span>
                <div>
                    <div id="modalStars" class="flex gap-0.5 text-xl"></div>
                    <p class="text-indigo-200 text-[10px]">rata-rata dari 5</p>
                </div>
            </div>
        </div>

        {{-- Body Modal --}}
        <div class="p-6 max-h-[60vh] overflow-y-auto space-y-3">

            {{-- Accordion Kategori --}}
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nilai per Kategori</p>
            <div id="modalKategori" class="space-y-2"></div>

            {{-- Feedback --}}
            <div id="modalFeedbackWrap" class="hidden pt-2">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Feedback Penilai</p>
                <div class="bg-indigo-50 rounded-xl p-4 border-l-4 border-indigo-400">
                    <p id="modalFeedback" class="text-gray-700 italic text-sm"></p>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Parse data dari JSON tag
const allAssessments = JSON.parse(document.getElementById('assessmentJson').textContent);

document.addEventListener('DOMContentLoaded', function () {

    // Radar Chart
    const radarEl = document.getElementById('radarChart');
    if (radarEl) {
        const radarData = @json($avgPerCategory);
        new Chart(radarEl, {
            type: 'radar',
            data: {
                labels: radarData.map(d => d.category),
                datasets: [{
                    label: 'Nilai',
                    data: radarData.map(d => d.average),
                    backgroundColor: 'rgba(79, 70, 229, 0.15)',
                    borderColor: '#4f46e5',
                    borderWidth: 2,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: 5,
                }]
            },
            options: {
                scales: { r: { min: 0, max: 5, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });
    }

    // Progress Chart
    const progressEl = document.getElementById('progressChart');
    if (progressEl) {
        const monthlyData = @json($monthlyData);
        const labels = Object.keys(monthlyData);
        const values = Object.values(monthlyData);
        new Chart(progressEl, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Rata-rata Nilai',
                    data: values,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: 5,
                }]
            },
            options: {
                scales: { y: { min: 0, max: 5, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });
    }
});

function lihatDetail(id) {
    const data = allAssessments.find(a => a.id === id);
    if (!data) return;

    // Header
    document.getElementById('modalPeriod').textContent = data.period;
    document.getElementById('modalMeta').textContent = data.date + ' · Dinilai oleh ' + data.evaluator;
    document.getElementById('modalAvg').textContent = data.avg + ' / 5';

    // Bintang rata-rata
    const starsEl = document.getElementById('modalStars');
    starsEl.innerHTML = '';
    for (let i = 1; i <= 5; i++) {
        const s = document.createElement('span');
        s.textContent = '★';
        s.className = data.avg >= i ? 'text-yellow-300' : 'text-indigo-300';
        starsEl.appendChild(s);
    }

    // Accordion kategori
    const kategoriEl = document.getElementById('modalKategori');
    kategoriEl.innerHTML = '';
    data.categories.forEach((cat, idx) => {
        const avgCat = cat.questions.reduce((s, q) => s + q.score, 0) / cat.questions.length;
        const color = avgCat >= 4 ? 'text-green-600' : (avgCat >= 3 ? 'text-yellow-600' : 'text-red-600');
        const barColor = avgCat >= 4 ? 'bg-green-500' : (avgCat >= 3 ? 'bg-yellow-500' : 'bg-red-500');
        const pct = (avgCat / 5) * 100;

        // Bintang rata-rata kategori
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += `<span class="${avgCat >= i ? 'text-yellow-400' : 'text-gray-300'}">★</span>`;
        }

        // List pernyataan
        let questionsHtml = '';
        cat.questions.forEach(q => {
            let qStars = '';
            for (let i = 1; i <= 5; i++) {
                qStars += `<span class="${q.score >= i ? 'text-yellow-400' : 'text-gray-300'} text-base">★</span>`;
            }
            questionsHtml += `
                <div class="flex items-start justify-between gap-3 py-2 border-b border-gray-100 last:border-0">
                    <p class="text-xs text-gray-600 flex-1">${q.question}</p>
                    <div class="flex gap-0.5 flex-shrink-0">${qStars}</div>
                </div>`;
        });

        const accordionId = 'acc-' + idx;
        kategoriEl.innerHTML += `
            <div class="border border-gray-100 rounded-2xl overflow-hidden">
                {{-- Tombol Kategori --}}
                <button onclick="toggleAccordion('${accordionId}')"
                    class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition text-left">
                    <div class="flex-1">
                        <p class="font-black text-sm uppercase text-gray-700">${cat.name}</p>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1.5">
                            <div class="h-1.5 rounded-full ${barColor}" style="width:${pct}%"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-4 flex-shrink-0">
                        <span class="font-black text-sm ${color}">${avgCat.toFixed(1)}/5</span>
                        <div class="flex gap-0.5 text-sm">${starsHtml}</div>
                        <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" id="icon-${accordionId}"></i>
                    </div>
                </button>
                {{-- Isi Accordion --}}
                <div id="${accordionId}" class="hidden px-4 pb-3 bg-gray-50">
                    ${questionsHtml}
                </div>
            </div>`;
    });

    // Feedback
    const feedbackWrap = document.getElementById('modalFeedbackWrap');
    if (data.notes && data.notes.trim() !== '') {
        document.getElementById('modalFeedback').textContent = data.notes;
        feedbackWrap.classList.remove('hidden');
    } else {
        feedbackWrap.classList.add('hidden');
    }

    document.getElementById('modalDetail').classList.remove('hidden');
}

function toggleAccordion(id) {
    const el = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    el.classList.toggle('hidden');
    icon.style.transform = el.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
}

function tutupModal() {
    document.getElementById('modalDetail').classList.add('hidden');
}
</script>
@endsection