@extends('layouts.admin')

@section('content')
<style>
    /* =============================================
       MODAL OVERLAY
    ============================================= */
    #print-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        /* HP: bottom sheet */
        align-items: flex-end;
        justify-content: center;
        padding: 0;
    }

    #print-modal.active {
        display: flex;
    }

    /* HP: naik dari bawah seperti bottom sheet */
    #print-modal .modal-card {
        background: white;
        width: 100%;
        height: 92vh;
        border-radius: 20px 20px 0 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 -8px 40px rgba(0, 0, 0, 0.3);
        animation: slideFromBottom 0.3s ease;
    }

    @keyframes slideFromBottom {
        from {
            opacity: 0;
            transform: translateY(60px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Tablet ke atas: modal mengambang di tengah */
    @media (min-width: 640px) {
        #print-modal {
            align-items: center;
            padding: 24px;
        }

        #print-modal .modal-card {
            max-width: 720px;
            height: auto;
            max-height: 90vh;
            border-radius: 16px;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.25s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    }

    /* Toolbar */
    #print-modal .modal-toolbar {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 16px 12px;
        /* extra top buat handle bar */
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        flex-shrink: 0;
        gap: 8px;
    }

    /* Handle bar (cuma tampil di HP) */
    #print-modal .modal-toolbar::before {
        content: '';
        position: absolute;
        top: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 36px;
        height: 4px;
        background: #d1d5db;
        border-radius: 2px;
    }

    @media (min-width: 640px) {
        #print-modal .modal-toolbar::before {
            display: none;
        }

        #print-modal .modal-toolbar {
            padding: 14px 20px;
        }
    }

    #print-modal .modal-toolbar .title {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #374151;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #print-modal .modal-toolbar .actions {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-shrink: 0;
    }

    #print-modal .btn-print-now {
        background: #1e1b4b;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    @media (min-width: 640px) {
        #print-modal .btn-print-now {
            padding: 8px 18px;
            font-size: 11px;
        }
    }

    #print-modal .btn-print-now:hover {
        background: #111827;
    }

    #print-modal .btn-close {
        background: #f3f4f6;
        color: #374151;
        border: none;
        width: 32px;
        height: 32px;
        min-width: 32px;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    #print-modal .btn-close:hover {
        background: #e5e7eb;
    }

    /* Body modal — scrollable */
    #print-modal .modal-body {
        overflow-y: auto;
        flex: 1;
        padding: 12px;
        background: #e5e7eb;
        -webkit-overflow-scrolling: touch;
    }

    @media (min-width: 640px) {
        #print-modal .modal-body {
            padding: 24px;
        }
    }

    /* Kertas dokumen */
    #print-modal .paper {
        background: white;
        width: 100%;
        max-width: 640px;
        margin: 0 auto;
        padding: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        font-family: 'Times New Roman', Times, serif;
        font-size: 9pt;
        color: #000;
    }

    @media (min-width: 640px) {
        #print-modal .paper {
            padding: 2cm;
            font-size: 10.5pt;
        }
    }

    /* Tabel bisa scroll horizontal di HP */
    #print-modal .paper .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* ===== STYLE DOKUMEN — fluid responsive via clamp & container query ===== */

    /* Container query context — paper jadi container */
    #print-modal .paper,
    #print-content {
        container-type: inline-size;
        container-name: paper;
    }

    /* KOP — fluid sizing pakai clamp */
    .doc-kop {
        display: flex;
        align-items: center;
        border-bottom: 3px double #000;
        padding-bottom: clamp(6px, 2cqi, 12px);
        margin-bottom: clamp(10px, 2.5cqi, 16px);
        gap: clamp(6px, 2cqi, 14px);
    }

    .doc-kop-logo {
        width: clamp(36px, 8cqi, 54px);
        height: clamp(36px, 8cqi, 54px);
        border: 2px solid #1e1b4b;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(10px, 2.5cqi, 15px);
        font-weight: 900;
        color: #1e1b4b;
        flex-shrink: 0;
    }

    .doc-kop-text {
        flex: 1;
        text-align: center;
        min-width: 0;
    }

    .doc-kop-text .instansi {
        font-size: clamp(6pt, 1.8cqi, 8.5pt);
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #555;
    }

    .doc-kop-text h2 {
        font-size: clamp(9pt, 3.2cqi, 15pt);
        font-weight: 900;
        text-transform: uppercase;
        margin: 2px 0;
        line-height: 1.2;
    }

    .doc-kop-text .sub {
        font-size: clamp(6pt, 1.6cqi, 8pt);
        color: #777;
    }

    /* INFO BOX — wrap di layar kecil, satu baris di besar */
    .doc-info {
        display: flex;
        flex-wrap: wrap;
        gap: clamp(4px, 1.5cqi, 8px);
        border: 1px solid #ccc;
        padding: clamp(5px, 1.5cqi, 9px) clamp(8px, 2cqi, 13px);
        margin-bottom: clamp(10px, 2cqi, 14px);
        background: #f9f9f9;
    }

    @container paper (min-width: 400px) {
        .doc-info {
            flex-wrap: nowrap;
            justify-content: space-between;
        }
    }

    .doc-info-item {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .doc-info-label {
        font-size: clamp(6pt, 1.6cqi, 7.5pt);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #888;
    }

    .doc-info-val {
        font-weight: bold;
        font-size: clamp(7pt, 1.9cqi, 9pt);
    }

    /* TABEL */
    .doc-table {
        width: 100%;
        border-collapse: collapse;
    }

    .doc-table thead tr {
        background-color: #1e1b4b;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .doc-table thead th {
        padding: clamp(4px, 1.3cqi, 8px) clamp(5px, 1.5cqi, 10px);
        color: #fff;
        font-size: clamp(6pt, 1.7cqi, 7.5pt);
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: left;
    }

    .doc-table thead th.c {
        text-align: center;
    }

    .doc-table tbody tr:nth-child(even) {
        background: #f5f5f5;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .doc-table tbody td {
        padding: clamp(4px, 1.2cqi, 7px) clamp(5px, 1.5cqi, 10px);
        border-bottom: 1px solid #e0e0e0;
        vertical-align: middle;
        font-size: clamp(7pt, 1.9cqi, 9.5pt);
    }

    .doc-table tbody td.c {
        text-align: center;
    }

    .doc-table tbody tr:last-child td {
        border-bottom: 2px solid #000;
    }

    .doc-nama {
        font-weight: bold;
        text-transform: uppercase;
        font-size: clamp(7pt, 1.9cqi, 9.5pt);
    }

    .doc-dept {
        font-size: clamp(6pt, 1.5cqi, 7.5pt);
        color: #777;
        margin-top: 1px;
    }

    .doc-waktu {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        font-size: clamp(7pt, 1.8cqi, 9pt);
    }

    .doc-waktu.in {
        color: #1a237e;
    }

    .doc-waktu.out {
        color: #b71c1c;
    }

    .doc-badge {
        display: inline-block;
        padding: 1px clamp(4px, 1.2cqi, 7px);
        font-size: clamp(5.5pt, 1.5cqi, 7pt);
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

    /* FOOTER */
    .doc-footer {
        margin-top: clamp(12px, 2.5cqi, 20px);
        display: flex;
        flex-direction: column;
        gap: 10px;
        font-size: clamp(7pt, 1.8cqi, 8.5pt);
        color: #555;
    }

    @container paper (min-width: 400px) {
        .doc-footer {
            flex-direction: row;
            justify-content: space-between;
            align-items: flex-end;
        }
    }

    .doc-ttd {
        text-align: center;
    }

    .doc-ttd-garis {
        margin-top: clamp(32px, 6cqi, 48px);
        border-top: 1px solid #000;
        padding-top: 3px;
        font-weight: bold;
        color: #000;
        min-width: clamp(120px, 25cqi, 180px);
        font-size: clamp(7pt, 1.8cqi, 8.5pt);
    }

    #print-content {
        display: none;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 2cm;
        }

        body * {
            visibility: hidden;
        }

        #print-content,
        #print-content * {
            visibility: visible;
        }

        #print-content {
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: white;
        }
    }
