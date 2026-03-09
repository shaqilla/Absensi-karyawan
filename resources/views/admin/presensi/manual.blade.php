@extends('layouts.admin')

@section('content')
<div class="w-full max-w-4xl">
    <div class="mb-10">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Absensi Manual (Backup)</h1>
        <p class="text-gray-500 text-sm">Gunakan fitur ini hanya jika terjadi kendala teknis pada sistem QR atau HP karyawan.</p>
    </div>

    @if(session('error'))
    <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-700 p-4 mb-6 rounded-xl font-bold uppercase text-xs">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.presensi.store_manual') }}" method="POST" class="p-10">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Karyawan</label>
                        <select name="user_id" class="w-full border-gray-200 rounded-xl p-4 font-bold text-gray-700 outline-none focus:ring-2 focus:ring-indigo-500 border" required>
                            <option value="">-- Pilih Nama --</option>
                            @foreach($karyawans as $k)
                            <option value="{{ $k->id }}">{{ strtoupper($k->nama) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tanggal Absensi</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="w-full border-gray-200 rounded-xl p-4 font-bold outline-none focus:ring-2 focus:ring-indigo-500 border" required>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Shift & Status</label>
                        <div class="flex gap-3">
                            <select name="shift_id" class="flex-1 border-gray-200 rounded-xl p-4 font-bold border outline-none focus:ring-2 focus:ring-indigo-500" required>
                                @foreach($shifts as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_shift }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="flex-1 border-gray-200 rounded-xl p-4 font-bold border outline-none focus:ring-2 focus:ring-indigo-500" required>
                                <option value="hadir">Hadir</option>
                                <option value="telat">Telat</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Alasan Manual (Wajib)</label>
                        <textarea name="keterangan" rows="3" class="w-full border-gray-200 rounded-xl p-4 font-bold border outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: HP Karyawan Rusak / Sistem Error" required></textarea>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex justify-end border-t pt-8">
                <button type="submit" class="bg-indigo-600 text-white px-12 py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg uppercase text-xs">
                    Simpan Absensi Manual
                </button>
            </div>
        </form>
    </div>
</div>
@endsection