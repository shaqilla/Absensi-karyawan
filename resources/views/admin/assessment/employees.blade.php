@extends('layouts.admin')

@section('content')
<div class="w-full pb-10">

    {{-- Header --}}
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Input Penilaian</h1>
            <p class="text-gray-400 text-sm italic font-medium">Pilih karyawan untuk diberikan evaluasi performa periode ini.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-black">
        @foreach($employees as $e)
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col justify-between transition-all hover:shadow-md">

            {{-- Info Karyawan --}}
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl flex-shrink-0 border border-indigo-100 uppercase shadow-sm">
                    {{ strtoupper(substr($e->user?->nama ?? '?', 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-black text-sm uppercase leading-tight text-gray-800">{{ $e->user->nama ?? '-' }}</h3>
                    <p class="text-[9px] text-indigo-500 font-bold uppercase tracking-widest mt-0.5">{{ $e->departemen->nama_departemen ?? 'General' }}</p>
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">{{ $e->jabatan ?? '-' }}</p>
                </div>
            </div>

            {{-- Status & Tombol --}}
            <div class="mt-4">
                @if($e->assessed_this_month)
                {{-- Sudah Dinilai --}}
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2 h-2 bg-green-400 rounded-full inline-block"></span>
                    <span class="text-[10px] font-black text-green-600 uppercase tracking-widest">Sudah Dinilai Bulan Ini</span>
                </div>

                @php
                $latestAssessment = \App\Models\Assessment::where('evaluatee_id', $e->user_id)
                ->whereMonth('assessment_date', now()->month)
                ->whereYear('assessment_date', now()->year)
                ->latest()->first();
                @endphp

                <div class="flex gap-2">
                    @if($latestAssessment)
                    {{-- BUTTON LIHAT (GANTI JADI PREVIEW MODAL) --}}
                    <button type="button" onclick="openPreview({{ $latestAssessment->id }})"
                        class="flex-1 bg-indigo-50 text-indigo-600 py-2.5 rounded-xl text-[10px] font-black uppercase text-center hover:bg-indigo-600 hover:text-white transition shadow-sm border border-indigo-100">
                        <i class="fas fa-eye mr-1"></i> Lihat
                    </button>

                    <a href="{{ route('admin.assessment.edit', $latestAssessment->id) }}"
                        class="flex-1 bg-amber-50 text-amber-600 py-2.5 rounded-xl text-[10px] font-black uppercase text-center hover:bg-amber-500 hover:text-white transition shadow-sm border border-amber-100">
                        <i class="fas fa-pen mr-1"></i> Edit
                    </a>
                    @endif
                </div>

                @else
                {{-- Belum Dinilai --}}
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2 h-2 bg-gray-300 rounded-full inline-block"></span>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Belum Dinilai</span>
                </div>
                <a href="{{ route('admin.assessment.create', $e->user_id) }}"
                    class="block w-full bg-indigo-600 text-white py-2.5 rounded-xl text-[10px] font-black uppercase text-center hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                    <i class="fas fa-star mr-1"></i> Nilai Sekarang
                </a>
                @endif
            </div>

        </div>
        @endforeach
    </div>

</div>

<!-- ============================================================
     MODAL PREVIEW NILAI (Popup Review)
     ============================================================ -->
<div id="modalPreview" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all duration-300">
    <div class="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl overflow-hidden border border-white/20 transform scale-95 transition-transform duration-300">

        <!-- Header Modal -->
        <div class="p-8 bg-indigo-900 text-white flex justify-between items-center">
            <div>
                <h2 class="text-xl font-black uppercase tracking-tight">Review Penilaian</h2>
                <p id="p_nama" class="text-[10px] text-indigo-300 font-bold uppercase mt-1 tracking-widest"></p>
            </div>
            <button onclick="closePreview()" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition text-2xl focus:outline-none">&times;</button>
        </div>

        <!-- Body Modal -->
        <div id="p_body" class="p-8 space-y-4 max-h-[55vh] overflow-y-auto no-scrollbar">
            <!-- Loading Spinner (Awalnya Muncul Ini) -->
            <div class="text-center py-10">
                <i class="fas fa-circle-notch fa-spin text-4xl text-indigo-600"></i>
                <p class="text-[10px] font-black text-gray-400 mt-4 uppercase">Mengambil Data...</p>
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-center">
            <button onclick="closePreview()" class="px-10 py-3 bg-slate-800 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-black transition shadow-lg">Tutup Preview</button>
        </div>
    </div>
</div>

<script>
    function openPreview(id) {
        const modal = document.getElementById('modalPreview');
        const body = document.getElementById('p_body');
        const nameLabel = document.getElementById('p_nama');

        // Munculkan Modal & Tampilkan Loading
        modal.classList.remove('hidden');
        body.innerHTML = '<div class="text-center py-10"><i class="fas fa-circle-notch fa-spin text-4xl text-indigo-600"></i><p class="text-[10px] font-black text-gray-400 mt-4 uppercase">Memproses Data...</p></div>';

        // Panggil data lewat AJAX (Diarahkan ke route assessment.detail)
        fetch(`/admin/assessment/detail/${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                nameLabel.innerText = data.evaluatee.nama;

                let html = '';
                data.details.forEach(item => {
                    // Logika generate bintang kuning
                    let stars = '';
                    for (let i = 1; i <= 5; i++) {
                        stars += `<i class="fa-star ${i <= item.score ? 'fas text-amber-400' : 'far text-gray-200'} text-xs"></i> `;
                    }

                    html += `
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div>
                        <p class="text-[9px] font-black text-indigo-600 uppercase tracking-widest mb-1">${item.question.category.name}</p>
                        <p class="text-[11px] font-bold text-slate-700 leading-tight">${item.question.question || '-'}</p>
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <div class="mb-1">${stars}</div>
                        <span class="text-[10px] font-black text-gray-400 bg-white px-2 py-0.5 rounded border border-gray-100">SKOR: ${item.score}</span>
                    </div>
                </div>`;
                });

                // Tambahkan Catatan Feedback di Bawah
                if (data.general_notes) {
                    html += `
                <div class="mt-4 p-5 bg-indigo-50 rounded-3xl border border-indigo-100">
                    <p class="text-[9px] font-black text-indigo-400 uppercase mb-2 tracking-widest">Catatan / Feedback Admin:</p>
                    <p class="text-xs italic text-indigo-900 font-medium leading-relaxed">"${data.general_notes}"</p>
                </div>`;
                }

                body.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                body.innerHTML = '<div class="text-center py-10"><i class="fas fa-exclamation-triangle text-rose-500 text-3xl mb-4"></i><p class="text-xs font-black text-rose-600 uppercase">Gagal memuat data!</p></div>';
            });
    }

    function closePreview() {
        document.getElementById('modalPreview').classList.add('hidden');
    }

    // Klik di luar modal buat nutup
    window.onclick = function(event) {
        const modal = document.getElementById('modalPreview');
        if (event.target == modal) {
            closePreview();
        }
    }
</script>
@endsection