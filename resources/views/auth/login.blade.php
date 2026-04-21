<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | HR Integrity System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen flex items-center justify-center p-0 md:p-6 text-black">

    <!-- Container Utama: Responsive Grid -->
    <div class="w-full max-w-[1100px] min-h-screen md:min-h-[700px] grid grid-cols-1 lg:grid-cols-2 bg-white md:rounded-[3rem] shadow-2xl overflow-hidden border border-slate-100">
        
        <!-- SISI KIRI: BRANDING (Hanya tampil di Desktop/Layar Lebar) -->
        <div class="hidden lg:flex bg-[#1e1b4b] p-16 flex-col justify-between relative overflow-hidden">
            <!-- Dekorasi Cahaya -->
            <div class="absolute -top-20 -left-20 w-80 h-80 bg-indigo-500 rounded-full opacity-20 blur-3xl"></div>
            <div class="absolute bottom-20 -right-20 w-80 h-80 bg-blue-600 rounded-full opacity-20 blur-3xl"></div>
            
            <div class="relative z-10 text-white">
                <div class="w-16 h-16 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center mb-10 border border-white/20 shadow-xl">
                    <i class="fas fa-fingerprint text-3xl text-indigo-400"></i>
                </div>
                <h1 class="text-5xl font-black leading-tight tracking-tighter uppercase mb-6">
                    Sistem<br>Presensi<br>Karyawan.
                </h1>
                <p class="text-indigo-200 text-lg font-medium leading-relaxed max-w-sm">
                    Kelola kehadiran, pantau integritas, dan tingkatkan performa dalam satu platform terpadu.
                </p>
            </div>

            <!-- Card Info Kecil (Glassmorphism) -->
            <div class="relative z-10 bg-white/5 backdrop-blur-md p-6 rounded-[2rem] border border-white/10">
                <div class="flex items-center gap-4 text-white">
                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-shield-check"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-60">Enkripsi Data</p>
                        <p class="text-sm font-bold uppercase tracking-tighter">Secure Access Verified</p>
                    </div>
                </div>
            </div>
            
            <!-- Ornamen Rocket -->
            <i class="fas fa-rocket absolute -right-16 bottom-20 text-[25rem] text-white opacity-5 rotate-12"></i>
        </div>

        <!-- SISI KANAN: FORM LOGIN (Tampil di semua device) -->
        <div class="p-8 md:p-16 lg:p-20 flex flex-col justify-center bg-white">
            
            <!-- Logo Mobile (Hanya muncul di layar kecil) -->
            <div class="lg:hidden flex items-center gap-3 mb-10">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter">Integrity HR</h2>
            </div>

            <div class="mb-10 text-center md:text-left">
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 uppercase tracking-tighter">Login</h2>
                <div class="hidden md:block w-12 h-1.5 bg-indigo-600 mt-3 rounded-full"></div>
                <p class="text-slate-400 font-bold mt-2 text-sm md:text-base">Silakan masuk dengan akun Anda.</p>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-2xl">
                    <ul class="text-[11px] text-rose-600 font-black uppercase tracking-wider space-y-1">
                        @foreach ($errors->all() as $error)
                            <li><i class="fas fa-exclamation-triangle mr-1"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email/NIP -->
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Email / NIP</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                            <i class="fas fa-user-circle"></i>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                            class="w-full pl-12 pr-5 py-4 md:py-5 bg-slate-50 border border-slate-100 rounded-[1.5rem] outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all font-bold text-slate-700 text-sm shadow-sm"
                            placeholder="nama@email.com">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex justify-between items-center mb-3 ml-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Password</label>
                        <a href="{{ route('password.request') }}" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Lupa?</a>
                    </div>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                            <i class="fas fa-lock-keyhole"></i>
                        </span>
                        <input id="password" type="password" name="password" required
                            class="w-full pl-12 pr-5 py-4 md:py-5 bg-slate-50 border border-slate-100 rounded-[1.5rem] outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all font-bold text-slate-700 text-sm shadow-sm"
                            placeholder="••••••••••••">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between py-2">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" name="remember" class="w-5 h-5 rounded-lg border-slate-300 text-indigo-600 focus:ring-indigo-500 transition cursor-pointer">
                        <span class="ml-3 text-[11px] font-black text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition">Ingat Saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-slate-900 text-white py-4 md:py-5 rounded-[1.5rem] font-black text-xs uppercase tracking-[0.25em] shadow-2xl shadow-indigo-200 hover:bg-indigo-600 hover:shadow-indigo-500/40 transition-all duration-300 active:scale-95 flex items-center justify-center gap-3">
                        Masuk Sekarang <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
            </form>

            <!-- Copyright Mobile/Desktop -->
            <div class="mt-12 md:mt-16 text-center lg:text-left border-t border-slate-50 pt-8">
                <p class="text-slate-300 text-[9px] font-black uppercase tracking-[0.4em]">
                    &copy; {{ date('Y') }} Integrity HR &bull; Corporate Build
                </p>
            </div>

        </div>
    </div>

</body>
</html>