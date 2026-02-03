<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi APP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body, html { height: 100%; margin: 0; overflow: hidden; }
        .dropdown-menu { display: none; }
        .dropdown-menu.show { display: block; }
    </style>
</head>
<body class="bg-gray-100 antialiased font-sans">

    <div class="flex h-screen w-screen overflow-hidden">
        
        <!-- SIDEBAR (Kiri) -->
        <aside class="w-72 bg-indigo-950 text-white flex-shrink-0 flex flex-col shadow-2xl z-20">
            <div class="p-8 text-xl font-black border-b border-indigo-900 tracking-widest text-center">
                Zen<span class="text-indigo-400">clock</span>
            </div>
            
            <nav class="flex-1 p-4 space-y-2 mt-4 overflow-y-auto">
                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-2">Main Menu</p>
                
                <a href="{{ route('admin.dashboard') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 shadow-lg' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-chart-line w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Dashboard</span>
                </a>

                <a href="{{ route('admin.qr.view') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.qr.view') ? 'bg-indigo-600 shadow-lg' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-qrcode w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Layar Scanner</span>
                </a>

                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mt-6 mb-2">Manajemen Data</p>

                <a href="{{ route('admin.karyawan.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.karyawan.*') ? 'bg-indigo-600 shadow-lg' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-users w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Data Karyawan</span>
                </a>

                <a href="{{ route('admin.shift.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.shift.*') ? 'bg-indigo-600 shadow-lg' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-clock w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Shift Kerja</span>
                </a>

                <a href="{{ route('admin.jadwal.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.jadwal.*') ? 'bg-indigo-600 shadow-lg' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-calendar-alt w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Jadwal Kerja</span>
                </a>

                <p class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mt-6 mb-2">Operasional</p>

                <a href="{{ route('admin.pengajuan.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.pengajuan.*') ? 'bg-indigo-600 shadow-lg' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-envelope-open-text w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Persetujuan Izin</span>
                </a>

                <a href="{{ route('admin.laporan.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.laporan.*') ? 'bg-indigo-600 shadow-lg' : 'hover:bg-indigo-900' }}">
                    <i class="fas fa-file-signature w-6 text-center"></i> 
                    <span class="ml-3 font-bold text-sm uppercase">Laporan Absensi</span>
                </a>
            </nav>
        </aside>

        <!-- AREA KONTEN (Kanan) -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50">
            
            <!-- TOPBAR (Header) -->
            <header class="bg-white shadow-sm h-20 flex items-center justify-between px-10 z-30 border-b">
                <div class="text-sm font-medium text-gray-400 uppercase tracking-widest italic">
                    Admin
                </div>

                <!-- PROFILE DROPDOWN (Admin) -->
                <div class="relative inline-block text-left">
                    <button id="adminDropdownBtn" class="flex items-center space-x-4 focus:outline-none group">
                        <div class="text-right mr-1">
                            <p class="text-xs font-black text-indigo-950 uppercase leading-none">{{ auth()->user()->nama }}</p>
                            <p class="text-[10px] text-red-500 font-bold uppercase tracking-tighter mt-1">Super Admin</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border-2 border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
                            <i class="fas fa-user-shield text-xl"></i>
                        </div>
                    </button>

                    <!-- Menu Dropdown -->
                    <div id="adminDropdownMenu" class="dropdown-menu absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                        <div class="p-4 border-b border-gray-50 bg-gray-50/50">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Administrator Account</p>
                        </div>
                        
                        <!-- Link Setting Profil Admin -->
                        <a href="{{ route('admin.profil') }}" class="flex items-center px-5 py-4 text-sm text-gray-700 hover:bg-indigo-50 transition">
                            <i class="fas fa-user-cog w-5 text-indigo-500"></i>
                            <span class="ml-3 font-bold text-xs uppercase tracking-widest">Setting Profil</span>
                        </a>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-5 py-4 text-sm text-red-600 hover:bg-red-50 transition text-left border-t border-gray-50">
                                <i class="fas fa-power-off w-5"></i>
                                <span class="ml-3 font-bold text-xs uppercase tracking-widest">Keluar Sistem</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- ISI HALAMAN UTAMA -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-10 w-full">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Script Dropdown -->
    <script>
        const btn = document.getElementById('adminDropdownBtn');
        const menu = document.getElementById('adminDropdownMenu');

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        window.addEventListener('click', () => {
            if (menu.classList.contains('show')) {
                menu.classList.remove('show');
            }
        });
    </script>
</body>
</html>