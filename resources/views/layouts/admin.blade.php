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
        
        /* Smooth transition untuk sidebar mobile */
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }

        /* Scrollbar rapi untuk sidebar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #4f46e5; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-100 antialiased font-sans">

    <div class="flex h-screen w-screen overflow-hidden relative">
        
        <!-- OVERLAY (Muncul saat sidebar dibuka di HP) -->
        <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity"></div>

        <!-- SIDEBAR -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-indigo-950 text-white flex flex-col shadow-2xl transform -translate-x-full md:translate-x-0 md:relative transition-transform duration-300 ease-in-out">
            <div class="p-8 text-xl font-black border-b border-indigo-900 tracking-widest text-center relative uppercase">
                Zen<span class="text-indigo-400">clock</span>
                <!-- Close button Mobile -->
                <button onclick="toggleSidebar()" class="md:hidden absolute right-4 top-8 text-indigo-400">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <nav class="flex-1 p-4 space-y-2 mt-4 overflow-y-auto">
                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-2">Utama</p>
                
                <a href="{{ route('admin.dashboard') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-chart-line w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Dashboard</span>
                </a>

                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mt-6 mb-2">Manajemen User</p>

                <!-- DROPDOWN KELOLA USER -->
                <div>
                    <button onclick="toggleUserDropdown()" class="w-full flex items-center justify-between p-3 rounded-xl transition text-indigo-200 hover:bg-indigo-900 hover:text-white group">
                        <div class="flex items-center">
                            <i class="fas fa-users-cog w-6 text-center text-indigo-400"></i> 
                            <span class="ml-3 font-bold text-sm uppercase">Kelola User</span>
                        </div>
                        <i id="userArrow" class="fas fa-chevron-down text-[10px] transition-transform duration-300 {{ request()->routeIs('admin.karyawan.index') || request()->routeIs('admin.users.admin') ? 'rotate-180' : '' }}"></i>
                    </button>
                    
                    <div id="userDropdown" class="{{ request()->routeIs('admin.karyawan.index') || request()->routeIs('admin.users.admin') ? '' : 'hidden' }} pl-10 mt-2 space-y-1">
                        <a href="{{ route('admin.karyawan.index') }}" class="block p-2 text-[10px] font-black uppercase tracking-widest {{ request()->routeIs('admin.karyawan.index') ? 'text-white bg-indigo-800 rounded-lg' : 'text-indigo-400 hover:text-white' }}">
                            Data Karyawan
                        </a>
                        <a href="{{ route('admin.users.admin') }}" class="block p-2 text-[10px] font-black uppercase tracking-widest {{ request()->routeIs('admin.users.admin') ? 'text-white bg-indigo-800 rounded-lg' : 'text-indigo-400 hover:text-white' }}">
                            Data Admin
                        </a>
                    </div>
                </div>

                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mt-6 mb-2">Master Data</p>

                <a href="{{ route('admin.shift.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.shift.*') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-clock w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Shift Kerja</span>
                </a>

                <a href="{{ route('admin.jadwal.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.jadwal.*') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-calendar-alt w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Jadwal Kerja</span>
                </a>

                <a href="{{ route('admin.lokasi.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.lokasi.*') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-map-marked-alt w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Lokasi Kantor</span>
                </a>

                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mt-6 mb-2">Operasional</p>

                <a href="{{ route('admin.pengajuan.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.pengajuan.*') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-envelope-open-text w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Persetujuan Izin</span>
                </a>

                <a href="{{ route('admin.laporan.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.laporan.*') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-file-signature w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Laporan Absensi</span>
                </a>
            </nav>
        </aside>

        <!-- AREA KONTEN (Kanan) -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50">
            
            <!-- TOPBAR -->
            <header class="bg-white shadow-sm h-20 flex items-center justify-between px-4 md:px-10 z-30 border-b">
                <div class="flex items-center">
                    <!-- Hamburger button (Mobile) -->
                    <button onclick="toggleSidebar()" class="md:hidden p-2 text-indigo-950 focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                    <div class="text-sm font-black text-indigo-950 uppercase tracking-widest ml-2 md:ml-0">
                        Admin <span class="text-gray-400 font-medium lowercase italic">Dashboard</span>
                    </div>
                </div>

                <!-- PROFILE DROPDOWN -->
                <div class="relative inline-block text-left">
                    <button id="adminDropdownBtn" class="flex items-center space-x-2 md:space-x-4 focus:outline-none group">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-black text-indigo-950 uppercase leading-none">{{ auth()->user()->nama }}</p>
                            <p class="text-[10px] text-red-500 font-black uppercase tracking-tighter mt-1">Super Admin</p>
                        </div>
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border-2 border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
                            <i class="fas fa-user-shield text-lg md:text-xl"></i>
                        </div>
                    </button>

                    <div id="adminDropdownMenu" class="dropdown-menu absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                        <div class="p-4 border-b border-gray-50 bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase">
                            Admin Account
                        </div>
                        <a href="{{ route('admin.profil') }}" class="flex items-center px-5 py-4 text-sm text-gray-700 hover:bg-indigo-50 transition border-b border-gray-50">
                            <i class="fas fa-user-cog w-5 text-indigo-500"></i>
                            <span class="ml-3 font-bold text-xs uppercase">Setting Profil</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-5 py-4 text-sm text-red-600 hover:bg-red-50 transition text-left font-bold uppercase ">
                                <i class="fas fa-power-off w-5"></i>
                                <span class="ml-1 tracking-widest">Keluar Sistem</span>
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

    <!-- Scripts -->
    <script>
        // Logic Sidebar Mobile Toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleSidebar() {
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.replace('-translate-x-full', 'translate-x-0');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.replace('translate-x-0', '-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Logic Dropdown Kelola User
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            const arrow = document.getElementById('userArrow');
            dropdown.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }

        // Logic Profile Dropdown
        const btn = document.getElementById('adminDropdownBtn');
        const menu = document.getElementById('adminDropdownMenu');
        if(btn) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('show');
            });
        }
        window.addEventListener('click', () => {
            if (menu && menu.classList.contains('show')) menu.classList.remove('show');
        });
    </script>
</body>
</html>