</style>

{{-- #print-content: selalu di DOM, hanya muncul saat print --}}
<div id="print-content">
    <div class="doc-kop">
        <div class="doc-kop-logo">ZC</div>
        <div class="doc-kop-text">
            <div class="instansi">Zenclock Intelligent System</div>
            <h2>{{ $view_type == 'daily' ? 'Laporan Kehadiran Karyawan' : 'Rekapitulasi Kehadiran Tahunan' }}</h2>
            <div class="sub">Dokumen resmi — digenerate otomatis oleh sistem</div>
        </div>
        <div class="doc-kop-logo" style="visibility:hidden;"></div>
    </div>
    <div class="doc-info">
        @if($view_type == 'daily')
        <div class="doc-info-item">
            <span class="doc-info-label">Periode</span>
            <span class="doc-info-val">{{ date('d F Y', strtotime($start_date)) }} — {{ date('d F Y', strtotime($end_date)) }}</span>
        </div>
        @elseif($view_type == 'monthly')
        @php $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
        <div class="doc-info-item">
            <span class="doc-info-label">Bulan</span>
            <span class="doc-info-val">{{ $namaBulan[$selected_month] }} {{ $selected_year }}</span>
        </div>
        @else
        <div class="doc-info-item">
            <span class="doc-info-label">Tahun Rekapitulasi</span>
            <span class="doc-info-val">{{ $selected_year }}</span>
        </div>
        @endif
        <div class="doc-info-item" style="text-align:right">
            <span class="doc-info-label">Tanggal Cetak</span>
            <span class="doc-info-val">{{ date('d F Y, H:i') }} WIB</span>
        </div>
        <div class="doc-info-item" style="text-align:right">
            <span class="doc-info-label">Total Data</span>
            <span class="doc-info-val">{{ count($laporans) }} baris</span>
        </div>
    </div>
    <table class="doc-table">
        @if($view_type == 'daily')
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:28%">Karyawan</th>
                <th class="c" style="width:13%">Tanggal</th>
                <th class="c" style="width:13%">Masuk</th>
                <th class="c" style="width:13%">Pulang</th>
                <th class="c">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporans as $i => $l)
            @php $bc = match(strtolower($l->status)) { 'hadir'=>'b-hadir','telat'=>'b-telat','izin'=>'b-izin','sakit'=>'b-sakit','cuti'=>'b-cuti','alpha'=>'b-alpha',default=>'' }; @endphp
            <tr>
                <td class="c" style="color:#aaa;font-size:8pt;">{{ $i+1 }}</td>
                <td>
                    <div class="doc-nama">{{ $l->nama }}</div>
                    <div class="doc-dept">{{ $l->departemen }}</div>
                </td>
                <td class="c">{{ date('d/m/Y', strtotime($l->tanggal)) }}</td>
                <td class="c"><span class="doc-waktu in">{{ $l->jam_masuk ?? '--:--' }}</span></td>
                <td class="c"><span class="doc-waktu out">{{ $l->jam_keluar ?? '--:--' }}</span></td>
                <td class="c"><span class="doc-badge {{ $bc }}">{{ $l->status }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:16px;color:#999;font-style:italic;">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
        @else
        {{-- Bulanan & Tahunan --}}
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
                <td class="c" style="color:#aaa;font-size:8pt;">{{ $i+1 }}</td>
                <td>
                    <div class="doc-nama">{{ $l->nama }}</div>
                    <div class="doc-dept">{{ $l->departemen }}</div>
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
    <div class="doc-footer">
        <div>Dokumen ini digenerate secara otomatis oleh Zenclock Intelligent System.<br>Tidak memerlukan tanda tangan basah jika dicetak dari sistem resmi.</div>
        <div class="doc-ttd">Mengetahui, Kepala / HRD<div class="doc-ttd-garis">( ________________________ )</div>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW --}}
