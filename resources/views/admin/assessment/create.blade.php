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

    @if(!isset($target) || !$target)
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">Data karyawan tidak ditemukan.</span>
    </div>
    @else
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <!-- HEADER KARYAWAN -->
        <div class="p-8 bg-gradient-to-r from-indigo-900 to-indigo-700 text-white">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mr-6 border border-white/20">
                    <i class="fas fa-user-tie text-3xl text-indigo-300"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.2em] mb-1">Objek Penilaian</p>
                    <h2 class="text-xl font-black uppercase tracking-tight">{{ $target->nama }}</h2>
                    <p class="text-xs opacity-60 italic">{{ $target->karyawan->jabatan ?? '-' }}</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.assessment.store') }}" method="POST" class="p-8 md:p-12" id="assessmentForm">
            @csrf
            <input type="hidden" name="evaluatee_id" value="{{ $target->id }}">

            @if($categories->isEmpty())
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4">
                <strong class="font-bold">Perhatian!</strong>
                <span class="block sm:inline">Belum ada kategori penilaian yang aktif.</span>
            </div>
            @else
            <!-- Progress Bar -->
            <div class="mb-8 bg-slate-50 p-4 rounded-2xl">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-slate-600">Progress Penilaian</span>
                    <span class="text-sm font-bold text-indigo-600" id="progressText">0/{{ $totalQuestions }} Pertanyaan</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" id="progressBar" style="width: 0%"></div>
                </div>
            </div>

            <!-- KATEGORI & PERTANYAAN -->
            <div class="space-y-8">
                @foreach($categories as $category)
                <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100">
                        <h3 class="font-black text-indigo-900 text-lg">{{ $category->name }}</h3>
                        <p class="text-xs text-indigo-600 italic">{{ $category->description }}</p>
                    </div>
                    <div class="p-6 space-y-6">
                        @forelse($category->activeQuestions as $question)
                        <div class="question-row" data-question="{{ $question->id }}">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        <span class="bg-indigo-100 text-indigo-600 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                            {{ $question->order }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-slate-700">{{ $question->question }}</p>
                                            @if($question->description)
                                            <p class="text-xs text-slate-400 italic mt-1">{{ $question->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!-- STAR RATING -->
                                <div class="rating-wrapper ml-9 md:ml-0" data-question="{{ $question->id }}">
                                    <input type="hidden" name="scores[{{ $question->id }}]" id="rating-{{ $question->id }}" class="rating-value" required>
                                    <div class="flex flex-row-reverse justify-end gap-1">
                                        @for($i = 5; $i >= 1; $i--)
                                        <button type="button" class="star-btn text-3xl focus:outline-none transition-all duration-150"
                                            data-question="{{ $question->id }}" data-rating="{{ $i }}"
                                            onclick="setRating({{ $question->id }}, {{ $i }})"
                                            onmouseover="hoverRating({{ $question->id }}, {{ $i }})"
                                            onmouseout="resetRating({{ $question->id }})">★</button>
                                        @endfor
                                    </div>
                                    <div class="text-xs font-medium mt-1 text-slate-400 rating-label" id="label-{{ $question->id }}">
                                        <span class="rating-text">Pilih rating</span>
                                        <span class="rating-value-text hidden text-yellow-600 font-bold"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-slate-400 py-4">
                            Tidak ada pertanyaan untuk kategori ini
                        </div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Catatan Feedback -->
            <div class="mt-8 bg-slate-50 p-6 rounded-3xl">
                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-4">
                    <i class="fas fa-pen mr-2"></i>Catatan Feedback untuk {{ $target->nama }}
                </label>
                <textarea name="notes" rows="4" class="w-full border-2 border-slate-100 rounded-2xl p-6 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-sm text-slate-600" placeholder="Berikan masukan atau apresiasi secara umum..."></textarea>
            </div>

            <!-- Tombol Aksi -->
            <div class="mt-12 flex flex-col sm:flex-row gap-4 justify-end">
                <button type="button" onclick="window.location.href='{{ route('admin.assessment.employees') }}'"
                    class="bg-gray-100 text-gray-500 px-8 py-4 rounded-2xl font-bold hover:bg-gray-200 transition text-xs uppercase tracking-widest">
                    <i class="fas fa-times mr-2"></i> Batal
                </button>
                <button type="submit" class="bg-indigo-600 text-white px-12 py-4 rounded-2xl font-bold hover:bg-indigo-700 transition shadow-lg text-xs uppercase tracking-widest active:scale-95">
                    <i class="fas fa-save mr-2"></i> Simpan Penilaian
                </button>
            </div>
        </form>
    </div>
    @endif
</div>

<style>
    .star-btn {
        color: #e2e8f0;
        transition: all 0.2s;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0 2px;
        line-height: 1;
    }

    .star-btn.active {
        color: #fbbf24;
        text-shadow: 0 0 5px rgba(251, 191, 36, 0.3);
    }

    .star-btn.hover {
        color: #fbbf24;
        transform: scale(1.1);
    }

    .star-btn:active {
        transform: scale(0.9);
    }

    .question-row {
        transition: all 0.2s;
        padding: 12px;
        border-radius: 16px;
    }

    .question-row:hover {
        background-color: #f8fafc;
    }

    .question-row.incomplete {
        background-color: #fef2f2;
        animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-5px);
        }

        75% {
            transform: translateX(5px);
        }
    }

    .rating-label {
        transition: all 0.2s;
        min-width: 100px;
    }

    .rating-label.active .rating-text {
        display: none;
    }

    .rating-label.active .rating-value-text {
        display: inline;
    }
