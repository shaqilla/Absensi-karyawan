<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Karyawan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body, html { height: 100%; overflow: hidden; }
    </style>
</head>
<body class="bg-gray-100">

    <div class="flex h-screen w-full overflow-hidden">
        <!-- SIDEBAR (PERSIS ADMIN) -->
        <aside class="w-64 bg-indigo-900 text-white flex-shrink-0 flex flex-col shadow-2xl">
            <div class="p-6 text-2xl font-bold border-b border-indigo-800">
                Absensi QR
            </div>
            <nav class="flex-1 p-4 space-y-2 mt-4">
                <a href="{{ route('karyawan.dashboard') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('karyawan.dashboard') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-home w-6"></i> Dashboard
                </a>
                <a href="{{ route('karyawan.scan') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('karyawan.scan') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-camera w-6"></i> Scan Absensi
                </a>
                <a href="{{ route('karyawan.jadwal.index') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('karyawan.jadwal.index') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-calendar-alt w-6 text-center"></i> 
                    <span class="ml-3 font-bold">Jadwal Kerja Saya</span>
                </a>
                <a href="{{ route('karyawan.izin.create') }}" class="flex items-center p-3 rounded-xl transition {{ request()->routeIs('karyawan.izin.*') ? 'bg-indigo-700 shadow-lg' : 'hover:bg-indigo-800' }}">
                    <i class="fas fa-envelope-open-text w-6"></i> Pengajuan Izin
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

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-100">
            <!-- TOPBAR -->
            <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b">
                <div class="text-gray-700 font-medium ml-4 uppercase text-sm">
                    Karyawan: <span class="font-bold text-indigo-600">{{ auth()->user()->nama }}</span>
                </div>
                <div class="mr-4">
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-[10px] font-black uppercase">KARYAWAN</span>
                </div>
            </header>

            <!-- ISI KONTEN (SANGAT LEBAR) -->
            <main class="flex-1 overflow-y-auto p-8">
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>