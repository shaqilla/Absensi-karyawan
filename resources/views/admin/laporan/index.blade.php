@extends('layouts.admin')

@section('content')
<style>
    /* ============================================================
       1. CSS KHUSUS PRINT (UNTUK PDF)
       ============================================================ */
    @media print {

        /* Hapus Header/Footer browser (Tanggal, Jam, URL di pojok) */
        @page {
            margin: 0;
            size: auto;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            height: auto !important;
            background: white !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* HANCURKAN FLEXBOX PARENT: Paksa nempel ke baris paling atas */
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

        /* HAPUS SEMUA GANGGUAN: Sidebar, Halo Admin, Tombol, Filter, Chart, DAN KOTAK STATISTIK */
        aside,
        header,
        nav,
        .filter-section,
        .btn-print,
        .mobile-helper,
        .no-print,
        .chart-box-container,
        /* Hapus Chart */
        .stats-grid-pdf {
            /* HAPUS KOTAK-KOTAK STATISTIK DI PDF */
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* PEMBUNGKUS AREA LAPORAN: Paksa ke koordinat 0 (paling atas) */
        .pdf-content-wrapper {
            display: block !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            padding: 1.5cm !important;
            /* Jarak aman agar tidak terpotong printer */
        }

        /* KOP LAPORAN (Hanya muncul di PDF) */
        .pdf-header-formal {
            display: block !important;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
        }

        .pdf-header-formal h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            color: black;
        }

        /* TABEL TANPA BORDER UJUNG LUAR (HANYA GARIS DALAM) */
        .table-container {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            border: none !important;
            /* Hapus border kotak luar */
        }

        th,
        td {
            border: 1px solid #ddd !important;
            /* Garis tipis pembatas dalam saja */
            padding: 8px !important;
            color: black !important;
            font-size: 11px !important;
        }

        /* Menghilangkan garis border yang nempel di paling pinggir kertas */
        th:first-child,
        td:first-child {
            border-left: none !important;
        }

        th:last-child,
        td:last-child {
            border-right: none !important;
        }

        thead tr:first-child th {
            border-top: none !important;
        }

        th {
            background-color: #f3f4f6 !important;
            font-weight: bold;
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

<!-- WRAPPER UTAMA -->
<div class="pdf-content-wrapper w-full pb-10 text-black">

    <!-- HEADER INI CUMA MUNCUL DI PDF (Nempel Paling Atas) -->
    <div class="pdf-header-formal">
        <h1>Laporan Kehadiran Karyawan</h1>
        <p style="font-weight: bold; color: #4f46e5; margin: 0;">ZENCLOCK INTELLIGENT SYSTEM</p>
        <p style="font-size: 11px;">Periode: {{ date('d/m/Y', strtotime($start_date)) }} s/d {{ date('d/m/Y', strtotime($end_date)) }}</p>
    </div>

    <!-- HEADER DI LAYAR MONITOR (Dihilangkan pas print) -->
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-8 gap-4 no-print text-black font-sans">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter">Rekap Laporan Kehadiran</h1>
            <p class="text-gray-500 text-sm italic font-medium">Monitoring performa tim secara visual.</p>
        </div>
        <button type="button" onclick="window.print()" class="btn-print bg-slate-900 text-white px-8 py-4 rounded-2xl font-black hover:bg-black transition flex items-center justify-center shadow-xl text-xs uppercase tracking-widest">
            <i class="fas fa-file-pdf mr-2 text-indigo-400"></i> Export ke PDF
        </button>
    </div>

    <!-- SECTION 1: CHART & STATS (Punya class masing-masing agar bisa diatur di CSS print) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">

        <!-- KOTAK CHART: Akan HILANG di PDF lewat CSS -->
        <div class="lg:col-span-1 bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col items-center chart-box-container">
            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 text-center no-print">Komposisi Status</h3>
            <div class="relative w-full h-48 md:h-64">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <!-- KOTAK STATISTIK: Akan HILANG di PDF lewat CSS sesuai request kamu -->
        <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-3 gap-4 stats-grid-pdf">
            @php
            $labels = ['Hadir', 'Telat', 'Izin', 'Sakit', 'Cuti', 'Alpha'];
            $colors = ['text-emerald-600', 'text-amber-500', 'text-blue-500', 'text-purple-500', 'text-indigo-500', 'text-rose-600'];
            $bgColors = ['bg-emerald-50', 'bg-amber-50', 'bg-blue-50', 'bg-purple-50', 'bg-indigo-50', 'bg-rose-50'];
            @endphp
            @foreach($chartData['datasets'] as $key => $val)
            <div class="{{ $bgColors[$key] }} p-6 rounded-[2rem] border border-white shadow-sm flex flex-col justify-center items-center">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $labels[$key] }}</p>
                <h4 class="text-2xl md:text-3xl font-black {{ $colors[$key] }}">{{ $val }}</h4>
                <p class="text-[8px] font-bold text-gray-400 uppercase no-print tracking-widest">Pegawai</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- FILTER SECTION (ILANG DI PDF) -->
    <div class="filter-section bg-white p-8 rounded-[2.5rem] shadow-sm mb-8 border border-gray-100 no-print text-black font-sans">
        <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex flex-col lg:flex-row gap-6 lg:items-end">
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $start_date }}" class="w-full border-gray-200 rounded-xl p-4 text-sm font-bold text-gray-700 bg-white shadow-inner">
            </div>
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $end_date }}" class="w-full border-gray-200 rounded-xl p-4 text-sm font-bold text-gray-700 bg-white shadow-inner">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg shadow-indigo-100">Filter</button>
        </form>
    </div>

    <!-- TABEL UTAMA (INI YANG MASUK PDF) -->
    <!-- Bagian Tabel di index.blade.php -->
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
                    @forelse($laporans as $l) {{-- <--- PASTIKAN INI $laporans --}}
                    <tr class="text-black">
                        <td class="p-6">
                            <p class="font-black text-gray-800 text-[11px] uppercase leading-none mb-1">{{ $l->nama }}</p>
                            <p class="text-[8px] text-indigo-500 font-bold uppercase tracking-tighter">{{ $l->departemen }}</p>
                        </td>
                        <td class="p-6 text-center font-bold text-gray-500 text-[10px]">{{ date('d/m/Y', strtotime($l->tanggal)) }}</td>
                        <td class="p-6 text-center font-mono font-black text-indigo-600 text-[10px]">{{ $l->jam_masuk }}</td>
                        <td class="p-6 text-center font-mono font-black text-rose-600 text-[10px]">{{ $l->jam_keluar }}</td>
                        <td class="p-6 text-center">
                            <span style="border: 1px solid #ddd; padding: 2px 6px; border-radius: 4px; font-size: 8px; color:black !important; font-weight: bold; text-transform: uppercase;">
                                {{ $l->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-20 text-center uppercase font-black text-xs text-black italic">Data Tidak Ditemukan</td>
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
                animation: false,
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