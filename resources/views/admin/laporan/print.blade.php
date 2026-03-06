<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Export PDF - Laporan Kehadiran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            margin: 0;
            size: auto;
        }

        body {
            margin: 0;
            padding: 1cm;
            background: white !important;
            font-family: sans-serif;
            -webkit-print-color-adjust: exact;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 11px;
        }

        th {
            background-color: #f3f4f6;
        }
    </style>
</head>

<body onload="window.print()"> <!-- Otomatis buka jendela print -->

    <!-- KOP LAPORAN -->
    <div class="header">
        <h1 class="text-2xl font-black uppercase">Rekap Kehadiran Karyawan</h1>
        <h2 class="text-sm font-bold text-indigo-600 uppercase">Zenclock Intelligent System</h2>
        <p class="text-[10px] italic">Periode: {{ date('d/m/Y', strtotime($start_date)) }} s/d {{ date('d/m/Y', strtotime($end_date)) }}</p>
    </div>

    <!-- STATISTIK (TANPA CHART) -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        @php
        $labels = ['Hadir', 'Telat', 'Izin', 'Sakit', 'Cuti', 'Alpha'];
        $colors = ['text-emerald-600', 'text-amber-500', 'text-blue-500', 'text-purple-500', 'text-indigo-500', 'text-rose-600'];
        $bgColors = ['bg-emerald-50', 'bg-amber-50', 'bg-blue-50', 'bg-purple-50', 'bg-indigo-50', 'bg-rose-50'];
        @endphp
        @foreach($chartData['datasets'] as $key => $val)
        <div class="{{ $bgColors[$key] }} p-4 rounded-xl border border-gray-200 text-center">
            <p class="text-[8px] font-black uppercase text-gray-400">{{ $labels[$key] }}</p>
            <h4 class="text-xl font-black {{ $colors[$key] }}">{{ $val }}</h4>
        </div>
        @endforeach
    </div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                <th>Karyawan</th>
                <th style="text-align: center">Tanggal</th>
                <th style="text-align: center">Masuk</th>
                <th style="text-align: center">Pulang</th>
                <th style="text-align: center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporans as $l)
            <tr>
                <td>
                    <div class="font-bold uppercase">{{ $l->nama }}</div>
                    <div class="text-[9px] text-gray-500 uppercase">{{ $l->departemen }}</div>
                </td>
                <td align="center">{{ date('d/m/Y', strtotime($l->tanggal)) }}</td>
                <td align="center">{{ $l->jam_masuk }}</td>
                <td align="center">{{ $l->jam_keluar }}</td>
                <td align="center">
                    <span style="text-transform: uppercase; font-weight: bold;">{{ $l->status }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" align="center">Data Tidak Ditemukan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>