<div id="print-modal">
    <div class="modal-card">
        <div class="modal-toolbar">
            <span class="title"><i class="fas fa-file-pdf" style="color:#4f46e5;margin-right:6px;"></i>Preview Laporan</span>
            <div class="actions">
                <button class="btn-print-now" onclick="window.print()"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>
                <button class="btn-close" onclick="tutupModal()">✕</button>
            </div>
        </div>
        <div class="modal-body">
            <div class="paper">
                <div class="doc-kop">
                    <div class="doc-kop-logo">ZC</div>
                    <div class="doc-kop-text">
                        <div class="instansi">Zenclock Intelligent System</div>
                        <h2>{{ $view_type == 'daily' ? 'Laporan Kehadiran Karyawan' : 'Rekapitulasi Kehadiran Tahunan' }}</h2>
                        <div class="sub">Dokumen resmi — digenerate otomatis oleh sistem</div>
                    </div>
                    <div class="doc-kop-logo" style="visibility:hidden;"></div>
                </div>
                <div class="doc-info">
                    @if($view_type == 'daily')
                    <div class="doc-info-item"><span class="doc-info-label">Periode</span><span class="doc-info-val">{{ date('d F Y', strtotime($start_date)) }} — {{ date('d F Y', strtotime($end_date)) }}</span></div>
                    @else
                    <div class="doc-info-item"><span class="doc-info-label">Tahun Rekapitulasi</span><span class="doc-info-val">{{ $selected_year }}</span></div>
                    @endif
                    <div class="doc-info-item" style="text-align:right"><span class="doc-info-label">Tanggal Cetak</span><span class="doc-info-val">{{ date('d F Y, H:i') }} WIB</span></div>
                    <div class="doc-info-item" style="text-align:right"><span class="doc-info-label">Total Data</span><span class="doc-info-val">{{ count($laporans) }} baris</span></div>
                </div>
                <div class="table-scroll">
                    <table class="doc-table">
                        @if($view_type == 'daily')
                        <thead>
                            <tr>
                                <th style="width:5%">No</th>
                                <th style="width:28%">Karyawan</th>
                                <th class="c" style="width:13%">Tanggal</th>
                                <th class="c" style="width:13%">Masuk</th>
                                <th class="c" style="width:13%">Pulang</th>
                                <th class="c">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporans as $i => $l)
                            @php $bc = match(strtolower($l->status)) { 'hadir'=>'b-hadir','telat'=>'b-telat','izin'=>'b-izin','sakit'=>'b-sakit','cuti'=>'b-cuti','alpha'=>'b-alpha',default=>'' }; @endphp
                            <tr>
                                <td class="c" style="color:#aaa;font-size:8pt;">{{ $i+1 }}</td>
                                <td>
                                    <div class="doc-nama">{{ $l->nama }}</div>
                                    <div class="doc-dept">{{ $l->departemen }}</div>
                                </td>
                                <td class="c">{{ date('d/m/Y', strtotime($l->tanggal)) }}</td>
                                <td class="c"><span class="doc-waktu in">{{ $l->jam_masuk ?? '--:--' }}</span></td>
                                <td class="c"><span class="doc-waktu out">{{ $l->jam_keluar ?? '--:--' }}</span></td>
                                <td class="c"><span class="doc-badge {{ $bc }}">{{ $l->status }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:16px;color:#999;font-style:italic;">Tidak ada data.</td>
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
                                <td class="c" style="color:#aaa;font-size:8pt;">{{ $i+1 }}</td>
                                <td>
                                    <div class="doc-nama">{{ $l->nama }}</div>
                                    <div class="doc-dept">{{ $l->departemen }}</div>
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
                </div>
                <div class="doc-footer">
                    <p>Dokumen ini digenerate secara otomatis oleh Zenclock Intelligent System.<br>Tidak memerlukan tanda tangan basah jika dicetak dari sistem resmi.</p>
                    <div class="doc-ttd">
                        Mengetahui, Kepala / HRD
                        <br>
                        <div class="margin-top:48px; border-top:1px solid #00; padding-top:4px;">
                            ( ________________________ )
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TAMPILAN HALAMAN LAPORAN --}}
<div class="flex flex-col md:flex-row justify-between md:items-center mb-8 gap-4 text-black font-sans">
    <div>
        <h1 class="text-3xl font-black uppercase tracking-tighter">Laporan & Rekapitulasi</h1>
        <p class="text-gray-500 text-sm italic font-medium">Monitoring data harian dan tahunan karyawan.</p>
    </div>
    <div class="flex items-center gap-4">
        <div class="flex bg-gray-200 p-1 rounded-2xl">
            <a href="{{ route('admin.laporan.index', ['view_type' => 'daily']) }}"
                class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $view_type == 'daily' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500' }}">Harian</a>
            <a href="{{ route('admin.laporan.index', ['view_type' => 'monthly', 'year' => $selected_year, 'month' => $selected_month]) }}"
                class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $view_type == 'monthly' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500' }}">Bulanan</a>
            <a href="{{ route('admin.laporan.index', ['view_type' => 'annual']) }}"
                class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $view_type == 'annual' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500' }}">Tahunan</a>
        </div>
        <button onclick="bukaModal()" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black hover:bg-black transition flex items-center justify-center shadow-xl text-xs uppercase tracking-widest">
            <i class="fas fa-file-pdf mr-2 text-indigo-400"></i> Export ke PDF
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    <div class="lg:col-span-1 bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col items-center">
        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 text-center">Komposisi Status</h3>
        <div class="relative w-full h-48 md:h-64"><canvas id="attendanceChart"></canvas></div>
    </div>
    <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-3 gap-4">
        @php
        $labels=['Hadir','Telat','Izin','Sakit','Cuti','Alpha'];
        $colors=['text-emerald-600','text-amber-500','text-blue-500','text-purple-500','text-indigo-500','text-rose-600'];
        $bgColors=['bg-emerald-50','bg-amber-50','bg-blue-50','bg-purple-50','bg-indigo-50','bg-rose-50'];
        @endphp
        @foreach($chartData['datasets'] as $key => $val)
        <div class="{{ $bgColors[$key] }} p-6 rounded-[2rem] border border-white shadow-sm flex flex-col justify-center items-center">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $labels[$key] }}</p>
            <h4 class="text-2xl md:text-3xl font-black {{ $colors[$key] }}">{{ $val }}</h4>
            <p class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Pegawai</p>
        </div>
        @endforeach
    </div>
