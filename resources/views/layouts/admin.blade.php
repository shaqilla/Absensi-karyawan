<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Menghilangkan scrollbar pada body supaya tidak double scroll */
        body { overflow: hidden; }
    </style>
</head>
<body class="bg-gray-100 antialiased">

    <!-- Container Utama: HARUS w-full dan h-screen -->
    <div class="flex h-screen w-full bg-gray-100">
        
        <!-- SIDEBAR (Kiri) -->
        <aside class="w-64 bg-indigo-900 text-white flex-shrink-0 flex flex-col shadow-2xl">
            <div class="p-6 text-2xl font-bold border-b border-indigo-800">
                Absensi QR
            </div>
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-home w-6"></i> Dashboard
                </a>
                <a href="{{ route('admin.qr.view') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.qr.view') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-qrcode w-6"></i> Layar QR Scanner
                </a>
                <a href="{{ route('admin.karyawan.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.karyawan.*') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-users w-6"></i> User Role
                </a>
                <a href="{{ route('admin.pengajuan.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.pengajuan.index') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-envelope-open-text w-6"></i> Pengajuan Izin
                </a>
                <a href="{{ route('admin.laporan.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('admin.laporan.index') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-file-alt w-6"></i> Laporan Kehadiran
                </a>
            </nav>
            <div class="p-4 border-t border-indigo-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full p-3 rounded-xl hover:bg-red-600 transition">
                        <i class="fas fa-sign-out-alt w-6"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- AREA KONTEN (Kanan) -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Topbar (Tetap di atas) -->
            <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b">
                <div class="ml-4 text-gray-600 font-medium">
                    Selamat Datang, <span class="font-bold text-indigo-600 uppercase">{{ auth()->user()->nama }}</span>
                </div>
                <div class="mr-4">
                    <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-[10px] font-black uppercase">
                        {{ auth()->user()->role }}
                    </span>
                </div>
            </header>

            <!-- Isi Halaman (Scrollable) -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-8">
                <div class="w-full"> <!-- Pastikan kontainer ini w-full -->
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

</body>
</html>