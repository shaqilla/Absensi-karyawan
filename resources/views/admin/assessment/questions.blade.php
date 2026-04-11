@extends('layouts.admin')

@section('content')
    <div class="w-full pb-10 text-black">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-black uppercase tracking-tighter">Daftar Pertanyaan</h1>
                <p class="text-gray-400 text-sm">Kelola butir penilaian untuk setiap kategori.</p>
            </div>
            <button onclick="document.getElementById('modalAdd').classList.remove('hidden')"
                class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase shadow-lg shadow-indigo-100">+
                Tambah Pertanyaan</button>
        </div>

        @if (session('success'))
            <div
                class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 rounded-xl text-xs font-bold uppercase">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b text-[10px] font-black text-slate-400 uppercase">
                            <th class="p-6">Kategori</th>
                            <th class="p-6">Isi Pertanyaan</th>
                            <th class="p-6 text-center">Status</th>
                            <th class="p-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($questions as $q)
                            <tr class="hover:bg-indigo-50/30 transition">
                                <td class="p-6">
                                    <span
                                        class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg font-black text-[10px] uppercase">
                                        {{ $q->category->name ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="p-6">
                                    <p class="font-bold text-slate-700 text-sm">{{ $q->question }}</p>
                                </td>
                                <td class="p-6 text-center">
                                    <span
                                        class="px-3 py-1 rounded-full text-[9px] font-black uppercase {{ $q->is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                                        {{ $q->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="p-6 text-center">
                                    <form action="{{ route('admin.assessment.questions.destroy', $q->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus pertanyaan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 transition">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-20 text-center text-gray-300 uppercase font-black text-xs">Belum
                                    ada pertanyaan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div id="modalAdd"
        class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] p-10 max-w-lg w-full shadow-2xl">
            <h2 class="text-xl font-black mb-6 uppercase text-slate-800">Tambah Pertanyaan</h2>
            <form action="{{ route('admin.assessment.questions.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block">Pilih Kategori</label>
                        <select name="category_id"
                            class="w-full border-gray-200 rounded-xl p-4 font-bold border outline-none focus:ring-2 focus:ring-indigo-500"
                            required>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block">Isi Pertanyaan</label>
                        <textarea name="question" placeholder="Apa yang ingin dinilai?"
                            class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                            rows="3" required></textarea>
                    </div>
                </div>
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')"
                        class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-xl font-black uppercase text-xs">Batal</button>
                    <button type="submit"
                        class="flex-1 py-4 bg-indigo-600 text-white rounded-xl font-black uppercase text-xs shadow-lg shadow-indigo-100">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
