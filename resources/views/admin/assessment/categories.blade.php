@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Indikator Penilaian</h1>
        <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest">+ Tambah Indikator</button>
    </div>

    <!-- TABEL KATEGORI -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden text-black">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b text-[10px] font-black text-gray-400 uppercase">
                    <th class="p-6">Nama Indikator</th>
                    <th class="p-6">Deskripsi</th>
                    <th class="p-6">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($categories as $cat)
                <tr>
                    <td class="p-6 font-bold text-indigo-600 uppercase">{{ $cat->name }}</td>
                    <td class="p-6 text-xs text-gray-500">{{ $cat->description }}</td>
                    <td class="p-6">
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase {{ $cat->is_active ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            {{ $cat->is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH (Simple Hidden Div) -->
<div id="modalAdd" class="hidden fixed inset-0 bg-black/50 z-[999] flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] p-10 max-w-md w-full shadow-2xl">
        <h2 class="text-xl font-black mb-6 uppercase">Indikator Baru</h2>
        <form action="{{ route('admin.assessment.categories.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <input type="text" name="name" placeholder="Nama Indikator (Cth: Teamwork)" class="w-full border-gray-200 rounded-xl p-4 outline-none focus:ring-2 focus:ring-indigo-500 border" required>
                <textarea name="description" placeholder="Deskripsi..." class="w-full border-gray-200 rounded-xl p-4 outline-none focus:ring-2 focus:ring-indigo-500 border" required></textarea>
            </div>
            <div class="mt-8 flex gap-3">
                <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')" class="flex-1 py-4 bg-gray-100 rounded-xl font-bold uppercase text-xs">Batal</button>
                <button type="submit" class="flex-1 py-4 bg-indigo-600 text-white rounded-xl font-bold uppercase text-xs">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection