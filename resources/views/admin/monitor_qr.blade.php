<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zenclock</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700&display=swap" rel="stylesheet">

    <style>
        body { 
            background: #09090b; 
            font-family: 'Space Grotesk', sans-serif;
            color: white;
            height: 100vh;
            overflow: hidden;
        }

        /* Efek Lampu Latar Belakang */
        .glow-bg {
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(79, 70, 229, 0.2);
            filter: blur(120px);
            border-radius: 50%;
            z-index: -1;
            transition: all 1s ease;
        }

        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Bingkai Viewfinder QR */
        .qr-wrapper {
            position: relative;
            padding: 20px;
            background: white;
            border-radius: 24px;
        }

        .viewfinder {
            position: absolute;
            top: -10px; left: -10px; right: -10px; bottom: -10px;
            border: 2px solid transparent;
            pointer-events: none;
            transition: all 0.5s ease;
        }

        /* Pojok-pojok bingkai */
        .corner {
            position: absolute;
            width: 30px;
            height: 30px;
            border-color: inherit;
            border-style: solid;
        }
        .tl { top: 0; left: 0; border-width: 4px 0 0 4px; border-top-left-radius: 15px; }
        .tr { top: 0; right: 0; border-width: 4px 4px 0 0; border-top-right-radius: 15px; }
        .bl { bottom: 0; left: 0; border-width: 0 0 4px 4px; border-bottom-left-radius: 15px; }
        .br { bottom: 0; right: 0; border-width: 0 4px 4px 0; border-bottom-right-radius: 15px; }

        .mode-masuk .viewfinder { border-color: #10b981; filter: drop-shadow(0 0 10px #10b981); }
        .mode-keluar .viewfinder { border-color: #f43f5e; filter: drop-shadow(0 0 10px #f43f5e); }

        .btn-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .active-masuk { background: #10b981 !important; color: white !important; box-shadow: 0 0 20px rgba(16, 185, 129, 0.4); }
        .active-keluar { background: #f43f5e !important; color: white !important; box-shadow: 0 0 20px rgba(244, 63, 94, 0.4); }
    </style>
</head>
<body class="flex items-center justify-center">

    <!-- Efek Cahaya Bergerak -->
    <div id="glow1" class="glow-bg" style="top: -10%; left: -10%;"></div>
    <div id="glow2" class="glow-bg" style="bottom: -10%; right: -10%;"></div>

    <div class="flex flex-col md:flex-row items-center gap-12 z-10">
        
        <!-- Sisi Kiri: Jam & Info -->
        <div class="text-center md:text-left space-y-4">
            <div>
                <h1 class="text-4xl font-light tracking-tighter text-white">
                    ZEN<span class="font-bold text-indigo-500">CLOCK</span>
                </h1>
                <p class="text-slate-500 tracking-widest text-xs uppercase font-medium">Attendance System Monitor</p>
            </div>
            
            <div class="py-6">
                <div id="clock" class="text-6xl font-bold tracking-tighter">00:00:00</div>
                <div id="date" class="text-indigo-400 font-medium text-sm mt-1 uppercase tracking-widest">Senin, 01 Januari 2024</div>
            </div>

            <!-- Tombol Navigasi -->
            <div class="flex flex-col gap-3 w-64">
                <button id="btn-masuk" onclick="changeMode('masuk')" class="btn-glass px-6 py-4 rounded-2xl text-left flex items-center justify-between group hover:bg-white/10">
                    <span class="font-bold tracking-wide">ABSEN MASUK</span>
                    <i class="fas fa-arrow-right text-xs opacity-0 group-hover:opacity-100 transition-all"></i>
                </button>
                <button id="btn-keluar" onclick="changeMode('keluar')" class="btn-glass px-6 py-4 rounded-2xl text-left flex items-center justify-between group hover:bg-white/10">
                    <span class="font-bold tracking-wide">ABSEN KELUAR</span>
                    <i class="fas fa-arrow-right text-xs opacity-0 group-hover:opacity-100 transition-all"></i>
                </button>
            </div>
        </div>

        <!-- Sisi Kanan: QR Display -->
        <div id="main-container" class="relative mode-masuk">
            <!-- Frame Bingkai Luar -->
            <div class="glass p-12 rounded-[40px] relative overflow-hidden">
                
                <div class="qr-wrapper shadow-2xl">
                    <div id="qrcode"></div>
                    <!-- Viewfinder corners -->
                    <div class="viewfinder">
                        <div class="corner tl"></div>
                        <div class="corner tr"></div>
                        <div class="corner bl"></div>
                        <div class="corner br"></div>
                    </div>
                </div>

                <div class="mt-10 text-center space-y-2">
                    <div id="status-label" class="inline-block px-4 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-[10px] font-bold tracking-widest uppercase border border-emerald-500/20">
                        System Online
                    </div>
                    <h2 id="status-text" class="text-xl font-bold tracking-tight">SIAP SCAN MASUK</h2>
                </div>
            </div>

            <!-- Animasi Garis Scan -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-indigo-500 to-transparent opacity-20 animate-bounce mt-20"></div>
        </div>
    </div>

    <script>
        let currentMode = 'masuk';
        const qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "INITIAL",
            width: 280,
            height: 280,
            colorDark : "#09090b",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        function changeMode(mode) {
            currentMode = mode;
            const container = document.getElementById('main-container');
            const btnMasuk = document.getElementById('btn-masuk');
            const btnKeluar = document.getElementById('btn-keluar');
            const statusText = document.getElementById('status-text');
            const statusLabel = document.getElementById('status-label');
            const glow1 = document.getElementById('glow1');

            if (mode === 'masuk') {
                container.className = "relative mode-masuk";
                btnMasuk.className = "btn-glass px-6 py-4 rounded-2xl text-left flex items-center justify-between active-masuk";
                btnKeluar.className = "btn-glass px-6 py-4 rounded-2xl text-left flex items-center justify-between group hover:bg-white/10";
                statusText.innerText = "SIAP SCAN MASUK";
                statusLabel.className = "inline-block px-4 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-[10px] font-bold tracking-widest uppercase border border-emerald-500/20";
                glow1.style.background = "rgba(16, 185, 129, 0.2)";
            } else {
                container.className = "relative mode-keluar";
                btnKeluar.className = "btn-glass px-6 py-4 rounded-2xl text-left flex items-center justify-between active-keluar";
                btnMasuk.className = "btn-glass px-6 py-4 rounded-2xl text-left flex items-center justify-between group hover:bg-white/10";
                statusText.innerText = "SIAP SCAN KELUAR";
                statusLabel.className = "inline-block px-4 py-1 rounded-full bg-rose-500/10 text-rose-400 text-[10px] font-bold tracking-widest uppercase border border-rose-500/20";
                glow1.style.background = "rgba(244, 63, 94, 0.2)";
            }
            updateQR();
        }

        function updateQR() {
        fetch("{{ route('admin.qr.generate') }}?mode=" + currentMode)
            .then(res => res.json())
            .then(data => {
                qrcode.clear();
                qrcode.makeCode(data.token); 
            }).catch(e => console.log("Refresh Error"));
    }

        function updateTime() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', { hour12: false });
            
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('date').innerText = now.toLocaleDateString('id-ID', options);
        }

        changeMode('masuk');
        setInterval(updateTime, 1000);
        setInterval(updateQR, 15000);
        updateTime();
    </script>
</body>
</html>