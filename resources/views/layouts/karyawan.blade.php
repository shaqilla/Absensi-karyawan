<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zenclock</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body, html { height: 100%; margin: 0; overflow: hidden; }
        .dropdown-menu { display: none; }
        .dropdown-menu.show { display: block; }
        
        /* Scrollbar rapi untuk sidebar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #4f46e5; border-radius: 10px; }

        /* Animasi Sidebar Mobile */
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100 antialiased font-sans">

    <div class="flex h-screen w-screen overflow-hidden relative">
        
        <!-- OVERLAY (Hanya muncul di HP saat sidebar terbuka) -->
        <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity"></div>

        <!-- SIDEBAR (Kiri) -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-indigo-950 text-white flex flex-col shadow-2xl transform -translate-x-full md:translate-x-0 md:relative transition-transform duration-300 ease-in-out">
            <div class="p-8 text-xl font-black border-b border-indigo-900 tracking-widest text-center uppercase relative">
                Zen<span class="text-indigo-400">clock</span>
                <!-- Tombol Close (Hanya di Mobile) -->
                <button onclick="toggleSidebar()" class="md:hidden absolute right-4 top-8 text-indigo-400">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <nav class="flex-1 p-4 space-y-2 mt-4 overflow-y-auto">
                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-2">Utama</p>
                
                <a href="{{ route('karyawan.dashboard') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('karyawan.dashboard') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-th-large w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Dashboard</span>
                </a>

                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mt-6 mb-2">Kehadiran</p>

                <a href="{{ route('karyawan.scan') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('karyawan.scan') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-camera w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Scan Presensi</span>
                </a>

                <a href="{{ route('karyawan.jadwal.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('karyawan.jadwal.index') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-calendar-alt w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Jadwal Kerja Saya</span>
                </a>

                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mt-6 mb-2">Layanan</p>

                <a href="{{ route('karyawan.izin.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('karyawan.izin.*') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-envelope-open-text w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Riwayat Izin</span>
                </a>
            </nav>
        </aside>

        <!-- AREA KONTEN (Kanan) -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50">
            
            <!-- TOPBAR (Header) -->
            <header class="bg-white shadow-sm h-20 flex items-center justify-between px-4 md:px-10 z-30 border-b">
                <div class="flex items-center">
                    <!-- Tombol Hamburger (Hanya muncul di Mobile) -->
                    <button onclick="toggleSidebar()" class="md:hidden p-2 text-indigo-950 focus:outline-none mr-2">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                    <div class="text-sm font-medium text-gray-400 uppercase tracking-widest italic hidden xs:block">
                        Employee
                    </div>
                </div>

                <!-- PROFILE DROPDOWN (Karyawan) -->
                <div class="relative inline-block text-left">
                    <button id="userDropdownBtn" class="flex items-center space-x-2 md:space-x-4 focus:outline-none group">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-black text-indigo-950 uppercase leading-none">{{ auth()->user()->nama }}</p>
                            <p class="text-[10px] text-indigo-500 font-bold uppercase tracking-tighter mt-1">Karyawan Aktif</p>
                        </div>
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border-2 border-indigo-50 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
                            <i class="fas fa-user text-lg md:text-xl"></i>
                        </div>
                    </button>

                    <!-- Menu Dropdown -->
                    <div id="userDropdownMenu" class="dropdown-menu absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                        <div class="p-4 border-b border-gray-50 bg-gray-50/50">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Akun Personal</p>
                        </div>
                        
                        <a href="{{ route('karyawan.profil') }}" class="flex items-center px-5 py-4 text-sm text-gray-700 hover:bg-indigo-50 transition border-b border-gray-50">
                            <i class="fas fa-user-cog w-5 text-indigo-500"></i>
                            <span class="ml-3 font-bold text-xs uppercase tracking-widest">Detail Profil</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-5 py-4 text-sm text-red-600 hover:bg-red-50 transition text-left">
                                <i class="fas fa-power-off w-5"></i>
                                <span class="ml-3 font-bold text-xs uppercase tracking-widest">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- ISI HALAMAN UTAMA -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-10 w-full">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Script Global -->
    <script>
        // Logic Sidebar Mobile
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Script Dropdown Profile
        const btn = document.getElementById('userDropdownBtn');
        const menu = document.getElementById('userDropdownMenu');

        if(btn && menu) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('show');
            });

            window.addEventListener('click', () => {
                if (menu.classList.contains('show')) {
                    menu.classList.remove('show');
                }
            });
        }
    </script>
</body>
</html>