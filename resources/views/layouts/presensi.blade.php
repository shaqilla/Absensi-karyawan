<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zenclock</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { overflow: hidden; }
    </style>
</head>
<body class="bg-gray-100 antialiased text-gray-800">

    <div class="flex h-screen w-full overflow-hidden bg-gray-100">
        
        <!-- SIDEBAR (KIRI) - PERSIS ADMIN -->
        <aside class="w-64 bg-indigo-900 text-white flex-shrink-0 flex flex-col shadow-2xl">
            <div class="p-6 text-2xl font-bold border-b border-indigo-800">
                ZenClock
            </div>
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="{{ route('karyawan.dashboard') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('karyawan.dashboard') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-home w-6"></i> Dashboard
                </a>
                <a href="{{ route('karyawan.scan') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('karyawan.scan') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-camera w-6"></i> Scan Absensi
                </a>
                <a href="{{ route('karyawan.izin.create') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('karyawan.izin.*') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-envelope-open-text w-6"></i> Pengajuan Izin
                </a>
            </nav>
            
            <!-- Tombol Logout di Sidebar -->
            <div class="p-4 border-t border-indigo-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full p-3 rounded-xl hover:bg-red-600 transition">
                        <i class="fas fa-sign-out-alt w-6"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT (KANAN) -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="bg-white shadow-sm p-4 flex justify-between items-center z-10 border-b">
                <div class="text-gray-700 font-medium ml-4">
                    Selamat Datang, <span class="font-bold text-indigo-600 uppercase">{{ auth()->user()->nama }}</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-black uppercase">Role: {{ auth()->user()->role }}</span>
                </div>
            </header>

            <!-- Isi Halaman -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-8">
                <div class="w-full">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

</body>
</html> 