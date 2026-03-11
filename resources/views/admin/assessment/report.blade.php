{{-- resources/views/admin/assessment/report.blade.php --}}
@extends('layouts.admin')

@section('title', 'Laporan Penilaian')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Laporan Penilaian Sikap</h4>
        <small class="text-muted">Rekap rata-rata nilai per kategori</small>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter Periode --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.assessment.report') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1">Filter Periode</label>
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Periode --</option>
                        @foreach($periods as $p)
                            <option value="{{ $p }}" {{ request('period') == $p ? 'selected' : '' }}>
                                {{ $p }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if(request('period'))
                <div class="col-auto">
                    <a href="{{ route('admin.assessment.report') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    @if($allAssessments->isEmpty())
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-inbox display-5 d-block mb-2"></i>
                Belum ada data penilaian{{ request('period') ? ' untuk periode ' . request('period') : '' }}.
            </div>
        </div>
    @else

    {{-- Rata-rata Per Kategori --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-4">
                Rata-rata Nilai per Kategori
                @if(request('period'))
                    <span class="badge bg-primary ms-2">{{ request('period') }}</span>
                @endif
            </h6>

            @foreach($avgPerCategory as $item)
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="fw-semibold">{{ $item['category'] }}</span>
                    <span class="text-muted small">
                        {{ number_format($item['average'], 2) }} / 5
                        <span class="ms-2 text-muted">({{ $item['total'] }} penilaian)</span>
                    </span>
                </div>
                <div class="progress" style="height: 12px;">
                    <div class="progress-bar
                        {{ $item['average'] >= 4 ? 'bg-success' : ($item['average'] >= 3 ? 'bg-warning' : 'bg-danger') }}"
                         style="width: {{ ($item['average'] / 5) * 100 }}%; transition: width 0.6s ease;">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Tabel Detail Penilaian --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">Detail Penilaian</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th></th>
                            <th>Karyawan</th>
                            <th>Periode</th>
                            <th>Rata-rata</th>
                            <th>Catatan</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allAssessments as $i => $assessment)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 flex-shrink-0"
                                         style="width:36px; height:36px; font-size:0.9rem; font-weight:bold;">
                                        {{ strtoupper(substr($assessment->user->nama ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $assessment->user->nama ?? '-' }}</div>
                                        <small class="text-muted">
                                            {{ $assessment->user->karyawan->jabatan ?? '' }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $assessment->period }}</td>
                            <td>
                                @php
                                    $avg = $assessment->details->avg('score') ?? 0;
                                @endphp
                                <span class="badge
                                    {{ $avg >= 4 ? 'bg-success' : ($avg >= 3 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ number_format($avg, 1) }} / 5
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $assessment->general_notes != '-' ? Str::limit($assessment->general_notes, 50) : '-' }}
                                </small>
                            </td>
                            <td>
                                <small>{{ \Carbon\Carbon::parse($assessment->assessment_date)->format('d M Y') }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @endif

</div>
@endsection