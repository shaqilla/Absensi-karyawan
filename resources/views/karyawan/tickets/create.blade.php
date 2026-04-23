@extends('layouts.karyawan')

@section('content')
<div class="max-w-4xl mx-auto pb-10 text-black">
    <div class="mb-8">
        <a href="{{ route('karyawan.tickets.index') }}" class="text-indigo-600 text-xs font-black uppercase tracking-widest hover:text-indigo-800 transition flex items-center gap-2 mb-4">
            <i class="fas fa-arrow-left"></i> Kembali ke List
        </a>
        <h1 class="text-3xl font-black uppercase tracking-tighter">Buat Aduan Baru</h1>
        <p class="text-slate-500 text-sm">Sampaikan kendala Anda secara mendetail agar kami bisa memberikan solusi tepat waktu.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- FORM KIRI -->
        <div class="lg:col-span-2">
            <form action="{{ route('karyawan.tickets.store') }}" method="POST" class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Subject Aduan</label>
                        <input type="text" name="subject" id="subjectInput" 
                            class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold placeholder:font-normal" 
                            placeholder="Contoh: Masalah Koneksi Internet di Lantai 2" autocomplete="off" required>
                    </div>

                    <!-- FITUR 2: FULL TEXT SEARCH - ALERT ADUAN SERUPA -->
                    <div id="duplicateAlert" class="hidden transform transition-all duration-300">
                        <div class="p-5 bg-amber-50 border border-amber-100 rounded-[1.5rem]">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-search"></i>
                                </div>
                                <p class="text-[10px] font-black text-amber-700 uppercase tracking-widest">Aduan Serupa Ditemukan (Pencegahan Duplikasi):</p>
                            </div>
                            <div id="duplicateList" class="space-y-2">
                                <!-- List aduan serupa muncul di sini via AJAX -->
                            </div>
                            <p class="mt-3 text-[9px] text-amber-600/70 italic">*Jika masalahnya sama, Anda tidak perlu membuat tiket baru. Silakan pantau tiket yang sudah ada.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Prioritas Kendala</label>
                            <select name="priority" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none">
                                <option value="Low">Low - Tidak Mendesak</option>
                                <option value="Mid" selected>Mid - Penting</option>
                                <option value="High">High - Darurat / Urgent</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <div class="p-4 bg-indigo-50 rounded-2xl w-full">
                                <p class="text-[9px] text-indigo-600 font-bold leading-tight uppercase tracking-tighter">
                                    <i class="fas fa-info-circle mr-1"></i> SLA Respon: <br>
                                    High < 1 Jam, Mid < 4 Jam, Low < 24 Jam.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Deskripsi Masalah</label>
                        <textarea name="description" id="descriptionInput" rows="5" 
                            class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" 
                            placeholder="Jelaskan secara detail: Kapan terjadi, apa kendalanya, dan langkah apa yang sudah Anda ambil..." required></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                            <span>Kirim Laporan Aduan</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- SIDEBAR INFO KANAN -->
        <div class="space-y-6">
            <div class="bg-slate-900 p-8 rounded-[2rem] text-white relative overflow-hidden shadow-xl">
                <div class="relative z-10">
                    <h3 class="text-xs font-black uppercase mb-4 text-indigo-400 tracking-widest">Tips Melapor</h3>
                    <ul class="space-y-4">
                        <li class="flex gap-3">
                            <i class="fas fa-check-circle text-emerald-400 mt-1"></i>
                            <p class="text-[11px] leading-relaxed opacity-80 font-medium">Gunakan judul yang jelas dan spesifik.</p>
                        </li>
                        <li class="flex gap-3">
                            <i class="fas fa-check-circle text-emerald-400 mt-1"></i>
                            <p class="text-[11px] leading-relaxed opacity-80 font-medium">Lampirkan deskripsi langkah-langkah yang menyebabkan error.</p>
                        </li>
                        <li class="flex gap-3">
                            <i class="fas fa-check-circle text-emerald-400 mt-1"></i>
                            <p class="text-[11px] leading-relaxed opacity-80 font-medium">Cek aduan serupa untuk mempercepat solusi.</p>
                        </li>
                    </ul>
                </div>
                <i class="fas fa-lightbulb absolute -bottom-4 -right-4 text-8xl text-white/5 rotate-12"></i>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT ANTI DUPLIKAT (PASTI JALAN) -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const subjectInput = document.getElementById('subjectInput');
        const duplicateAlert = document.getElementById('duplicateAlert'); 
        const duplicateList = document.getElementById('duplicateList'); 
        
        let searchTimer;

        if(subjectInput) {
            subjectInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                let val = this.value;

                if (val.length >= 3) { 
                    console.log("Nyari kata: " + val); // Cek Console F12

                    searchTimer = setTimeout(() => {
                        fetch("{{ route('karyawan.tickets.check') }}", {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json', 
                                'Accept': 'application/json', 
                                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                            },
                            body: JSON.stringify({ subject: val })
                        })
                        .then(res => res.json())
                        .then(data => {
                            console.log("Dapet data:", data); // Cek Console F12
                            
                            if (data.length > 0) {
                                duplicateAlert.classList.remove('hidden'); 
                                
                                let html = '';
                                data.forEach(t => {
                                    html += `
                                        <div class="p-4 bg-white border border-amber-200 rounded-xl mb-2 shadow-sm flex justify-between items-center">
                                            <div>
                                                <p class="text-sm font-bold text-slate-800">${t.subject}</p>
                                                <p class="text-[10px] font-black uppercase text-amber-600 mt-1">Status: ${t.status}</p>
                                            </div>
                                        </div>
                                    `;
                                });
                                duplicateList.innerHTML = html; 
                            } else {
                                duplicateAlert.classList.add('hidden'); 
                            }
                        })
                        .catch(err => console.error('Error FETCH:', err));
                    }, 500);
                } else {
                    duplicateAlert.classList.add('hidden');
                }
            });
        }
    });
</script>
@endsection