</div>

<div class="bg-white p-8 rounded-[2.5rem] shadow-sm mb-8 border border-gray-100 text-black">
    <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex flex-col lg:flex-row gap-6 lg:items-end">
        <input type="hidden" name="view_type" value="{{ $view_type }}">
        @if($view_type == 'daily')
        <div class="flex-1">
            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Mulai Tanggal</label>
            <input type="date" name="start_date" value="{{ $start_date }}" class="w-full border border-gray-200 rounded-xl p-4 text-sm font-bold text-gray-700 bg-white shadow-inner">
        </div>
        <div class="flex-1">
            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $end_date }}" class="w-full border border-gray-200 rounded-xl p-4 text-sm font-bold text-gray-700 bg-white shadow-inner">
        </div>
        @elseif($view_type == 'monthly')
        <div class="flex-1">
            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Pilih Bulan</label>
            <select name="month" class="w-full border border-gray-200 rounded-xl p-4 text-sm font-bold text-gray-700 bg-white">
                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bln)
                <option value="{{ $i + 1 }}" {{ $selected_month == ($i + 1) ? 'selected' : '' }}>{{ $bln }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Pilih Tahun</label>
            <select name="year" class="w-full border border-gray-200 rounded-xl p-4 text-sm font-bold text-gray-700 bg-white">
                @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                <option value="{{ $y }}" {{ $selected_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        @else
        <div class="flex-1">
            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Pilih Tahun Rekapitulasi</label>
            <select name="year" class="w-full border border-gray-200 rounded-xl p-4 text-sm font-bold text-gray-700 bg-white">
                @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                <option value="{{ $y }}" {{ $selected_year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                @endfor
            </select>
        </div>
        @endif
        <button type="submit" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg">Tampilkan</button>
    </form>
</div>

<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden font-sans">
    <div class="overflow-x-auto w-full">
        <table class="w-full text-left border-collapse">
            @if($view_type == 'daily')
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 uppercase text-[10px] font-black text-gray-400 tracking-widest">
                    <th class="p-6">Karyawan</th>
                    <th class="p-6 text-center">Tanggal</th>
                    <th class="p-6 text-center">Masuk</th>
                    <th class="p-6 text-center">Pulang</th>
                    <th class="p-6 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($laporans as $l)
                <tr class="text-black hover:bg-gray-50 transition">
                    <td class="p-6">
                        <p class="font-black text-[11px] uppercase leading-none mb-1">{{ $l->nama }}</p>
                        <p class="text-[8px] text-indigo-500 font-bold uppercase tracking-tighter">{{ $l->departemen }}</p>
                    </td>
                    <td class="p-6 text-center font-bold text-gray-500 text-[10px]">{{ date('d/m/Y', strtotime($l->tanggal)) }}</td>
                    <td class="p-6 text-center font-mono font-black text-indigo-600 text-[10px]">{{ $l->jam_masuk }}</td>
                    <td class="p-6 text-center font-mono font-black text-rose-600 text-[10px]">{{ $l->jam_keluar }}</td>
                    <td class="p-6 text-center">
                        @php $statusColor = match(strtolower($l->status)) { 'hadir'=>'bg-emerald-100 text-emerald-700','telat'=>'bg-amber-100 text-amber-700','izin'=>'bg-blue-100 text-blue-700','sakit'=>'bg-purple-100 text-purple-700','cuti'=>'bg-indigo-100 text-indigo-700','alpha'=>'bg-rose-100 text-rose-700',default=>'bg-gray-100 text-gray-600' }; @endphp
                        <span class="{{ $statusColor }} px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest">{{ $l->status }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-20 text-center uppercase font-black text-xs text-gray-400 italic">Data Tidak Ditemukan</td>
                </tr>
                @endforelse
            </tbody>
            @else
            {{-- Bulanan & Tahunan: format rekap sama --}}
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 uppercase text-[10px] font-black text-gray-400 tracking-widest">
                    <th class="p-6">Karyawan</th>
                    <th class="p-6 text-center text-emerald-600">Hadir</th>
                    <th class="p-6 text-center text-amber-600">Telat</th>
                    <th class="p-6 text-center text-blue-600">Izin</th>
                    <th class="p-6 text-center text-purple-600">Sakit</th>
                    <th class="p-6 text-center text-rose-600">Cuti</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @foreach($laporans as $l)
                <tr class="text-black font-bold hover:bg-gray-50 transition">
                    <td class="p-6">
                        <p class="font-black text-[11px] uppercase leading-none mb-1">{{ $l->nama }}</p>
                        <p class="text-[8px] text-gray-400 uppercase">{{ $l->departemen }}</p>
                    </td>
                    <td class="p-6 text-center text-emerald-600">{{ $l->hadir }}</td>
                    <td class="p-6 text-center text-amber-600">{{ $l->telat }}</td>
                    <td class="p-6 text-center text-blue-600">{{ $l->izin }}</td>
                    <td class="p-6 text-center text-purple-600">{{ $l->sakit }}</td>
                    <td class="p-6 text-center text-rose-600">{{ $l->cuti }}</td>
                </tr>
                @endforeach
            </tbody>
            @endif
        </table>
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
                    borderWidth: 0
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

    function bukaModal() {
        document.getElementById('print-modal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function tutupModal() {
        document.getElementById('print-modal').classList.remove('active');
        document.body.style.overflow = '';
    }

    document.getElementById('print-modal').addEventListener('click', function(e) {
        if (e.target === this) tutupModal();
    });
</script>

@endsection