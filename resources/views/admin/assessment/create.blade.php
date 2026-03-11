@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">Input Penilaian Pegawai</h1>
            <p class="text-gray-500 text-sm italic">Berikan evaluasi kinerja berdasarkan indikator yang tersedia.</p>
        </div>
        <a href="{{ route('admin.assessment.employees') }}" class="bg-gray-100 text-gray-500 px-6 py-2 rounded-xl font-bold hover:bg-gray-200 transition text-xs uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <!-- HEADER KARYAWAN YANG DINILAI -->
        <div class="p-8 bg-indigo-900 text-white flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mr-6 border border-white/20">
                    <i class="fas fa-user-tie text-3xl text-indigo-300"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.2em] mb-1">Objek Penilaian</p>
                    <h2 class="text-xl font-black uppercase tracking-tight">{{ $target->nama }}</h2>
                    <p class="text-xs opacity-60 italic">{{ $target->karyawan->jabatan ?? 'Staf' }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black text-indigo-300 uppercase">Periode</p>
                <p class="text-lg font-black uppercase">{{ now()->format('F Y') }}</p>
            </div>
        </div>

        <form action="{{ route('admin.assessment.store') }}" method="POST" class="p-8 md:p-12">
            @csrf
            <input type="hidden" name="evaluatee_id" value="{{ $target->id }}">

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="border-b-2 border-slate-100">
                            <th class="py-6 px-4 text-sm font-black text-slate-800 uppercase tracking-widest w-1/2">Indikator Penilaian</th>
                            <th class="py-6 px-2 text-center text-[10px] font-black text-slate-600 uppercase" colspan="5">Rating (Bintang)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($categories as $cat)
                        <tr class="group hover:bg-indigo-50/30 transition-colors">
                            <td class="py-6 px-4">
                                <p class="font-bold text-slate-700 text-sm group-hover:text-indigo-600 transition-colors">{{ $cat->name }}</p>
                                <p class="text-[10px] text-slate-400 mt-1 italic">{{ $cat->description }}</p>
                            </td>
                            <!-- RATING BINTANG DENGAN EFEK TERISI SEMUA -->
                            <td class="py-6 px-2 text-center" colspan="5">
                                <div class="rating-wrapper-{{ $cat->id }} flex justify-center items-center gap-1">
                                    <input type="hidden" name="scores[{{ $cat->id }}]" id="rating-{{ $cat->id }}" class="rating-value" required>
                                    
                                    @for($i = 1; $i <= 5; $i++)
                                    <button type="button" 
                                            class="star-btn star-{{ $cat->id }} text-3xl focus:outline-none transition-all duration-150"
                                            data-category="{{ $cat->id }}"
                                            data-rating="{{ $i }}"
                                            onclick="setRating({{ $cat->id }}, {{ $i }})"
                                            onmouseover="hoverRating({{ $cat->id }}, {{ $i }})"
                                            onmouseout="resetRating({{ $cat->id }})">
                                        ★
                                    </button>
                                    @endfor
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-12">
                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-4">Catatan Feedback untuk {{ $target->nama }}</label>
                <textarea name="notes" rows="4" class="w-full border-2 border-slate-100 rounded-[2rem] p-6 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-sm text-slate-600" placeholder="Berikan masukan atau apresiasi..."></textarea>
            </div>

            <div class="mt-12 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-16 py-4 rounded-3xl font-black hover:bg-indigo-700 transition shadow-xl uppercase text-xs tracking-widest active:scale-95">
                    Kirim Penilaian <i class="fas fa-check-circle ml-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Style default bintang */
.star-btn {
    color: #d1d5db; /* gray-300 */
    transition: all 0.2s ease;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0 2px;
}

/* Style bintang aktif (terisi) */
.star-btn.active {
    color: #fbbf24; /* yellow-400 */
}

/* Style saat hover */
.star-btn.hover {
    color: #fbbf24; /* yellow-400 */
}

/* Efek scale saat diklik */
.star-btn:active {
    transform: scale(0.9);
}
</style>

<script>
// Simpan nilai rating untuk setiap kategori
let ratings = {};

// Set rating untuk kategori tertentu
function setRating(categoryId, rating) {
    ratings[categoryId] = rating;
    document.getElementById(`rating-${categoryId}`).value = rating;
    updateStars(categoryId, rating);
}

// Update tampilan bintang
function updateStars(categoryId, rating) {
    const stars = document.querySelectorAll(`.star-${categoryId}`);
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

// Efek hover - semua bintang sampai index tertentu jadi kuning
function hoverRating(categoryId, rating) {
    const stars = document.querySelectorAll(`.star-${categoryId}`);
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('hover');
        } else {
            star.classList.remove('hover');
        }
    });
}

// Reset hover - kembali ke rating yang sudah dipilih
function resetRating(categoryId) {
    const stars = document.querySelectorAll(`.star-${categoryId}`);
    stars.forEach(star => {
        star.classList.remove('hover');
    });
    
    // Kembalikan ke rating yang sudah dipilih (jika ada)
    if (ratings[categoryId]) {
        updateStars(categoryId, ratings[categoryId]);
    } else {
        // Jika belum ada rating, semua bintang kembali ke abu-abu
        stars.forEach(star => {
            star.classList.remove('active');
        });
    }
}

// Validasi form
document.querySelector('form').addEventListener('submit', function(e) {
    let isValid = true;
    let firstEmpty = null;
    
    @foreach($categories as $cat)
    const rating{{ $cat->id }} = document.getElementById('rating-{{ $cat->id }}').value;
    if (!rating{{ $cat->id }}) {
        isValid = false;
        // Animasi untuk baris yang belum diisi
        document.querySelectorAll(`.star-{{ $cat->id }}`).forEach(star => {
            star.style.animation = 'shake 0.5s ease';
            setTimeout(() => star.style.animation = '', 500);
        });
    }
    @endforeach
    
    if (!isValid) {
        e.preventDefault();
        alert('Harap isi semua rating bintang terlebih dahulu!');
    }
});

// Animasi shake
const style = document.createElement('style');
style.textContent = `
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
`;
document.head.appendChild(style);
</script>
@endsection