@extends('layouts.admin')

@section('content')
<div class="max-w-4xl">
    <h1 class="text-2xl font-black text-gray-800 uppercase mb-8">Setel Jadwal Kerja</h1>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.jadwal.store') }}" method="POST" class="p-10">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Karyawan</label>
                        <select name="user_id" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold" required>
                            @foreach($karyawans as $k)
                                <option value="{{ $k->id }}">{{ strtoupper($k->nama) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Shift</label>
                        <select name="shift_id" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold" required>
                            @foreach($shifts as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_shift }} ({{ $s->jam_masuk }} - {{ $s->jam_keluar }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Hari Kerja</label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($hari as $h)
                        <label class="flex items-center p-3 border border-gray-100 rounded-xl hover:bg-indigo-50 cursor-pointer transition">
                            <input type="checkbox" name="hari[]" value="{{ $h }}" class="w-4 h-4 text-indigo-600 mr-3">
                            <span class="text-sm font-bold text-gray-700">{{ $h }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="mt-10 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-12 py-4 rounded-2xl font-black hover:bg-indigo-700 shadow-lg uppercase text-sm">
                    Simpan Jadwal Kerja
                </button>
            </div>
        </form>
    </div>
</div>
@endsection