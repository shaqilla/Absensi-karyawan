<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Presensiar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            -webkit-tap-highlight-color: transparent;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="antialiased text-gray-800">

    <!-- Kontainer Utama -->
    <div class="min-h-screen flex flex-col mx-auto w-full max-w-md bg-gray-50 shadow-2xl relative">
        
        <!-- Area Konten -->
        <main class="flex-1 pb-20">
            @yield('content')
        </main>

        <!-- Navbar Bawah (Opsional, biar kayak aplikasi asli) -->
        <nav class="fixed bottom-0 max-w-md w-full bg-white border-t flex justify-around py-3 z-50">
            <a href="{{ route('karyawan.dashboard') }}" class="text-center {{ request()->routeIs('karyawan.dashboard') ? 'text-indigo-600' : 'text-gray-400' }}">
                <i class="fas fa-home text-xl"></i>
                <p class="text-[10px] font-bold">Home</p>
            </a>
            <a href="{{ route('karyawan.scan') }}" class="text-center text-gray-400">
                <div class="bg-indigo-600 text-white w-12 h-12 rounded-full flex items-center justify-center -mt-8 border-4 border-gray-50 shadow-lg">
                    <i class="fas fa-camera text-xl"></i>
                </div>
                <p class="text-[10px] font-bold mt-1">Scan</p>
            </a>
            <a href="{{ route('karyawan.izin.create') }}" class="text-center {{ request()->routeIs('karyawan.izin.*') ? 'text-indigo-600' : 'text-gray-400' }}">
                <i class="fas fa-envelope-open-text text-xl"></i>
                <p class="text-[10px] font-bold">Izin</p>
            </a>
        </nav>
    </div>

</body>
</html>