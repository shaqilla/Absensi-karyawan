@extends('layouts.admin')

@section('content')
<div class="w-full max-w-4xl">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Edit Jadwal Kerja</h1>
            <p class="text-gray-500 text-xs italic">Ubah penugasan shift untuk pegawai ini.</p>
        </div>
        <a href="{{ route('admin.jadwal.index') }}" class="text-gray-400 hover:text-indigo-600 transition font-bold text-[10px] uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl md:rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.jadwal.update', $jadwal->id) }}" method="POST" class="p-8 md:p-12">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pegawai</label>
                        <select name="user_id" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold bg-gray-50" required>
                            @foreach($karyawans as $k)
                            <option value="{{ $k->id }}" {{ $jadwal->user_id == $k->id ? 'selected' : '' }}>{{ strtoupper($k->nama) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Hari</label>
                        <select name="hari" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold bg-gray-50" required>
                            @foreach($hari as $h)
                            <option value="{{ $h }}" {{ $jadwal->hari == $h ? 'selected' : '' }}>{{ strtoupper($h) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Shift Kerja</label>
                        <select name="shift_id" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold bg-gray-50" required>
                            @foreach($shifts as $s)
                            <option value="{{ $s->id }}" {{ $jadwal->shift_id == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_shift }} ({{ date('H:i', strtotime($s->jam_masuk)) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Status Jadwal</label>
                        <div class="flex gap-6 mt-2">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="status" value="aktif" class="w-4 h-4 text-indigo-600" {{ $jadwal->status == 'aktif' ? 'checked' : '' }}>
                                <span class="ml-2 text-xs font-bold text-gray-600 uppercase">Wajib Masuk</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="status" value="libur" class="w-4 h-4 text-indigo-600" {{ $jadwal->status == 'libur' ? 'checked' : '' }}>
                                <span class="ml-2 text-xs font-bold text-gray-600 uppercase">Libur</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex justify-end border-t pt-8">
                <button type="submit" class="w-full md:w-auto bg-slate-900 text-white px-16 py-4 rounded-2xl font-black hover:bg-black transition shadow-lg uppercase text-xs">
                    Update Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection