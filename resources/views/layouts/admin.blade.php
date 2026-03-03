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

        #sidebar {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-text {
            transition: opacity 0.2s;
            white-space: nowrap;
        }

        .sidebar-collapsed {
            width: 5rem !important;
        }

        .sidebar-collapsed .sidebar-text {
            display: none !important;
        }

        .sidebar-collapsed nav p {
            display: none !important;
        }

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

        <!-- OVERLAY (HP) -->
        <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity"></div>

        <!-- SIDEBAR -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-indigo-950 text-white flex flex-col shadow-2xl transform -translate-x-full md:translate-x-0 md:relative transition-transform duration-300 ease-in-out">
            <div class="sidebar-header flex items-center justify-between p-6 border-b border-indigo-900 h-20">
                <div class="sidebar-text flex items-center">
                    <span class="text-xl font-black tracking-widest uppercase">Zen<span class="text-indigo-400">clock</span></span>
                </div>
                <button onclick="toggleSidebar()" class="text-indigo-400 hover:text-white transition focus:outline-none p-2">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <nav class="flex-1 p-4 space-y-2 mt-4 overflow-y-auto overflow-x-hidden text-indigo-100">
                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-2 sidebar-text">Utama</p>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-chart-line w-6 text-center"></i> <span class="ml-3 font-bold text-sm uppercase sidebar-text">Dashboard</span>
                </a>

                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mt-6 mb-2 sidebar-text">Manajemen Data</p>
                <a href="{{ route('admin.karyawan.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.karyawan.*') ? 'bg-indigo-600 text-white' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-users w-6 text-center"></i> <span class="ml-3 font-bold text-sm uppercase sidebar-text">Data Karyawan</span>
                </a>
                <a href="{{ route('admin.shift.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.shift.*') ? 'bg-indigo-600 text-white' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-clock w-6 text-center"></i> <span class="ml-3 font-bold text-sm uppercase sidebar-text">Shift Kerja</span>
                </a>
                <a href="{{ route('admin.jadwal.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.jadwal.*') ? 'bg-indigo-600 text-white' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-calendar-alt w-6 text-center"></i> <span class="ml-3 font-bold text-sm uppercase sidebar-text">Jadwal Kerja</span>
                </a>

                <!-- LOKASI KANTOR SUDAH KEMBALI DI SINI -->
                <a href="{{ route('admin.lokasi.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.lokasi.*') ? 'bg-indigo-600 text-white' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-map-marked-alt w-6 text-center text-emerald-400"></i> <span class="ml-3 font-bold text-sm uppercase sidebar-text">Lokasi Kantor</span>
                </a>

                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mt-6 mb-2 sidebar-text">Operasional</p>
                <a href="{{ route('admin.pengajuan.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.pengajuan.*') ? 'bg-indigo-600 text-white' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-envelope-open-text w-6 text-center"></i> <span class="ml-3 font-bold text-sm uppercase sidebar-text">Persetujuan Izin</span>
                </a>
                <a href="{{ route('admin.laporan.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.laporan.*') ? 'bg-indigo-600 text-white' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-file-signature w-6 text-center"></i> <span class="ml-3 font-bold text-sm uppercase sidebar-text">Laporan Absensi</span>
                </a>
            </nav>
        </aside>

        <!-- AREA KONTEN -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 overflow-hidden">
            <header class="bg-white shadow-sm h-20 flex items-center justify-between px-10 z-30 border-b">
                <div class="text-sm font-black text-indigo-950 uppercase tracking-widest italic">Admin Portal</div>

                <div class="relative inline-block text-left">
                    <button id="adminDropdownBtn" class="flex items-center space-x-4 focus:outline-none">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-black text-indigo-950 uppercase">{{ auth()->user()->nama }}</p>
                            <p class="text-[10px] text-red-500 font-bold uppercase">Super Admin</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border-2 border-indigo-100">
                            <i class="fas fa-user-shield text-xl"></i>
                        </div>
                    </button>
                    <!-- Dropdown Content -->
                    <div id="adminDropdownMenu" class="dropdown-menu absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                        <div class="p-4 border-b border-gray-50 bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase">Account Settings</div>
                        <a href="{{ route('admin.profil') }}" class="flex items-center px-5 py-4 text-sm text-gray-700 hover:bg-indigo-50 transition border-b border-gray-50">
                            <i class="fas fa-user-cog w-5 text-indigo-500"></i> <span class="ml-3 font-bold text-xs uppercase">Setting Profil</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-5 py-4 text-sm text-red-600 hover:bg-red-50 transition text-left">
                                <i class="fas fa-power-off w-5"></i> <span class="ml-1 font-bold uppercase text-xs">Keluar Sistem</span>
                            </button>
                        </form>
                    </div>
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
            const overlay = document.getElementById('overlay');
            if (window.innerWidth >= 768) {
                sidebar.classList.toggle('sidebar-collapsed');
            } else {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }

        const btn = document.getElementById('adminDropdownBtn');
        const menu = document.getElementById('adminDropdownMenu');
        if (btn) {
            btn.onclick = (e) => {
                e.stopPropagation();
                menu.classList.toggle('show');
            };
            window.onclick = () => menu.classList.remove('show');
        }
    </script>
</body>

</html>