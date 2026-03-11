@extends('layouts.karyawan')

@section('content')
<div class="w-full pb-10">
    <div class="mb-10 text-center md:text-left">
        <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">Analisis Performa Saya</h1>
        <p class="text-gray-500 text-sm">Visualisasi hasil evaluasi sikap dan kinerja Anda.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- 1. GRAFIK RADAR -->
        <div class="bg-white p-10 rounded-[3rem] shadow-sm border border-slate-100 flex flex-col items-center">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">Radar Chart Performa</h3>
            <div class="relative w-full h-80">
                <canvas id="radarChart"></canvas>
            </div>
        </div>

        <!-- 2. CATATAN & FEEDBACK -->
        <div class="bg-white p-10 rounded-[3rem] shadow-sm border border-slate-100">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Feedback Atasan</h3>

            <div class="bg-indigo-50 p-8 rounded-[2rem] border border-indigo-100 relative italic text-indigo-900 leading-relaxed">
                <i class="fas fa-quote-left absolute top-4 left-4 text-indigo-200 text-2xl"></i>
                "{{ $notes }}"
            </div>

            <div class="mt-8 space-y-4">
                <p class="text-[10px] font-black text-gray-400 uppercase">Informasi:</p>
                <div class="flex items-center text-xs text-gray-600">
                    <i class="fas fa-info-circle mr-2 text-indigo-500"></i>
                    Grafik di atas menunjukkan kekuatan karakter Anda berdasarkan 5 indikator utama.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('radarChart').getContext('2d');

        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: {
                    !!json_encode($labels) !!
                },
                datasets: [{
                    label: 'Skor Saya',
                    data: {
                        !!json_encode($scores) !!   
                    },
                    fill: true,
                    backgroundColor: 'rgba(79, 70, 229, 0.2)',
                    borderColor: 'rgb(79, 70, 229)',
                    pointBackgroundColor: 'rgb(79, 70, 229)',
                    pointBorderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        suggestedMin: 0,
                        suggestedMax: 5,
                        ticks: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endsection