<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $view_type == 'daily' ? 'Laporan Harian' : 'Rekapitulasi Tahunan' }} - Zenclock</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 2cm 2cm 2cm 2cm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ===== KOP SURAT ===== */
        .kop {
            display: flex;
            align-items: center;
            border-bottom: 4px double #000;
            padding-bottom: 10px;
            margin-bottom: 16px;
            gap: 16px;
        }

        .kop-logo {
            width: 60px;
            height: 60px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -1px;
            color: #1e1b4b;
        }

        .kop-text {
            flex: 1;
            text-align: center;
        }

        .kop-text .instansi {
            font-size: 9pt;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #444;
            margin-bottom: 2px;
        }

        .kop-text h1 {
            font-size: 16pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 1.1;
        }

        .kop-text .sub {
            font-size: 9pt;
            color: #555;
            margin-top: 3px;
        }

        /* ===== INFO LAPORAN ===== */
        .info-box {
            border: 1px solid #ccc;
            border-radius: 2px;
            padding: 8px 14px;
            margin-bottom: 16px;
            font-size: 10pt;
            display: flex;
            justify-content: space-between;
            background: #f9f9f9;
        }

        .info-box .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-box .info-label {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #666;
            margin-bottom: 2px;
        }

        .info-box .info-val {
            font-weight: bold;
            font-size: 10pt;
        }

        /* ===== TABEL ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        thead tr {
            background-color: #1e1b4b;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        thead th {
            padding: 8px 10px;
            color: #fff;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            text-align: left;
            border: none;
        }

        thead th.c {
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background-color: #f5f5f5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
            font-size: 10pt;
        }

        tbody td.c {
            text-align: center;
        }

        tbody tr:last-child td {
            border-bottom: 2px solid #000;
        }

        .nama {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10pt;
        }

        .dept {
            font-size: 8pt;
            color: #666;
            margin-top: 1px;
        }

        .waktu {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            font-size: 10pt;
        }

        .waktu.masuk {
            color: #1a237e;
        }

        .waktu.keluar {
            color: #b71c1c;
        }

        /* Badge status */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 2px;
            border: 1px solid;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .b-hadir {
            background: #e8f5e9;
            color: #1b5e20;
            border-color: #a5d6a7;
        }

        .b-telat {
            background: #fff8e1;
            color: #e65100;
            border-color: #ffcc80;
        }

        .b-izin {
            background: #e3f2fd;
            color: #0d47a1;
            border-color: #90caf9;
        }

        .b-sakit {
            background: #f3e5f5;
            color: #4a148c;
            border-color: #ce93d8;
        }

        .b-cuti {
            background: #fce4ec;
            color: #880e4f;
            border-color: #f48fb1;
        }

        .b-alpha {
            background: #ffebee;
            color: #b71c1c;
            border-color: #ef9a9a;
        }

        /* Angka rekap tahunan */
        .n-hadir {
            color: #1b5e20;
            font-weight: bold;
        }

        .n-telat {
            color: #e65100;
            font-weight: bold;
        }

        .n-izin {
            color: #0d47a1;
            font-weight: bold;
        }

        .n-sakit {
            color: #4a148c;
            font-weight: bold;
        }

        .n-cuti {
            color: #880e4f;
            font-weight: bold;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 24px;
            display: flex;
            justify-content: space-between;
            font-size: 9.5pt;
        }

        .footer .kiri {
            color: #555;
            font-size: 9pt;
            line-height: 1.6;
        }

        .footer .ttd {
            text-align: center;
            font-size: 9.5pt;
        }

        .footer .ttd .garis {
            margin-top: 52px;
            border-top: 1px solid #000;
            padding-top: 4px;
            font-weight: bold;
            min-width: 180px;
        }

        /* Sembunyikan semua saat print kecuali body */
        @media screen {
            body {
                padding: 40px;
                max-width: 900px;
                margin: 0 auto;
                background: #e5e7eb;
            }

            .page {
                background: white;
                padding: 2cm;
                box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
                min-height: 297mm;
            }
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .page {
                box-shadow: none;
                padding: 0;
            }
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    <div class="page">

        {{-- KOP --}}
        <div class="kop">
            <div class="kop-logo">ZC</div>
            <div class="kop-text">
                <div class="instansi">Zenclock Intelligent System</div>
                <h1>{{ $view_type == 'daily' ? 'Laporan Kehadiran Karyawan' : 'Rekapitulasi Kehadiran Tahunan' }}</h1>
                <div class="sub">Dokumen resmi — dicetak otomatis oleh sistem</div>
            </div>
            <div class="kop-logo" style="visibility:hidden;"></div>{{-- spacer biar tengah --}}
        </div>

        {{-- INFO --}}
        <div class="info-box">
            @if($view_type == 'daily')
            <div class="info-item">
                <span class="info-label">Periode</span>
                <span class="info-val">{{ date('d F Y', strtotime($start_date)) }} — {{ date('d F Y', strtotime($end_date)) }}</span>
            </div>
            @else
            <div class="info-item">
                <span class="info-label">Tahun Rekapitulasi</span>
                <span class="info-val">{{ $selected_year }}</span>
            </div>
            @endif
            <div class="info-item" style="text-align:right">
                <span class="info-label">Tanggal Cetak</span>
                <span class="info-val">{{ date('d F Y, H:i') }} WIB</span>
            </div>
            <div class="info-item" style="text-align:right">
                <span class="info-label">Total Data</span>
                <span class="info-val">{{ count($laporans) }} baris</span>
            </div>
        </div>

        {{-- TABEL --}}
        <table>
            @if($view_type == 'daily')
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:28%">Karyawan</th>
                    <th class="c" style="width:14%">Tanggal</th>
                    <th class="c" style="width:13%">Jam Masuk</th>
                    <th class="c" style="width:13%">Jam Pulang</th>
                    <th class="c" style="width:27%">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporans as $i => $l)
                @php
                $s = strtolower($l->status);
                $bc = match($s) {
                'hadir' => 'b-hadir', 'telat' => 'b-telat',
                'izin' => 'b-izin', 'sakit' => 'b-sakit',
                'cuti' => 'b-cuti', 'alpha' => 'b-alpha',
                default => ''
                };
                @endphp
                <tr>
                    <td class="c" style="color:#999;font-size:8.5pt;">{{ $i + 1 }}</td>
                    <td>
                        <div class="nama">{{ $l->nama }}</div>
                        <div class="dept">{{ $l->departemen }}</div>
                    </td>
                    <td class="c">{{ date('d/m/Y', strtotime($l->tanggal)) }}</td>
                    <td class="c"><span class="waktu masuk">{{ $l->jam_masuk ?? '--:--' }}</span></td>
                    <td class="c"><span class="waktu keluar">{{ $l->jam_keluar ?? '--:--' }}</span></td>
                    <td class="c"><span class="badge {{ $bc }}">{{ $l->status }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:20px;color:#999;font-style:italic;">
                        Tidak ada data untuk periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>

            @else
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:35%">Karyawan</th>
                    <th class="c">Hadir</th>
                    <th class="c">Telat</th>
                    <th class="c">Izin</th>
                    <th class="c">Sakit</th>
                    <th class="c">Cuti</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporans as $i => $l)
                <tr>
                    <td class="c" style="color:#999;font-size:8.5pt;">{{ $i + 1 }}</td>
                    <td>
                        <div class="nama">{{ $l->nama }}</div>
                        <div class="dept">{{ $l->departemen }}</div>
                    </td>
                    <td class="c"><span class="n-hadir">{{ $l->hadir }}</span></td>
                    <td class="c"><span class="n-telat">{{ $l->telat }}</span></td>
                    <td class="c"><span class="n-izin">{{ $l->izin }}</span></td>
                    <td class="c"><span class="n-sakit">{{ $l->sakit }}</span></td>
                    <td class="c"><span class="n-cuti">{{ $l->cuti }}</span></td>
                </tr>
                @endforeach
            </tbody>
            @endif
        </table>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="kiri">
                Dokumen ini digenerate secara otomatis oleh Zenclock Intelligent System.<br>
                Tidak memerlukan tanda tangan basah jika dicetak dari sistem resmi.
            </div>
            <div class="ttd">
                <div>Mengetahui,</div>
                <div>Kepala / HRD</div>
                <div class="garis">( ________________________ )</div>
            </div>
        </div>

    </div>

    <script>
        // Auto print begitu halaman siap, lalu kembali ke halaman sebelumnya
        window.addEventListener('load', function() {
            window.print();
            window.addEventListener('afterprint', function() {
                history.back();
            });
        });
    </script>

</body>

</html>