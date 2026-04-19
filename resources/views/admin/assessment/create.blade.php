@extends('layouts.admin')

@section('content')
    <div class="w-full pb-10 text-black">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-800 uppercase tracking-tighter">Input Penilaian Pegawai
                </h1>
                <p class="text-gray-500 text-sm italic">Berikan evaluasi kinerja berdasarkan indikator yang tersedia.</p>
            </div>
            <a href="{{ route('admin.assessment.employees') }}"
                class="bg-white border border-slate-200 text-slate-400 px-6 py-3 rounded-2xl font-bold hover:bg-gray-50 transition text-xs uppercase tracking-widest flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        @if (!isset($target) || !$target)
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-6 rounded-2xl shadow-sm mb-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-2xl mr-4"></i>
                    <p class="font-bold uppercase text-sm">Error: Data karyawan tidak ditemukan.</p>
                </div>
            </div>
        @else
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">

                <!-- HEADER KARYAWAN (BANNER) -->
                <div class="p-8 bg-gradient-to-r from-indigo-900 to-indigo-700 text-white relative overflow-hidden">
                    <div class="relative z-10 flex items-center">
                        <div
                            class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mr-6 border border-white/20 shadow-inner">
                            <i class="fas fa-user-tie text-3xl text-indigo-200"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.2em] mb-1">Objek
                                Penilaian</p>
                            <h2 class="text-2xl font-black uppercase tracking-tight">{{ $target->nama }}</h2>
                            <p class="text-xs opacity-70 italic font-medium">
                                {{ $target->karyawan->jabatan ?? 'Position not set' }}</p>
                        </div>
                    </div>
                    <i class="fas fa-certificate absolute -right-4 -bottom-4 text-9xl opacity-10"></i>
                </div>

                <form action="{{ route('admin.assessment.store') }}" method="POST" class="p-6 md:p-12" id="assessmentForm">
                    @csrf

                    {{-- FIX: HITUNG TOTAL PERTANYAAN DI SINI --}}
                    @php
                        $totalQuestions = $categories->sum(function($cat) {
                            return $cat->questions->count();
                        });
                    @endphp

                    <input type="hidden" name="evaluatee_id" value="{{ $target->id }}">

                    @if ($categories->isEmpty())
                        <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-700 p-6 rounded-2xl shadow-sm">
                            <p class="font-bold uppercase text-xs">Perhatian: Belum ada kategori penilaian aktif di sistem.
                            </p>
                        </div>
                    @else
                        <!-- PROGRESS BAR CERDAS -->
                        <div class="mb-10 bg-slate-50 p-6 rounded-3xl border border-slate-100">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Progress
                                    Evaluasi</span>
                                <span class="text-xs font-black text-indigo-600" id="progressText">0 / {{ $totalQuestions }}
                                    Terisi</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden shadow-inner">
                                <div id="progressBar"
                                    class="bg-indigo-600 h-3 rounded-full transition-all duration-500 ease-out shadow-lg shadow-indigo-200"
                                    style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- LOOP KATEGORI & PERTANYAAN -->
                        <div class="space-y-10">
                            @foreach ($categories as $category)
                                <div
                                    class="bg-white border border-slate-50 rounded-[2rem] overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                    <div class="bg-indigo-50/50 px-8 py-5 border-b border-indigo-50">
                                        <h3 class="font-black text-indigo-900 text-sm uppercase tracking-widest">
                                            {{ $category->name }}</h3>
                                        <p class="text-[10px] text-indigo-400 font-bold uppercase mt-1 italic">
                                            {{ $category->description }}</p>
                                    </div>

                                    <div class="p-8 space-y-8">
                                        @forelse($category->questions as $question)
                                            <div class="question-row group transition-all"
                                                data-question="{{ $question->id }}">
                                                <div
                                                    class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                                                    <div class="flex-1">
                                                        <div class="flex items-start gap-4">
                                                            <div
                                                                class="bg-indigo-100 text-indigo-600 text-[10px] font-black w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm border border-indigo-200">
                                                                {{ $loop->iteration }}
                                                            </div>
                                                            <div class="space-y-1">
                                                                <p
                                                                    class="font-bold text-slate-700 text-sm md:text-base leading-tight">
                                                                    {{ $question->question }}</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- INTERACTIVE STAR RATING -->
                                                    <div class="rating-wrapper lg:ml-0"
                                                        data-question="{{ $question->id }}">
                                                        <input type="hidden" name="scores[{{ $question->id }}]"
                                                            id="rating-{{ $question->id }}" class="rating-value" required>
                                                        <div class="flex flex-row-reverse justify-end gap-2">
                                                            @for ($i = 5; $i >= 1; $i--)
                                                                <button type="button"
                                                                    class="star-btn text-3xl md:text-4xl focus:outline-none transition-all duration-150"
                                                                    data-question="{{ $question->id }}"
                                                                    data-rating="{{ $i }}"
                                                                    onclick="setRating({{ $question->id }}, {{ $i }})"
                                                                    onmouseover="hoverRating({{ $question->id }}, {{ $i }})"
                                                                    onmouseout="resetRating({{ $question->id }})">★</button>
                                                            @endfor
                                                        </div>
                                                        <div class="text-[9px] font-black mt-2 text-slate-300 rating-label uppercase tracking-widest text-right"
                                                            id="label-{{ $question->id }}">
                                                            <span class="rating-text">Belum Dinilai</span>
                                                            <span class="rating-value-text hidden"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div
                                                class="text-center text-slate-300 text-xs py-4 italic uppercase font-bold tracking-widest">
                                                Pertanyaan belum tersedia.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- FEEDBACK AREA -->
                    <div class="mt-12 bg-slate-50 p-8 rounded-[2rem] border border-slate-100 text-black">
                        <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-4">
                            <i class="fas fa-comment-alt mr-2"></i>Catatan Feedback untuk
                            {{ explode(' ', $target->nama)[0] }}
                        </label>
                        <textarea name="notes" rows="4"
                            class="w-full border-2 border-white rounded-2xl p-6 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-sm text-slate-600 shadow-sm"
                            placeholder="Berikan saran konstruktif..."></textarea>
                    </div>

                    <!-- SUBMIT BUTTONS -->
                    <div class="mt-12 flex flex-col sm:flex-row gap-4 justify-end border-t border-slate-50 pt-10">
                        <button type="button" onclick="window.location.href='{{ route('admin.assessment.employees') }}'"
                            class="bg-white border border-slate-200 text-slate-400 px-10 py-4 rounded-2xl font-black hover:bg-slate-50 transition text-xs uppercase tracking-widest">
                            Batalkan
                        </button>
                        <button type="submit"
                            class="bg-indigo-600 text-white px-12 py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-xl shadow-indigo-200 uppercase text-xs tracking-widest active:scale-95">
                            Simpan Penilaian <i class="fas fa-paper-plane ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <style>
        .star-btn { color: #e2e8f0; background: none; border: none; cursor: pointer; }
        .star-btn.active { color: #fbbf24; text-shadow: 0 0 15px rgba(251, 191, 36, 0.4); }
        .star-btn.hover { color: #f59e0b; transform: scale(1.2); }
        .question-row.incomplete { border-left: 4px solid #f87171; background-color: #fff1f2; padding-left: 15px; }
        .rating-label.active .rating-text { display: none; }
        .rating-label.active .rating-value-text { display: inline; }
    </style>

    <script>
        const ratings = {};
        const totalQuestions = {{ $totalQuestions }};
        const ratingLabels = {
            1: { text: 'Sangat Kurang', class: 'text-rose-600' },
            2: { text: 'Kurang', class: 'text-orange-500' },
            3: { text: 'Cukup', class: 'text-amber-500' },
            4: { text: 'Baik', class: 'text-emerald-500' },
            5: { text: 'Sangat Baik', class: 'text-green-600' }
        };

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
                const starVal = parseInt(star.dataset.rating);
                if (starVal <= val) star.classList.add('active');
                else star.classList.remove('active');
                star.classList.remove('hover');
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
            else document.querySelectorAll(`.star-btn[data-question="${qId}"]`).forEach(star => star.classList.remove('active'));
        }

        function updateProgress() {
            const answered = Object.keys(ratings).length;
            const percent = (answered / totalQuestions) * 100;
            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressText').innerHTML = answered + ' / ' + totalQuestions + ' Pertanyaan Terisi';
        }

        document.getElementById('assessmentForm').addEventListener('submit', function(e) {
            let valid = true;
            document.querySelectorAll('.rating-value').forEach(input => {
                if (!input.value) {
                    valid = false;
                    input.closest('.question-row').classList.add('incomplete');
                }
            });

            if (!valid) {
                e.preventDefault();
                Swal.fire('Belum Lengkap', 'Harap isi semua indikator penilaian sebelum menyimpan.', 'warning');
            }
        });
    </script>
@endsection
