@extends('layouts.admin')

@section('content')
<style>
    /* 1. CSS KHUSUS UNTUK MEMPERBAIKI HASIL PDF */
    @media print {

        /* Hapus Header/Footer Browser (Tanggal, Jam, URL di tanda merah) */
        @page {
            margin: 0;
            size: auto;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            height: auto !important;
            display: block !important;
            /* Matikan sistem flex body */
            background: white !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* HANCURKAN SISTEM FLEXBOX PARENT (Ini kunci agar nempel ke atas) */
        main,
        .flex,
        .min-h-screen,
        .items-center,
        .justify-center,
        #sidebar,
        header {
            display: block !important;
            position: static !important;
            width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* HAPUS SEMUA SAMPAH (Sidebar, Halo Admin, Filter, dan CHART) */
        aside,
        header,
        nav,
        .filter-section,
        .btn-print,
        .mobile-helper,
        .no-print,
        .chart-box-container {
            /* KITA HAPUS CHARTNYA DI SINI */
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
        }

        /* AREA LAPORAN (KOTAK HIJAU) DIPAKSA KE ATAS */
        .pdf-content-wrapper {
            display: block !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            padding: 1.5cm !important;
            /* Margin aman agar tidak terpotong printer */
        }

        /* KOP LAPORAN FORMAL */
        .pdf-header-formal {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }

        .pdf-header-formal h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
        }

        /* ATUR STATISTIK AGAR RAPI DI PDF (TANPA CHART) */
        .stats-grid-pdf {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 10px !important;
            margin-bottom: 20px !important;
        }

        .stat-card-pdf {
            border: 1px solid #ddd !important;
            padding: 10px !important;
            border-radius: 12px !important;
            text-align: center !important;
            background-color: #f9fafb !important;
        }

        /* TABEL LAPORAN */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 11px !important;
        }

        th,
        td {
            border: 1px solid #ccc !important;
            padding: 8px !important;
            color: black !important;
        }

        th {
            background-color: #f3f4f6 !important;
        }

        tr {
            page-break-inside: avoid;
        }
    }

    /* SEMBUNYIKAN KOP PDF SAAT DI LAYAR LAPTOP */
    .pdf-header-formal {
        display: none;
    }
</style>

<!-- WRAPPER UNTUK PDF -->
<div class="pdf-content-wrapper w-full pb-10 text-black">

    <!-- HEADER INI CUMA MUNCUL DI PDF (Nempel Paling Atas) -->
    <div class="pdf-header-formal">
        <h1>Laporan Kehadiran Karyawan</h1>
        <p style="font-weight: bold; color: #4f46e5; margin: 0;">ZENCLOCK INTELLIGENT SYSTEM</p>
        <p style="font-size: 11px;">Periode: {{ date('d/m/Y', strtotime($start_date)) }} s/d {{ date('d/m/Y', strtotime($end_date)) }}</p>
    </div>

    <!-- HEADER DI LAYAR MONITOR (Dihilangkan pas print) -->
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-8 gap-4 no-print text-black">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter">Rekap Laporan Kehadiran</h1>
            <p class="text-gray-500 text-sm">Monitoring performa tim secara visual.</p>
        </div>
        <button type="button" onclick="window.print()" class="btn-print bg-slate-900 text-white px-8 py-4 rounded-2xl font-black hover:bg-black transition flex items-center justify-center shadow-xl text-xs uppercase tracking-widest">
            <i class="fas fa-file-pdf mr-2 text-indigo-400"></i> Export ke PDF
        </button>
    </div>

    <!-- SECTION 1: CHART (ILANG DI PDF) & STATS (ADA DI PDF) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">

        <!-- KOTAK CHART: Pake class 'chart-box-container' biar ilang pas print -->
        <div class="lg:col-span-1 bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col items-center chart-box-container">
            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 text-center">Komposisi Status</h3>
            <div class="relative w-full h-48 md:h-64">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <!-- STATISTIK: Masuk ke PDF lewat class 'stats-grid-pdf' -->
        <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-3 gap-4 stats-grid-pdf">
            @php
            $labels = ['Hadir', 'Telat', 'Izin', 'Sakit', 'Cuti', 'Alpha'];
            $colors = ['text-emerald-600', 'text-amber-500', 'text-blue-500', 'text-purple-500', 'text-indigo-500', 'text-rose-600'];
            $bgColors = ['bg-emerald-50', 'bg-amber-50', 'bg-blue-50', 'bg-purple-50', 'bg-indigo-50', 'bg-rose-50'];
            @endphp
            @foreach($chartData['datasets'] as $key => $val)
            <div class="{{ $bgColors[$key] }} p-6 rounded-[2rem] border border-white shadow-sm flex flex-col justify-center items-center stat-card-pdf">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 leading-none">{{ $labels[$key] }}</p>
                <h4 class="text-3xl font-black {{ $colors[$key] }} leading-none">{{ $val }}</h4>
                <p class="text-[8px] font-bold text-gray-400 uppercase no-print mt-1 tracking-widest">Pegawai</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- FILTER SECTION (ILANG DI PDF) -->
    <div class="filter-section bg-white p-8 rounded-[2.5rem] shadow-sm mb-8 border border-gray-100 no-print text-black">
        <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex flex-col lg:flex-row gap-6 lg:items-end">
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $start_date }}" class="w-full border-gray-200 rounded-xl p-4 text-sm font-bold text-gray-700 bg-white">
            </div>
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $end_date }}" class="w-full border-gray-200 rounded-xl p-4 text-sm font-bold text-gray-700 bg-white">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black uppercase text-xs">Filter</button>
        </form>
    </div>

    <!-- TABEL UTAMA (MASUK PDF) -->
    <div class="table-container bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 uppercase text-[10px] font-black text-gray-400 tracking-widest">
                        <th class="p-6">Karyawan</th>
                        <th class="p-6 text-center">Tanggal</th>
                        <th class="p-6 text-center">Masuk</th>
                        <th class="p-6 text-center">Pulang</th>
                        <th class="p-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($laporans as $l)
                    <tr class="text-black">
                        <td class="p-6">
                            <p class="font-black text-[11px] uppercase leading-none mb-1">{{ $l->nama }}</p>
                            <p class="text-[8px] text-indigo-500 font-bold uppercase tracking-tighter">{{ $l->departemen }}</p>
                        </td>
                        <td class="p-6 text-center font-bold text-gray-500 text-[10px]">{{ date('d/m/Y', strtotime($l->tanggal)) }}</td>
                        <td class="p-6 text-center font-mono font-black text-indigo-600 text-[10px]">{{ $l->jam_masuk }}</td>
                        <td class="p-6 text-center font-mono font-black text-rose-600 text-[10px]">{{ $l->jam_keluar }}</td>
                        <td class="p-6 text-center">
                            <span style="border: 1px solid #ddd; padding: 2px 6px; border-radius: 5px; font-size: 8px; color:black !important; font-weight:bold;" class="uppercase">
                                {{ $l->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-20 text-center uppercase font-black text-xs text-black">Data Tidak Ditemukan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const chartData = @json($chartData);
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.datasets,
                    backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#a855f7', '#6366f1', '#f43f5e'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false, // MATIKAN ANIMASI BIAR GAK BLANK
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            font: {
                                size: 9,
                                weight: 'bold'
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    });
</script>
@endsection