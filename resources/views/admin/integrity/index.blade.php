@extends('layouts.admin')
@section('content')
<div class="w-full pb-10 text-black">
    <h1 class="text-3xl font-black uppercase tracking-tighter mb-8">Pusat Ekonomi & Integritas</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- 1. ATURAN POIN -->
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-black uppercase text-indigo-600">Aturan Otomatis</h2>
                <button onclick="document.getElementById('modalRule').classList.remove('hidden')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase">+ Aturan</button>
            </div>
            <table class="w-full text-left text-xs">
                <thead><tr class="text-gray-400 border-b uppercase font-black"><th class="pb-3">Nama Aturan</th><th class="pb-3 text-center">Nilai</th><th class="pb-3 text-center">Aksi</th></tr></thead>
                <tbody class="divide-y">
                    @foreach($rules as $r)
                    <tr>
                        <td class="py-4 font-bold uppercase">{{ $r->rule_name }}</td>
                        <td class="py-4 text-center font-black {{ $r->point_modifier > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $r->point_modifier }}</td>
                        <td class="py-4 text-center">
                            <form action="{{ route('admin.integrity.rule.destroy', $r->id) }}" method="POST">@csrf @method('DELETE')<button class="text-rose-500"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 2. MARKETPLACE -->
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-black uppercase text-amber-600">Katalog Hadiah</h2>
                <button onclick="document.getElementById('modalItem').classList.remove('hidden')" class="bg-amber-500 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase">+ Reward</button>
            </div>
            <table class="w-full text-left text-xs">
                <thead><tr class="text-gray-400 border-b uppercase font-black"><th class="pb-3">Nama Reward</th><th class="pb-3 text-center">Harga</th><th class="pb-3 text-center">Aksi</th></tr></thead>
                <tbody class="divide-y">
                    @foreach($items as $i)
                    <tr>
                        <td class="py-4 font-bold uppercase">{{ $i->item_name }}</td>
                        <td class="py-4 text-center font-black text-amber-600">{{ $i->point_cost }} Poin</td>
                        <td class="py-4 text-center">
                            <form action="{{ route('admin.integrity.item.destroy', $i->id) }}" method="POST">@csrf @method('DELETE')<button class="text-rose-500"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. LEADERBOARD INTEGRITAS (MUNCUL DI BAWAH) -->
    <div class="mt-12 bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">Peringkat Poin Teratas</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">Global Leaderboard</p>
            </div>
            <span class="bg-amber-100 text-amber-600 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                Bulan Ini
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($rankings as $index => $rank)
            @php
                $posisi = $index + 1;
                // Logika Penentuan Warna Box Angka
                $bgCircle = 'bg-slate-800'; // Default buat ranking 4++
                $shadowColor = 'shadow-slate-100';

                if ($posisi == 1) {
                    $bgCircle = 'bg-gradient-to-br from-amber-300 to-amber-500';
                    $shadowColor = 'shadow-amber-200';
                } elseif ($posisi == 2) {
                    $bgCircle = 'bg-gradient-to-br from-slate-300 to-slate-400';
                    $shadowColor = 'shadow-slate-200';
                } elseif ($posisi == 3) {
                    $bgCircle = 'bg-gradient-to-br from-orange-300 to-orange-500';
                    $shadowColor = 'shadow-orange-200';
                }
            @endphp

            <div class="flex items-center p-5 bg-white rounded-3xl border border-slate-100 relative group hover:border-indigo-500 hover:shadow-xl hover:shadow-indigo-50/50 transition-all duration-300">
                <!-- Angka Ranking -->
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-white mr-4 {{ $bgCircle }} shadow-lg {{ $shadowColor }} shrink-0">
                    {{ $posisi }}
                </div>

                <!-- Nama & Poin -->
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-black text-slate-800 uppercase leading-none truncate" title="{{ $rank->nama }}">
                        {{ $rank->nama }}
                    </p>
                    <div class="flex items-center mt-2">
                        <span class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-md font-bold">
                            {{ number_format($rank->currentPoints(), 0, ',', '.') }} PTS
                        </span>
                    </div>
                </div>

                <!-- Icon Spesial Peringkat 1 -->
                @if($posisi == 1)
                <div class="absolute -top-3 -right-1 bg-amber-400 w-8 h-8 rounded-full flex items-center justify-center shadow-lg border-4 border-white">
                    <i class="fas fa-crown text-white text-[10px]"></i>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal Aturan -->
<div id="modalRule" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] p-10 max-w-md w-full shadow-2xl">
        <h2 class="text-xl font-black mb-6 uppercase">Aturan Poin Baru</h2>
        <form action="{{ route('admin.integrity.rule.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <input type="text" name="rule_name" placeholder="Cth: Datang Jam 7 Pagi" class="w-full border rounded-xl p-4 border-gray-200" required>
                <select name="condition_operator" class="w-full border rounded-xl p-4 border-gray-200">
                    <option value="<">Mencapai Sebelum ( < )</option>
                    <option value=">">Melewati Jam ( > )</option>
                </select>
                <input type="time" name="condition_value" class="w-full border rounded-xl p-4 border-gray-200" required>
                <input type="number" name="point_modifier" placeholder="Cth: 10 atau -5" class="w-full border rounded-xl p-4 border-gray-200" required>
                <input type="hidden" name="target_role" value="karyawan">
            </div>
            <div class="mt-8 flex gap-3">
                <button type="button" onclick="this.closest('#modalRule').classList.add('hidden')" class="flex-1 py-4 bg-gray-100 rounded-xl font-black uppercase text-xs">Batal</button>
                <button type="submit" class="flex-1 py-4 bg-indigo-600 text-white rounded-xl font-black uppercase text-xs">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Item -->
<div id="modalItem" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] p-10 max-w-md w-full shadow-2xl">
        <h2 class="text-xl font-black mb-6 uppercase text-amber-600">Reward Baru</h2>
        <form action="{{ route('admin.integrity.item.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <input type="text" name="item_name" placeholder="Cth: Token Bebas Telat 15 Menit" class="w-full border rounded-xl p-4 border-gray-200" required>
                <input type="number" name="point_cost" placeholder="Harga Poin (Cth: 50)" class="w-full border rounded-xl p-4 border-gray-200" required>
            </div>
            <div class="mt-8 flex gap-3">
                <button type="button" onclick="this.closest('#modalItem').classList.add('hidden')" class="flex-1 py-4 bg-gray-100 rounded-xl font-black uppercase text-xs">Batal</button>
                <button type="submit" class="flex-1 py-4 bg-amber-500 text-white rounded-xl font-black uppercase text-xs">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
