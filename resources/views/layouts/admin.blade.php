<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zenclock</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        .dropdown-menu {
            display: none;
        }

        .dropdown-menu.show {
            display: block;
        }

        /* Animasi Sidebar */
        #sidebar {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s ease-in-out;
        }

        .sidebar-text {
            transition: opacity 0.2s;
            white-space: nowrap;
        }

        /* CSS Saat Sidebar Mengecil (Desktop) */
        .sidebar-collapsed {
            width: 5rem !important;
        }

        .sidebar-collapsed .sidebar-text {
            display: none !important;
        }

        .sidebar-collapsed .sidebar-header {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .sidebar-collapsed .menu-item {
            justify-content: center !important;
        }

        .sidebar-collapsed nav p {
            display: none !important;
        }

        /* Scrollbar rapi */
        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #4f46e5;
            border-radius: 10px;
        }
    </style>
</head>

<body class="bg-gray-100 antialiased font-sans">

    <div class="flex h-screen w-screen overflow-hidden relative">

        <!-- OVERLAY (Hanya di Mobile) -->
        <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-indigo-950/60 backdrop-blur-sm z-40 hidden md:hidden"></div>

        <!-- SIDEBAR (Hamburger ada di sini!) -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-indigo-950 text-white flex flex-col shadow-2xl transform -translate-x-full md:translate-x-0 md:relative h-full">

            <!-- HEADER SIDEBAR: Logo & Hamburger -->
            <div class="sidebar-header flex items-center justify-between p-6 border-b border-indigo-900 h-20">
                <div class="sidebar-text flex items-center">
                    <span class="text-xl font-black tracking-widest uppercase">Zen<span class="text-indigo-400">clock</span></span>
                </div>
                <!-- TOMBOL HAMBURGER (DI DALAM SIDEBAR) -->
                <button onclick="toggleSidebar()" class="text-indigo-400 hover:text-white transition focus:outline-none p-2 rounded-lg hover:bg-indigo-900">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <nav class="flex-1 p-4 space-y-2 mt-4 overflow-y-auto no-scrollbar">
                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2 sidebar-text">Utama</p>
                <a href="{{ route('admin.dashboard') }}" class="menu-item flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-chart-line w-6 text-center"></i> <span class="ml-3 font-bold text-sm sidebar-text">DASHBOARD</span>
                </a>

                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-widest mt-6 mb-2 sidebar-text">Manajemen</p>
                <a href="{{ route('admin.karyawan.index') }}" class="menu-item flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.karyawan.*') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-users w-6"></i> <span class="ml-3 font-bold text-sm sidebar-text">DATA KARYAWAN</span>
                </a>
                <a href="{{ route('admin.shift.index') }}" class="menu-item flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.shift.*') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-clock w-6"></i> <span class="ml-3 font-bold text-sm sidebar-text">SHIFT KERJA</span>
                </a>
                <a href="{{ route('admin.jadwal.index') }}" class="menu-item flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.jadwal.*') ? 'bg-indigo-600 shadow-lg text-white' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}">
                    <i class="fas fa-calendar-alt w-6"></i> <span class="ml-3 font-bold text-sm sidebar-text">JADWAL KERJA</span>
                </a>
                <a href="{{ route('admin.lokasi.index') }}" class="flex items-center p-3 rounded-xl {{ request()->routeIs('admin.lokasi.*') ? 'bg-indigo-600' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-map-marked-alt w-6 text-indigo-400"></i> <span class="ml-3 font-bold text-sm sidebar-text uppercase">Lokasi Kantor</span>
                </a>

                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-widest mt-6 mb-2 sidebar-text">Operasional</p>
                <a href="{{ route('admin.pengajuan.index') }}" class="flex items-center p-3 rounded-xl {{ request()->routeIs('admin.pengajuan.*') ? 'bg-indigo-600' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-envelope-open-text w-6"></i> <span class="ml-3 font-bold text-sm sidebar-text uppercase">Persetujuan Izin</span>
                </a>
                <a href="{{ route('admin.laporan.index') }}" class="flex items-center p-3 rounded-xl {{ request()->routeIs('admin.laporan.*') ? 'bg-indigo-600' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-file-signature w-6"></i> <span class="ml-3 font-bold text-sm sidebar-text uppercase">Laporan Absensi</span>
                </a>
                <a href="{{ route('admin.presensi.manual') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.presensi.manual') ? 'bg-indigo-600' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-edit w-6 text-center text-amber-400"></i>
                    <span class="ml-3 font-bold text-sm uppercase">Absensi Manual</span>
                </a>
            </nav>
        </aside>

        <!-- AREA KONTEN -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 overflow-hidden">
            <!-- TOPBAR (Bersih dari Hamburger!) -->
            <header class="bg-white shadow-sm h-20 flex items-center justify-between px-6 md:px-10 z-30 border-b">
                <div class="text-sm font-bold text-indigo-950 uppercase tracking-widest hidden sm:block">
                    Halo, <span class="text-indigo-600 font-black">{{ auth()->user()->nama }}</span>
                </div>
                <!-- Tombol Menu Mobile (Hanya muncul jika sidebar tersembunyi di HP) -->
                <button onclick="toggleSidebar()" class="md:hidden p-3 text-indigo-950 bg-indigo-50 rounded-2xl">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- PROFILE DROPDOWN -->
                <div class="relative inline-block text-left" id="profileArea">
                    <button id="adminDropdownBtn" class="flex items-center space-x-2 md:space-x-4 focus:outline-none group">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-black text-indigo-950 uppercase leading-none">{{ auth()->user()->nama }}</p>
                            <p class="text-[9px] text-red-500 font-bold uppercase mt-1 italic">Super Admin</p>
                        </div>
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border-2 border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                            <i class="fas fa-user-shield text-lg"></i>
                        </div>
                    </button>

                    <!-- Menu Dropdown (DIPERBAIKI) -->
                    <div id="adminDropdownMenu" class="hidden absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-[999]">
                        <div class="p-4 border-b border-gray-50 bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase">Admin Account</div>

                        <a href="{{ route('admin.profil') }}" class="flex items-center px-5 py-4 text-sm text-gray-700 hover:bg-indigo-50 transition border-b border-gray-50">
                            <i class="fas fa-user-cog w-5 text-indigo-500 text-center"></i>
                            <span class="ml-3 font-bold text-xs uppercase">Setting Profil</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-5 py-4 text-sm text-red-600 hover:bg-red-50 transition text-left">
                                <i class="fas fa-power-off w-5 text-center"></i>
                                <span class="ml-3 font-bold text-xs uppercase">Keluar Sistem</span>
                            </button>
                        </form>
                    </div>
            </header>

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
                // DESKTOP: Perkecil/Perbesar Sidebar
                sidebar.classList.toggle('sidebar-collapsed');
            } else {
                // MOBILE: Geser Sidebar Masuk/Keluar
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }

        // Profile Dropdown
        const btn = document.getElementById('adminDropdownBtn');
        const menu = document.getElementById('adminDropdownMenu');
        if (btn) {
            btn.onclick = (e) => {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            };
            window.onclick = () => menu.classList.add('hidden');
        }
    </script>
</body>

</html>