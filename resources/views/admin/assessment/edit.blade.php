@extends('layouts.admin')

@section('content')
    <div class="w-full pb-10">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4 text-black">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-800 uppercase tracking-tighter">Edit Penilaian Pegawai</h1>
                <p class="text-gray-500 text-sm italic">Perbarui evaluasi kinerja berdasarkan indikator yang tersedia.</p>
            </div>
            <a href="{{ route('admin.assessment.report') }}"
                class="bg-white border border-slate-200 text-slate-400 px-6 py-3 rounded-2xl font-bold hover:bg-gray-50 transition text-xs uppercase tracking-widest flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Batal
            </a>
        </div>

        @if (!isset($assessment))
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-6 rounded-2xl shadow-sm mb-4">
                <p class="font-bold uppercase text-sm">Error: Data penilaian tidak ditemukan.</p>
            </div>
        @else
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden text-black">

                <!-- HEADER KARYAWAN -->
                <div class="p-8 bg-gradient-to-r from-slate-900 to-indigo-900 text-white relative overflow-hidden">
                    <div class="relative z-10 flex items-center">
                        <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mr-6 border border-white/20">
                            <i class="fas fa-user-tie text-3xl text-indigo-200"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.2em] mb-1">Mengedit Penilaian Untuk</p>
                            <h2 class="text-2xl font-black uppercase tracking-tight">{{ $assessment->evaluatee->nama }}</h2>
                            <p class="text-xs opacity-70 italic font-medium">Periode: {{ $assessment->period }}</p>
                        </div>
                    </div>
                    <i class="fas fa-edit absolute -right-4 -bottom-4 text-9xl opacity-10"></i>
                </div>

                {{-- FORM UPDATE --}}
                <form action="{{ route('admin.assessment.update', $assessment->id) }}" method="POST" class="p-6 md:p-12" id="assessmentForm">
                    @csrf
                    @method('PUT')

                    @php $totalQuestions = 0; @endphp

                    <!-- PROGRESS BAR -->
                    <div class="mb-10 bg-slate-50 p-6 rounded-3xl border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Update Progress</span>
                            <span class="text-xs font-black text-indigo-600" id="progressText">0 / 0 Terisi</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden shadow-inner">
                            <div id="progressBar" class="bg-indigo-600 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="space-y-10">
                        @foreach ($categories as $category)
                            <div class="bg-white border border-slate-100 rounded-[2rem] overflow-hidden shadow-sm">
                                <div class="bg-slate-50 px-8 py-5 border-b border-slate-100">
                                    <h3 class="font-black text-slate-800 text-sm uppercase tracking-widest">{{ $category->name }}</h3>
                                </div>

                                <div class="p-8 space-y-8">
                                    @foreach($category->questions as $question)
                                        @php $totalQuestions++; @endphp
                                        <div class="question-row group" data-question="{{ $question->id }}">
                                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                                                <div class="flex-1">
                                                    <p class="font-bold text-slate-700 text-sm md:text-base">{{ $question->question }}</p>
                                                </div>

                                                <!-- RATING STARS -->
                                                <div class="rating-wrapper" data-question="{{ $question->id }}">
                                                    {{-- Value lama diambil dari array $oldScores --}}
                                                    <input type="hidden" name="scores[{{ $question->id }}]"
                                                           id="rating-{{ $question->id }}"
                                                           class="rating-value"
                                                           value="{{ $oldScores[$question->id] ?? '' }}" required>

                                                    <div class="flex flex-row-reverse justify-end gap-2">
                                                        @for ($i = 5; $i >= 1; $i--)
                                                            <button type="button" class="star-btn text-3xl md:text-4xl"
                                                                data-question="{{ $question->id }}" data-rating="{{ $i }}"
                                                                onclick="setRating({{ $question->id }}, {{ $i }})"
                                                                onmouseover="hoverRating({{ $question->id }}, {{ $i }})"
                                                                onmouseout="resetRating({{ $question->id }})">★</button>
                                                        @endfor
                                                    </div>
                                                    <div class="text-[9px] font-black mt-2 rating-label uppercase text-right" id="label-{{ $question->id }}">
                                                        <span class="rating-text">Belum Dinilai</span>
                                                        <span class="rating-value-text hidden"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- FEEDBACK AREA -->
                    <div class="mt-12 bg-slate-50 p-8 rounded-[2rem] border border-slate-100">
                        <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-4">Catatan Feedback</label>
                        <textarea name="notes" rows="4" class="w-full border-2 border-white rounded-2xl p-6 outline-none focus:border-indigo-500 font-medium text-sm text-slate-600 shadow-sm">{{ old('notes', $assessment->general_notes) }}</textarea>
                    </div>

                    <div class="mt-12 flex justify-end gap-4 border-t pt-10">
                        <button type="submit" class="bg-indigo-600 text-white px-12 py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-xl uppercase text-xs tracking-widest">
                            Simpan Perubahan <i class="fas fa-save ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <style>
        .star-btn { color: #e2e8f0; background: none; border: none; cursor: pointer; transition: all 0.2s; }
        .star-btn.active { color: #fbbf24; text-shadow: 0 0 10px rgba(251, 191, 36, 0.4); }
        .star-btn.hover { color: #f59e0b; transform: scale(1.1); }
        .rating-label.active .rating-text { display: none; }
        .rating-label.active .rating-value-text { display: inline; }
    </style>

    <script>
        // Inisialisasi data lama dari PHP ke JS
        const ratings = @json($oldScores);
        const totalQuestions = {{ $totalQuestions }};
        const ratingLabels = {
            1: { text: 'Sangat Kurang', class: 'text-rose-600' },
            2: { text: 'Kurang', class: 'text-orange-500' },
            3: { text: 'Cukup', class: 'text-amber-500' },
            4: { text: 'Baik', class: 'text-emerald-500' },
            5: { text: 'Sangat Baik', class: 'text-green-600' }
        };

        // Jalankan saat halaman load untuk nampilin nilai lama
        document.addEventListener('DOMContentLoaded', function() {
            for (const [qId, val] of Object.entries(ratings)) {
                setRating(parseInt(qId), parseInt(val));
            }
            updateProgress();
        });

        function setRating(questionId, rating) {
            ratings[questionId] = rating;
            document.getElementById('rating-' + questionId).value = rating;
            updateStars(questionId, rating);

            const label = document.getElementById('label-' + questionId);
            if (label) {
                const valText = label.querySelector('.rating-value-text');
                valText.innerHTML = `★ ${ratingLabels[rating].text}`;
                valText.className = `rating-value-text font-black ${ratingLabels[rating].class}`;
                label.classList.add('active');
            }
            updateProgress();
        }

        function updateStars(qId, val) {
            document.querySelectorAll(`.star-btn[data-question="${qId}"]`).forEach(star => {
                if (parseInt(star.dataset.rating) <= val) star.classList.add('active');
                else star.classList.remove('active');
            });
        }

        function hoverRating(qId, val) {
            document.querySelectorAll(`.star-btn[data-question="${qId}"]`).forEach(star => {
                if (parseInt(star.dataset.rating) <= val) star.classList.add('hover');
                else star.classList.remove('hover');
            });
        }

        function resetRating(qId) {
            document.querySelectorAll(`.star-btn[data-question="${qId}"]`).forEach(star => star.classList.remove('hover'));
            if (ratings[qId]) updateStars(qId, ratings[qId]);
        }

        function updateProgress() {
            const answered = Object.keys(ratings).length;
            const percent = (answered / totalQuestions) * 100;
            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressText').innerHTML = `${answered} / ${totalQuestions} Pertanyaan Terisi`;
        }
    </script>
@endsection
