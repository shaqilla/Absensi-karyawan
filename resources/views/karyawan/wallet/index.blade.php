@extends('layouts.karyawan')
@section('content')
<div class="w-full pb-10 text-black">
    <!-- HERO: DOMPET POIN -->
    <div class="bg-gradient-to-br from-indigo-900 to-indigo-700 p-10 rounded-[3rem] text-white mb-10 shadow-2xl relative overflow-hidden">
        <div class="relative z-10">
            <p class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.2em] mb-2">Poin Integritas Saya</p>
            <div class="flex items-end gap-3">
                <h1 id="user-points" class="text-6xl font-black tracking-tighter">{{ auth()->user()->currentPoints() }}</h1>
                <p class="text-indigo-300 font-bold mb-2 uppercase text-xs">Points</p>
            </div>
        </div>
        <i class="fas fa-coins absolute -right-10 -bottom-10 text-[15rem] opacity-10"></i>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- TAB 1: MARKETPLACE -->
        <div class="lg:col-span-1">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Penukaran Hadiah</h3>
            <div class="space-y-4">
                @foreach($items as $i)
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-indigo-500 transition-all">
                    <div>
                        <h4 class="font-bold text-xs text-slate-700 uppercase leading-none">{{ $i->item_name }}</h4>
                        <p class="text-[10px] font-black text-amber-500 mt-2">{{ $i->point_cost }} POIN</p>
                    </div>
                    <!-- Tombol diubah jadi fungsi JavaScript -->
                    <button type="button" onclick="tukarPoin({{ $i->id }}, '{{ $i->item_name }}', {{ $i->point_cost }})"
                        class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center hover:bg-indigo-600 hover:text-white transition shadow-sm">
                        <i class="fas fa-shopping-cart text-xs"></i>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        <!-- TAB 2: RIWAYAT MUTASI -->
        <div class="lg:col-span-1">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Mutasi Poin</h3>
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 space-y-4 max-h-[400px] overflow-y-auto no-scrollbar" id="history-list">
                @foreach($history as $h)
                <div class="flex items-center justify-between border-b border-slate-50 pb-3">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 {{ $h->amount > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            <i class="fas {{ $h->amount > 0 ? 'fa-plus-circle' : 'fa-minus-circle' }} text-xs"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-700 leading-none">{{ $h->description }}</p>
                            <p class="text-[8px] text-gray-400 mt-1 uppercase">{{ $h->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    <p class="text-xs font-black {{ $h->amount > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $h->amount }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- TAB 3: INVENTORY & COUPONS -->
        <div class="lg:col-span-1">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">My Inventory</h3>
            <div class="bg-slate-900 p-8 rounded-[2.5rem] text-white space-y-4 shadow-xl min-h-[300px]">
                @forelse($tokens as $t)
                <div class="p-4 bg-white/5 rounded-2xl border border-white/10 flex items-center justify-between group hover:bg-white/10 transition">
                    <div class="flex items-center">
                        @if(str_contains(strtolower($t->item->item_name), 'diskon') || str_contains(strtolower($t->item->item_name), 'kupon'))
                            <i class="fas fa-percentage text-emerald-400 mr-3 text-lg"></i>
                        @else
                            <i class="fas fa-ticket-alt text-amber-400 mr-3"></i>
                        @endif
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest leading-none">{{ $t->item->item_name }}</p>
                            <p class="text-[8px] text-slate-500 mt-1 uppercase">ID: #TK-00{{ $t->id }}</p>
                        </div>
                    </div>
                    <span class="text-[8px] font-black bg-indigo-600 text-white px-2 py-1 rounded-lg uppercase">Aktif</span>
                </div>
                @empty
                <div class="text-center py-10">
                    <i class="fas fa-box-open text-slate-700 text-4xl mb-4"></i>
                    <p class="text-slate-500 text-[10px] uppercase italic font-bold">Belum ada item.<br>Silahkan tukar poin anda.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- LEADERBOARD SECTION (TAMBAHAN REVISI) -->
    <div class="mt-16 bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">Peringkat Integritas</h3>
            <span class="bg-amber-100 text-amber-600 px-4 py-1 rounded-full text-[10px] font-black uppercase">Ranking Bulan Ini</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                // Kita ambil dulu semua karyawan, lalu urutkan berdasarkan poin secara DESC (Besar ke Kecil)
                // values() digunakan untuk meriset index array agar urut 0, 1, 2
                $rankings = \App\Models\User::where('role', 'karyawan')
                    ->get()
                    ->sortByDesc(function($u) {
                        return $u->currentPoints();
                    })
                    ->values()
                    ->take(3);
            @endphp

            @foreach($rankings as $index => $rank)
            <div class="flex items-center p-5 bg-slate-50 rounded-3xl border border-slate-100">
                {{-- Warna medali: 1=Emas(Amber), 2=Perak(Slate), 3=Perunggu(Orange) --}}
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-white mr-4
                    {{ $index == 0 ? 'bg-amber-400 shadow-amber-200 shadow-lg' : ($index == 1 ? 'bg-slate-400' : 'bg-orange-400') }}">
                    {{ $index + 1 }}
                </div>
                <div>
                    {{-- Nama dipotong biar gak kepanjangan kalau namanya panjang banget --}}
                    <p class="text-xs font-black text-slate-800 uppercase leading-none">
                        {{ Str::limit($rank->nama, 15) }}
                    </p>
                    <p class="text-[10px] text-indigo-600 font-bold mt-1">{{ $rank->currentPoints() }} PTS</p>
                </div>

                {{-- Icon Crown khusus Juara 1 --}}
                @if($index == 0)
                    <i class="fas fa-crown ml-auto text-amber-400"></i>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    function tukarPoin(itemId, itemName, cost) {
        Swal.fire({
            title: 'Konfirmasi Penukaran',
            text: `Apakah kamu yakin ingin menukar ${cost} poin untuk "${itemName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Tukar Sekarang!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                // Proses AJAX
                fetch(`/karyawan/wallet/exchange/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil Ditukar!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#4f46e5',
                            customClass: { popup: 'rounded-[2rem]' }
                        }).then(() => {
                            location.reload(); // Refresh halaman di tempat
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#4f46e5',
                            customClass: { popup: 'rounded-[2rem]' }
                        });
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                });
            }
        })
    }
</script>
@endsection
