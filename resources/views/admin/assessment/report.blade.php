@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Laporan Penilaian</h1>
            <p class="text-gray-400 text-sm italic">Rekap rata-rata nilai per kategori</p>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filter Periode --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 mb-6">
        <form method="GET" action="{{ route('admin.assessment.report') }}" class="flex items-end gap-4">
            <div class="flex-1 max-w-xs">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">Filter Periode</label>
                <select name="period" onchange="this.form.submit()"
                    class="w-full border border-gray-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-black">
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

    @if($allAssessments->isEmpty())
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-16 text-center text-gray-400">
        <i class="fas fa-inbox text-5xl mb-4 block"></i>
        <p class="font-bold">Belum ada data penilaian{{ request('period') ? ' untuk periode ' . request('period') : '' }}.</p>
    </div>

    @else

    {{-- Rata-rata Per Kategori --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight">Rata-rata Nilai per Kategori</h2>
            @if(request('period'))
            <span class="bg-indigo-100 text-indigo-600 text-[10px] font-black px-3 py-1 rounded-full uppercase">
                {{ request('period') }}
            </span>
            @endif
        </div>
        <div class="space-y-4">
            @foreach($avgPerCategory as $item)
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="font-bold text-sm text-gray-700 uppercase">{{ $item['category'] }}</span>
                    <div class="text-right">
                        <span class="font-black text-sm {{ $item['average'] >= 4 ? 'text-green-600' : ($item['average'] >= 3 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ number_format($item['average'], 2) }} / 5
                        </span>
                        <span class="text-[10px] text-gray-400 ml-2">({{ $item['total'] }} penilaian)</span>
                    </div>
                </div>
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

    {{-- Tabel Detail Penilaian --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight">Detail Penilaian</h2>
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
                    @foreach($allAssessments as $i => $assessment)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-5 text-sm text-gray-400 font-bold">{{ $i + 1 }}</td>
                        <td class="p-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center font-black text-sm flex-shrink-0">
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
                            @php $avg = $assessment->details->avg('score') ?? 0; @endphp
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
@endsection