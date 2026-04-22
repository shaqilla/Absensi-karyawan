<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zenclock | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body, html { height: 100%; margin: 0; overflow: hidden; }
        #sidebar { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s ease-in-out; }
        .sidebar-text { transition: opacity 0.2s; white-space: nowrap; }
        .sidebar-collapsed { width: 5rem !important; }
        .sidebar-collapsed .sidebar-text, .sidebar-collapsed nav p { display: none !important; }
        .sidebar-collapsed .sidebar-header { justify-content: center !important; padding: 1.5rem 0 !important; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: #4f46e5; border-radius: 10px; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>

<body class="bg-gray-100 antialiased font-sans">

    <div class="flex h-screen w-screen overflow-hidden relative text-black">

        <!-- OVERLAY -->
        <div id="sidebarOverlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-indigo-950/60 backdrop-blur-sm z-40 hidden md:hidden">
        </div>

        <!-- SIDEBAR -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-indigo-950 text-white flex flex-col shadow-2xl transform -translate-x-full md:translate-x-0 md:relative h-full transition-transform duration-300">

            <!-- HEADER SIDEBAR -->
            <div class="sidebar-header flex items-center justify-between p-6 border-b border-indigo-900 h-20">
                <div class="sidebar-text flex items-center">
                    <span class="text-xl font-black tracking-widest uppercase">Zen<span class="text-indigo-400">clock</span></span>
                </div>
                <button onclick="toggleSidebar()" class="text-indigo-400 hover:text-white transition focus:outline-none p-2">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <nav class="flex-1 p-4 space-y-2 mt-4 overflow-y-auto no-scrollbar">

                {{-- UTAMA: BISA DIAKSES ADMIN & PIMPINAN --}}
                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2 sidebar-text">Utama</p>
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-chart-line w-6 text-center"></i>
                    <span class="ml-3 font-bold text-sm sidebar-text uppercase">Dashboard</span>
                </a>

                {{-- MANAJEMEN: KHUSUS ADMIN SAJA --}}
                @if(auth()->user()->role == 'admin')
                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-widest mt-6 mb-2 sidebar-text">Manajemen</p>
                <a href="{{ route('admin.karyawan.index') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.karyawan.*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-users w-6 text-center"></i>
                    <span class="ml-3 font-bold text-sm sidebar-text uppercase tracking-tighter">Data Karyawan</span>
                </a>
                <a href="{{ route('admin.shift.index') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.shift.*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-clock w-6 text-center"></i>
                    <span class="ml-3 font-bold text-sm sidebar-text uppercase tracking-tighter">Shift Kerja</span>
                </a>
                <a href="{{ route('admin.jadwal.index') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.jadwal.*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-calendar-alt w-6 text-center"></i>
                    <span class="ml-3 font-bold text-sm sidebar-text uppercase tracking-tighter">Jadwal Kerja</span>
                </a>
                @endif

                {{-- PENILAIAN --}}
                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-widest mt-6 mb-2 sidebar-text">Penilaian</p>

                {{-- Setup Soal & Kategori: Cuma Admin --}}
                @if(auth()->user()->role == 'admin')
                <a href="{{ route('admin.assessment.categories') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.assessment.categories*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-tags w-6 text-center"></i>
                    <span class="ml-3 font-bold text-sm sidebar-text uppercase tracking-tighter">Kategori</span>
                </a>
                <a href="{{ route('admin.assessment.questions.index') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.assessment.questions.*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-question-circle w-6 text-center"></i>
                    <span class="ml-3 font-bold text-sm uppercase sidebar-text">Data Pertanyaan</span>
                </a>
                @endif

                {{-- Input Nilai & Laporan: Admin & Pimpinan BISA --}}
                <a href="{{ route('admin.assessment.employees') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.assessment.employees') || request()->routeIs('admin.assessment.create') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-pen-nib w-6 text-center"></i>
                    <span class="ml-3 font-bold text-sm sidebar-text uppercase tracking-tighter">Input Penilaian</span>
                </a>
                <a href="{{ route('admin.assessment.report') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.assessment.report') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-chart-line w-6 text-center"></i>
                    <span class="ml-3 font-bold text-sm sidebar-text uppercase tracking-tighter">Laporan Penilaian</span>
                </a>

                {{-- DOMPET INTEGRITAS: KHUSUS ADMIN --}}
                @if(auth()->user()->role == 'admin')
                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mt-6 mb-2 sidebar-text">Ekonomi Sistem</p>
                <a href="{{ route('admin.integrity.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.integrity.*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-coins w-6 text-center text-amber-400"></i>
                    <span class="ml-3 font-bold text-sm uppercase sidebar-text">Aturan Poin</span>
                </a>
                @endif

                {{-- OPERASIONAL --}}
                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-widest mt-6 mb-2 sidebar-text">Operasional</p>

                {{-- Approval Izin & Laporan: Admin & Pimpinan BISA --}}
                <a href="{{ route('admin.pengajuan.index') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.pengajuan.*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-envelope-open-text w-6 text-center"></i>
                    <span class="ml-3 font-bold text-sm sidebar-text uppercase tracking-tighter">Persetujuan Izin</span>
                </a>
                <a href="{{ route('admin.laporan.index') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.laporan.*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-file-signature w-6 text-center"></i>
                    <span class="ml-3 font-bold text-sm sidebar-text uppercase tracking-tighter">Laporan Absensi</span>
                </a>

                {{-- Manual & Lokasi: KHUSUS ADMIN --}}
                @if(auth()->user()->role == 'admin')
                <a href="{{ route('admin.presensi.manual') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.presensi.manual') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-edit w-6 text-center"></i>
                    <span class="ml-3 font-bold text-sm sidebar-text uppercase tracking-tighter">Absensi Manual</span>
                </a>
                <a href="{{ route('admin.lokasi.index') }}"
                    class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.lokasi.*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-indigo-200 hover:bg-indigo-900' }}">
                    <i class="fas fa-map-marked-alt w-6 text-center text-emerald-400"></i>
                    <span class="ml-3 font-bold text-sm sidebar-text uppercase tracking-tighter">Lokasi Kantor</span>
                </a>
                @endif

            </nav>
        </aside>

        <!-- AREA KONTEN -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 overflow-hidden">
            <!-- TOPBAR -->
            <header class="bg-white shadow-sm h-20 flex items-center justify-between px-6 md:px-10 z-30 border-b relative">
                <div class="flex items-center text-black">
                    <button onclick="toggleSidebar()" class="md:hidden p-3 mr-3 text-indigo-950 bg-indigo-50 rounded-2xl focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="text-sm font-bold text-indigo-950 uppercase tracking-widest hidden sm:block">
                        Halo, <span class="text-indigo-600 font-black">{{ auth()->user()->nama }}</span>
                    </div>
                </div>

                <!-- PROFILE DROPDOWN -->
                <div class="relative inline-block text-left">
                    <button id="adminDropdownBtn" class="flex items-center space-x-2 md:space-x-4 focus:outline-none group">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-black text-indigo-950 uppercase leading-none">{{ auth()->user()->nama }}</p>
                            <p class="text-[9px] {{ auth()->user()->role == 'admin' ? 'text-red-500' : 'text-indigo-500' }} font-black uppercase mt-1 italic tracking-[0.2em]">
                                {{ auth()->user()->role }}
                            </p>
                        </div>
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border-2 border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                            <i class="fas fa-user-{{ auth()->user()->role == 'admin' ? 'shield' : 'user-tie' }} text-lg"></i>
                        </div>
                    </button>

                    <div id="adminDropdownMenu" class="hidden absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-[999]">
                        <div class="p-4 border-b border-gray-50 bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            {{ auth()->user()->role }} Account
                        </div>
                        <a href="{{ route('admin.profil') }}" class="flex items-center px-5 py-4 text-sm text-gray-700 hover:bg-indigo-50 border-b border-gray-50 font-bold uppercase text-[9px]">
                            <i class="fas fa-user-cog w-5 text-indigo-500"></i> Setting Profil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-5 py-4 text-sm text-red-600 hover:bg-red-50 font-bold uppercase text-[9px]">
                                <i class="fas fa-power-off w-5"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-10 w-full bg-gray-50">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (window.innerWidth >= 768) {
                sidebar.classList.toggle('sidebar-collapsed');
            } else {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }

        const btn = document.getElementById('adminDropdownBtn');
        const menu = document.getElementById('adminDropdownMenu');
        if (btn && menu) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            });
            window.addEventListener('click', () => {
                if (!menu.classList.contains('hidden')) menu.classList.add('hidden');
            });
        }
    </script>
</body>
</html>
