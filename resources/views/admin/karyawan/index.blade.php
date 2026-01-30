@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Data Karyawan & User</h1>
    <a href="{{ route('admin.karyawan.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
        <i class="fas fa-plus mr-2"></i> Tambah User/Karyawan
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-gray-50 text-gray-400 text-xs uppercase font-semibold">
                <th class="p-4">NIP</th>
                <th class="p-4">Nama & Role</th>
                <th class="p-4">Departemen</th>
                <th class="p-4">Jabatan</th>
                <th class="p-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($karyawans as $k)
            <tr class="text-sm hover:bg-gray-50 transition">
                <td class="p-4 font-mono text-gray-500">{{ $k->nip }}</td>
                <td class="p-4">
                    <div class="font-bold text-gray-800">{{ $k->user->nama }}</div>
                    <!-- Badge Role -->
                    @if($k->user->role == 'admin')
                        <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-md font-black uppercase">Admin</span>
                    @else
                        <span class="text-[10px] bg-blue-100 text-blue-600 px-2 py-0.5 rounded-md font-black uppercase">Karyawan</span>
                    @endif
                </td>
                <td class="p-4 text-gray-600">{{ $k->departemen->nama_departemen }}</td>
                <td class="p-4 text-gray-600">{{ $k->jabatan }}</td>
                <td class="p-4 text-center">
                    <div class="flex justify-center space-x-2">
                        
                        <!-- TOMBOL EDIT DI SINI (SUDAH DIPERBAIKI) -->
                        <a href="{{ route('admin.karyawan.edit', $k->id) }}" class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-edit"></i>
                        </a>

                        <!-- Tombol Hapus dengan Form -->
                        <form action="{{ route('admin.karyawan.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus karyawan ini? User login juga akan terhapus.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection