@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Indikator Penilaian</h1>
        <button onclick="document.getElementById('modalAdd').classList.remove('hidden')"
            class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest">
            + Tambah Indikator
        </button>
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

    <!-- TABEL KATEGORI -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden text-black">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b text-[10px] font-black text-gray-400 uppercase">
                    <th class="p-6">Nama Indikator</th>
                    <th class="p-6">Deskripsi</th>
                    <th class="p-6">Status</th>
                    <th class="p-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $cat)
                <tr class="{{ $cat->is_active ? '' : 'opacity-50' }}">
                    <td class="p-6 font-bold text-indigo-600 uppercase">{{ $cat->name }}</td>
                    <td class="p-6 text-xs text-gray-500">{{ $cat->description }}</td>
                    <td class="p-6">
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase {{ $cat->is_active ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            {{ $cat->is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </td>
                    <td class="p-6">
                        <div class="flex items-center justify-center gap-2">

                            {{-- Tombol Edit --}}
                            <button onclick="openEdit({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description) }}')"
                                class="bg-indigo-50 text-indigo-600 px-3 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-indigo-100 transition">
                                <i class="fas fa-pencil-alt mr-1"></i> Edit
                            </button>

                            {{-- Tombol Toggle Aktif/Nonaktif --}}
                            <form action="{{ route('admin.assessment.categories.toggle', $cat->id) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="{{ $cat->is_active ? 'bg-yellow-50 text-yellow-600 hover:bg-yellow-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }} px-3 py-2 rounded-xl text-[10px] font-black uppercase transition">
                                    <i class="fas fa-{{ $cat->is_active ? 'pause' : 'play' }} mr-1"></i>
                                    {{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>

                            {{-- Tombol Hapus --}}
                            <form action="{{ route('admin.assessment.categories.destroy', $cat->id) }}" method="POST" class="inline"
                                onsubmit="return confirm('Hapus kategori ini? Kategori yang sudah digunakan tidak bisa dihapus.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="bg-red-50 text-red-600 px-3 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-red-100 transition">
                                    <i class="fas fa-trash mr-1"></i> Hapus
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-12 text-center text-gray-400 text-sm">
                        <i class="fas fa-inbox text-3xl mb-3 block"></i>
                        Belum ada kategori penilaian. Tambahkan sekarang!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH -->
<div id="modalAdd" class="hidden fixed inset-0 bg-black/50 z-[999] flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] p-10 max-w-md w-full shadow-2xl">
        <h2 class="text-xl font-black mb-6 uppercase">Indikator Baru</h2>
        <form action="{{ route('admin.assessment.categories.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">Nama Indikator</label>
                    <input type="text" name="name" placeholder="Cth: Teamwork, Kedisiplinan"
                        class="w-full border-gray-200 rounded-xl p-4 outline-none focus:ring-2 focus:ring-indigo-500 border text-sm" required>
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">Deskripsi</label>
                    <textarea name="description" placeholder="Deskripsi indikator..."
                        class="w-full border-gray-200 rounded-xl p-4 outline-none focus:ring-2 focus:ring-indigo-500 border text-sm" rows="3"></textarea>
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">Tipe</label>
                    <select name="type" class="w-full border-gray-200 rounded-xl p-4 outline-none focus:ring-2 focus:ring-indigo-500 border text-sm" required>
                        <option value="Employee">Employee (Karyawan)</option>
                        <option value="Student">Student (Siswa)</option>
                    </select>
                </div>
            </div>
            <div class="mt-8 flex gap-3">
                <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')"
                    class="flex-1 py-4 bg-gray-100 rounded-xl font-bold uppercase text-xs">Batal</button>
                <button type="submit"
                    class="flex-1 py-4 bg-indigo-600 text-white rounded-xl font-bold uppercase text-xs">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT -->
<div id="modalEdit" class="hidden fixed inset-0 bg-black/50 z-[999] flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] p-10 max-w-md w-full shadow-2xl">
        <h2 class="text-xl font-black mb-6 uppercase">Edit Indikator</h2>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">Nama Indikator</label>
                    <input type="text" name="name" id="editName"
                        class="w-full border-gray-200 rounded-xl p-4 outline-none focus:ring-2 focus:ring-indigo-500 border text-sm" required>
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 block">Deskripsi</label>
                    <textarea name="description" id="editDescription"
                        class="w-full border-gray-200 rounded-xl p-4 outline-none focus:ring-2 focus:ring-indigo-500 border text-sm" rows="3"></textarea>
                </div>
            </div>
            <div class="mt-8 flex gap-3">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')"
                    class="flex-1 py-4 bg-gray-100 rounded-xl font-bold uppercase text-xs">Batal</button>
                <button type="submit"
                    class="flex-1 py-4 bg-indigo-600 text-white rounded-xl font-bold uppercase text-xs">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEdit(id, name, description) {
        // Set action form edit sesuai ID kategori
        document.getElementById('editForm').action = `/admin/assessment/categories/${id}`;
        document.getElementById('editName').value = name;
        document.getElementById('editDescription').value = description;
        document.getElementById('modalEdit').classList.remove('hidden');
    }

    // Tutup modal kalau klik di luar
    document.getElementById('modalAdd').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
    document.getElementById('modalEdit').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>
@endsection