@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    <h1 class="text-3xl font-black text-gray-800 uppercase mb-8">Pilih Karyawan Untuk Dinilai</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-black">
        @foreach($employees as $e)
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold mr-4">
                    {{ substr($e->user->nama, 0, 1) }}
                </div>
                <div>
                    <h3 class="font-bold text-sm uppercase leading-none mb-1">{{ $e->user->nama }}</h3>
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">{{ $e->departemen->nama_departemen ?? 'No Dept' }}</p>
                </div>
            </div>
            <a href="{{ route('admin.assessment.create', $e->user_id) }}" class="bg-indigo-600 text-white px-5 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-indigo-700 transition">
                Nilai <i class="fas fa-chevron-right ml-1"></i>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection