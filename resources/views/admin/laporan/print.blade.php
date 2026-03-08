<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran - PDF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* 1. PAKSA KERTAS NEMPEL PALING ATAS */
        @page {
            margin: 0;
            size: auto;
        }

        body {
            margin: 0 !important;
            padding: 1cm !important;
            /* Jarak standar agar teks tidak terpotong printer */
            background: white !important;
            font-family: sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* 2. HEADER LAPORAN - RAPAT KE ATAS */
        .header {
            text-align: center;
            margin-top: 0 !important;
            margin-bottom: 25px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            color: black;
        }

        .header h2 {
            margin: 0;
            font-size: 14px;
            color: #4f46e5;
            font-weight: bold;
        }

        /* 3. TABEL TANPA BORDER UJUNG LUAR (HANYA GARIS DALAM) */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            border: none !important;
            /* Hilangkan border kotak luar */
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            /* Garis pembatas tipis di dalam saja */
            padding: 10px;
            font-size: 11px;
            text-align: left;
            color: black !important;
        }

        /* INI KUNCI MENGHILANGKAN BORDER DI UJUNG TABEL */
        th:first-child,
        td:first-child {
            border-left: none !important;
        }

        /* Ujung kiri hilang */
        th:last-child,
        td:last-child {
            border-right: none !important;
        }

        /* Ujung kanan hilang */
        thead tr:first-child th {
            border-top: none !important;
        }

        /* Atas tabel hilang */

        th {
            background-color: #f8fafc !important;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b !important;
        }
    </style>
</head>

<body onload="window.print()">

    <!-- KOP LAPORAN (NEMPEL PALING ATAS) -->
    <div class="header">
        <h1>Rekap Kehadiran Karyawan</h1>
        <h2>Zenclock Intelligent System</h2>
        <p style="font-size: 10px; margin: 5px 0;">Periode: {{ date('d M Y', strtotime($start_date)) }} s/d {{ date('d M Y', strtotime($end_date)) }}</p>
    </div>

    <!-- 
        BAGIAN STATISTIK (KOTAK-KOTAK) SUDAH SAYA HAPUS TOTAL DI SINI 
        AGAR TIDAK MUNCUL DI PDF 
    -->

    <!-- TABEL DATA (MURNI TABEL SAJA) -->
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
                    <div style="font-weight: bold; text-transform: uppercase; font-size: 11px;">{{ $l->nama }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">{{ $l->departemen }}</div>
                </td>
                <td style="text-align: center;">{{ date('d/m/Y', strtotime($l->tanggal)) }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $l->jam_masuk }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $l->jam_keluar }}</td>
                <td style="text-align: center;">
                    <span style="font-weight: 800; text-transform: uppercase; font-size: 10px;">{{ $l->status }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 50px;">DATA TIDAK DITEMUKAN</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>