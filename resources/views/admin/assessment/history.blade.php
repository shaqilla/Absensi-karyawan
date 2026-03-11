@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Riwayat Penilaian</h1>
            <p class="text-gray-400 text-sm italic">Semua data penilaian karyawan</p>
        </div>
        <a href="{{ route('admin.dashboard') }}"
            class="bg-gray-100 text-gray-500 px-6 py-2 rounded-xl font-bold hover:bg-gray-200 transition text-xs uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm font-semibold">
        {{ session('error') }}
    </div>
    @endif

    {{-- Filter & Search --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 mb-6">
        <form method="GET" action="{{ route('admin.assessment.history') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">Cari Karyawan</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama karyawan..."
                    class="w-full border border-gray-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-black">
            </div>
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">Periode</label>
                <select name="period" class="border border-gray-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-black min-w-[150px]">
                    <option value="">Semua Periode</option>
                    @foreach($periods ?? [] as $p)
                    <option value="{{ $p }}" {{ request('period') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 transition text-xs uppercase tracking-widest">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                @if(request('search') || request('period'))
                <a href="{{ route('admin.assessment.history') }}"
                    class="bg-gray-100 text-gray-500 px-6 py-3 rounded-xl text-xs font-black uppercase hover:bg-gray-200 transition">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Daftar Penilaian --}}
    @if($assessments->isEmpty())
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-16 text-center text-gray-400">
        <i class="fas fa-inbox text-5xl mb-4 block"></i>
        <p class="font-bold text-sm">Belum ada data penilaian.</p>
    </div>
    @else
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b text-[10px] font-black text-gray-400 uppercase">
                        <th class="p-5">No</th>
                        <th class="p-5">Tanggal</th>
                        <th class="p-5">Penilai</th>
                        <th class="p-5">Karyawan</th>
                        <th class="p-5">Periode</th>
                        <th class="p-5 text-center">Rata-rata</th>
                        <th class="p-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($assessments as $index => $assessment)
                    @php
                    $avgScore = $assessment->details->avg('score') ?? 0;
                    $avgColor = $avgScore >= 4 ? 'text-green-600' : ($avgScore >= 3 ? 'text-yellow-600' : 'text-red-600');
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-5 text-sm font-bold text-gray-400">{{ $assessments->firstItem() + $index }}</td>
                        <td class="p-5">
                            <div class="font-semibold text-sm">{{ \Carbon\Carbon::parse($assessment->assessment_date)->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($assessment->assessment_date)->format('H:i') }}</div>
                        </td>
                        <td class="p-5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center font-black text-sm">
                                    {{ strtoupper(substr($assessment->evaluator->nama ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-sm">{{ $assessment->evaluator->nama ?? '-' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $assessment->evaluator->karyawan->jabatan ?? 'Admin' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center font-black text-sm">
                                    {{ strtoupper(substr($assessment->evaluatee->nama ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-sm">{{ $assessment->evaluatee->nama ?? '-' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $assessment->evaluatee->karyawan->jabatan ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-5">
                            <span class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-2 py-1 rounded-lg">
                                {{ $assessment->period }}
                            </span>
                        </td>
                        <td class="p-5 text-center">
                            <span class="font-black text-lg {{ $avgColor }}">{{ number_format($avgScore, 1) }}</span>
                            <span class="text-xs text-gray-400">/5</span>
                            <div class="flex justify-center gap-0.5 mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-xs {{ $avgScore >= $i ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                    @endfor
                            </div>
                        </td>
                        <td class="p-5 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.assessment.detail', $assessment->id) }}"
                                    class="bg-indigo-100 text-indigo-600 hover:bg-indigo-200 w-8 h-8 rounded-lg flex items-center justify-center transition"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->role == 'admin' || $assessment->evaluator_id == auth()->id())
                                <a href="{{ route('admin.assessment.edit', $assessment->id) }}"
                                    class="bg-yellow-100 text-yellow-600 hover:bg-yellow-200 w-8 h-8 rounded-lg flex items-center justify-center transition"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @if(auth()->user()->role == 'admin')
                                <form method="POST" action="{{ route('admin.assessment.destroy', $assessment->id) }}"
                                    onsubmit="return confirm('Yakin ingin menghapus penilaian ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-100 text-red-600 hover:bg-red-200 w-8 h-8 rounded-lg flex items-center justify-center transition"
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($assessments->hasPages())
        <div class="p-6 border-t border-gray-100">
            {{ $assessments->links() }}
        </div>
        @endif
    </div>
    @endif
</div>
@endsection