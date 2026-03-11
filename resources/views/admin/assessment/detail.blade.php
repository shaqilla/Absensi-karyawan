@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Detail Penilaian</h1>
            <p class="text-gray-400 text-sm italic">Informasi lengkap hasil penilaian karyawan</p>
        </div>
        <a href="{{ route('admin.assessment.history') }}"
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

    {{-- Info Penilaian --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-8 bg-gradient-to-r from-indigo-900 to-indigo-700 text-white">
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mr-6 border border-white/20">
                        <i class="fas fa-clipboard-check text-3xl text-indigo-300"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.2em] mb-1">Informasi Penilaian</p>
                        <h2 class="text-xl font-black uppercase tracking-tight">{{ $assessment->period }}</h2>
                        <p class="text-xs opacity-60 italic">{{ \Carbon\Carbon::parse($assessment->assessment_date)->format('d F Y') }}</p>
                    </div>
                </div>
                <div class="bg-white/10 px-6 py-3 rounded-2xl border border-white/20">
                    <p class="text-[10px] font-black text-indigo-300 uppercase">Rata-rata</p>
                    <p class="text-3xl font-black text-yellow-300">{{ number_format($assessment->details->avg('score'), 1) }} <span class="text-lg">/5</span></p>
                </div>
            </div>
        </div>

        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Penilai --}}
            <div class="bg-indigo-50 p-6 rounded-2xl">
                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3">Penilai</p>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-indigo-200 text-indigo-700 rounded-xl flex items-center justify-center font-black text-xl mr-4">
                        {{ strtoupper(substr($assessment->evaluator->nama ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-black text-gray-800">{{ $assessment->evaluator->nama ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $assessment->evaluator->karyawan->jabatan ?? 'Admin' }}</div>
                    </div>
                </div>
            </div>

            {{-- Yang Dinilai --}}
            <div class="bg-emerald-50 p-6 rounded-2xl">
                <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-3">Yang Dinilai</p>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-emerald-200 text-emerald-700 rounded-xl flex items-center justify-center font-black text-xl mr-4">
                        {{ strtoupper(substr($assessment->evaluatee->nama ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-black text-gray-800">{{ $assessment->evaluatee->nama ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $assessment->evaluatee->karyawan->jabatan ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Nilai per Kategori --}}
    <div class="space-y-6">
        @foreach($categoryScores as $category => $data)
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex justify-between items-center">
                <div>
                    <h3 class="font-black text-indigo-900 text-lg">{{ $category }}</h3>
                    <p class="text-xs text-indigo-600">Rata-rata: {{ $data['average'] }} / 5</p>
                </div>
                <div class="bg-indigo-200 text-indigo-800 font-black px-4 py-2 rounded-xl">
                    {{ $data['average'] }} ★
                </div>
            </div>
            <div class="p-6">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="py-3 text-left text-xs font-black text-gray-400 uppercase">No</th>
                            <th class="py-3 text-left text-xs font-black text-gray-400 uppercase">Pertanyaan</th>
                            <th class="py-3 text-center text-xs font-black text-gray-400 uppercase">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['questions'] as $index => $item)
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="py-4 text-sm font-bold text-gray-400">{{ $index + 1 }}</td>
                            <td class="py-4 text-sm text-gray-700">{{ $item['question'] }}</td>
                            <td class="py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold
                                    {{ $item['score'] >= 4 ? 'bg-green-100 text-green-600' : ($item['score'] >= 3 ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') }}">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $item['score'] >= $i ? 'text-yellow-500' : 'text-gray-300' }}">★</span>
                                @endfor
                                <span class="ml-1">{{ $item['score'] }}/5</span>
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Catatan --}}
    @if($assessment->general_notes)
    <div class="mt-6 bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-4">Catatan / Feedback</p>
        <div class="bg-gray-50 p-6 rounded-2xl italic text-gray-600 border-l-4 border-indigo-400">
            "{{ $assessment->general_notes }}"
        </div>
    </div>
    @endif

    {{-- Tombol Aksi --}}
    @if(auth()->user()->role == 'admin' || $assessment->evaluator_id == auth()->id())
    <div class="mt-8 flex justify-end gap-4">
        <a href="{{ route('admin.assessment.edit', $assessment->id) }}"
            class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-bold hover:bg-indigo-700 transition text-xs uppercase tracking-widest">
            <i class="fas fa-edit mr-2"></i> Edit Penilaian
        </a>
    </div>
    @endif
</div>
@endsection