</style>

<script>
    const ratings = {};
    const totalQuestions = {{ $totalQuestions }};

    const ratingLabels = {
        1: {
            text: 'Sangat Kurang',
            class: 'text-red-600'
        },
        2: {
            text: 'Kurang',
            class: 'text-orange-600'
        },
        3: {
            text: 'Cukup',
            class: 'text-yellow-600'
        },
        4: {
            text: 'Baik',
            class: 'text-green-600'
        },
        5: {
            text: 'Sangat Baik',
            class: 'text-emerald-600'
        }
    };

    function setRating(questionId, rating) {
        ratings[questionId] = rating;
        document.getElementById('rating-' + questionId).value = rating;
        updateStars(questionId, rating);
        const label = document.getElementById('label-' + questionId);
        if (label) {
            const valueText = label.querySelector('.rating-value-text');
            valueText.innerHTML = `★ ${rating} - ${ratingLabels[rating].text}`;
            valueText.className = `rating-value-text font-bold ${ratingLabels[rating].class}`;
            label.classList.add('active');
        }
        updateProgress();
    }

    function updateStars(questionId, rating) {
        document.querySelectorAll(`.star-btn[data-question="${questionId}"]`).forEach(star => {
            const starRating = parseInt(star.dataset.rating);
            if (starRating <= rating) star.classList.add('active');
            else star.classList.remove('active');
            star.classList.remove('hover');
        });
    }

    function hoverRating(questionId, hoverVal) {
        document.querySelectorAll(`.star-btn[data-question="${questionId}"]`).forEach(star => {
            const starRating = parseInt(star.dataset.rating);
            if (starRating <= hoverVal) star.classList.add('hover');
            else star.classList.remove('hover');
        });
    }

    function resetRating(questionId) {
        document.querySelectorAll(`.star-btn[data-question="${questionId}"]`).forEach(star => star.classList.remove('hover'));
        if (ratings[questionId]) updateStars(questionId, ratings[questionId]);
        else {
            document.querySelectorAll(`.star-btn[data-question="${questionId}"]`).forEach(star => star.classList.remove('active'));
            const label = document.getElementById('label-' + questionId);
            if (label) label.classList.remove('active');
        }
    }

    function updateProgress() {
        const answered = Object.keys(ratings).length;
        const percent = (answered / totalQuestions) * 100;
        document.getElementById('progressBar').style.width = percent + '%';
        document.getElementById('progressText').innerHTML = answered + '/' + totalQuestions + ' Pertanyaan';
        if (answered === totalQuestions) {
            document.getElementById('progressBar').classList.add('bg-emerald-600');
            document.getElementById('progressText').classList.add('text-emerald-600');
        } else {
            document.getElementById('progressBar').classList.remove('bg-emerald-600');
            document.getElementById('progressText').classList.remove('text-emerald-600');
        }
    }

    document.getElementById('assessmentForm').addEventListener('submit', function(e) {
        let valid = true;
        let firstInvalid = null;
        document.querySelectorAll('.rating-value').forEach(input => {
            if (!input.value) {
                valid = false;
                const row = input.closest('.question-row');
                if (row) {
                    row.classList.add('incomplete');
                    setTimeout(() => row.classList.remove('incomplete'), 1000);
                    if (!firstInvalid) firstInvalid = row;
                }
            }
        });
        if (!valid) {
            e.preventDefault();
            alert('Harap isi semua pertanyaan terlebih dahulu! (' + Object.keys(ratings).length + '/' + totalQuestions + ' terisi)');
            if (firstInvalid) firstInvalid.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        updateProgress();
        // Jika ada data existing, bisa diisi nanti
    });
</script>
@endsection