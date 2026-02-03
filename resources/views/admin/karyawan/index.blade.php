@extends('layouts.admin')

@section('content')
<div class="w-full">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Data Karyawan</h1>
            <p class="text-gray-500 text-sm">Manajemen akun dan filter departemen.</p>
        </div>
        <a href="{{ route('admin.karyawan.create') }}" class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 uppercase text-xs tracking-widest text-center">
            + Tambah User
        </a>
    </div>

    <!-- FILTER & STATISTIK CARD -->
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 mb-8">
        <form action="{{ route('admin.karyawan.index') }}" method="GET" class="flex flex-col md:flex-row md:items-end gap-6">
            <!-- Dropdown Departemen -->
            <div class="flex-1">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Filter Departemen</label>
                <select name="departemen_id" class="w-full border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 text-sm">
                    <option value="">Semua Departemen</option>
                    @foreach($departemens as $d)
                        <option value="{{ $d->id }}" {{ request('departemen_id') == $d->id ? 'selected' : '' }}>
                            {{ $d->nama_departemen }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex gap-2">
                <button type="submit" class="bg-slate-800 text-white px-6 py-3 rounded-xl font-bold hover:bg-slate-900 transition text-sm">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                @if(request('departemen_id'))
                    <a href="{{ route('admin.karyawan.index') }}" class="bg-gray-100 text-gray-500 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition text-sm">
                        Reset
                    </a>
                @endif
            </div>

            <!-- Hasil Statistik Ringkas -->
            <div class="md:ml-auto flex items-center bg-indigo-50 px-6 py-3 rounded-2xl border border-indigo-100">
                <div class="text-right">
                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">Total Karyawan</p>
                    <p class="text-xl font-black text-indigo-600 leading-none">{{ $totalFiltered }} Orang</p>
                </div>
                <i class="fas fa-users-cog text-indigo-200 text-3xl ml-4"></i>
            </div>
        </form>
    </div>

    <!-- TABEL KARYAWAN -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Karyawan & Role</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">NIP</th>
                        <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Departemen</th>
                        <th class="p-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($karyawans as $k)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-6">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold mr-4 border border-slate-200">
                                    {{ substr($k->user->nama, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-800 uppercase text-sm tracking-tight">{{ $k->user->nama }}</p>
                                    @if($k->user->role == 'admin')
                                        <span class="text-[9px] bg-rose-100 text-rose-600 px-2 py-0.5 rounded font-black uppercase">Admin</span>
                                    @else
                                        <span class="text-[9px] bg-blue-100 text-blue-600 px-2 py-0.5 rounded font-black uppercase">Karyawan</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-6 font-mono text-xs font-bold text-gray-500">{{ $k->nip }}</td>
                        <td class="p-6">
                            <span class="text-xs font-bold text-gray-700 bg-gray-100 px-3 py-1 rounded-lg">
                                {{ $k->departemen->nama_departemen }}
                            </span>
                        </td>
                        <td class="p-6">
                            <div class="flex justify-center items-center gap-2">
                                <a href="{{ route('admin.karyawan.edit', $k->id) }}" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition-all shadow-sm">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <form action="{{ route('admin.karyawan.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-20 text-center">
                            <i class="fas fa-user-slash text-gray-200 text-5xl mb-4"></i>
                            <p class="text-gray-400 italic">Data karyawan tidak ditemukan untuk departemen ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection