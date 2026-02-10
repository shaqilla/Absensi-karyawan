@extends('layouts.karyawan')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Scan Presensi</h1>
            <p class="text-gray-500 text-sm">Pastikan wajah dan QR Code terlihat jelas.</p>
        </div>
        <a href="{{ route('karyawan.dashboard') }}" class="bg-rose-50 text-rose-600 px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-rose-600 hover:text-white transition shadow-sm border border-rose-100 flex items-center">
            <i class="fas fa-times mr-2"></i> Batalkan Scan
        </a>
    </div>

    <div class="flex flex-col items-center justify-center min-h-[60vh]">
        <div class="bg-white p-6 rounded-[2.5rem] shadow-xl w-full max-w-lg border border-gray-100">
            <!-- Tempat Scanner -->
            <div id="reader" class="overflow-hidden rounded-3xl bg-slate-900 shadow-inner"></div>
            
            <!-- Indikator GPS & Radius -->
            <div id="status-location" class="mt-8 text-center p-5 rounded-2xl bg-gray-100 text-gray-500 font-black text-[10px] uppercase tracking-[0.2em] transition-all duration-500">
                <i class="fas fa-spinner fa-spin mr-2"></i> Sedang Mengunci Lokasi...
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>
    let userLat, userLng;
    const statusText = document.getElementById('status-location');
    let html5QrCode;

    // DATA KANTOR (Diambil dari database lewat Controller)
    const officeLat = {{ $lokasi->latitude }};
    const officeLng = {{ $lokasi->longitude }};
    const maxRadius = {{ $lokasi->radius }}; // dalam meter

    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(successGPS, errorGPS, { 
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        });
    } else {
        Swal.fire('Error', 'GPS tidak didukung', 'error');
    }

    // Fungsi Hitung Jarak (Haversine Formula)
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Radius bumi dalam meter
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c; // Hasil dalam meter
    }

    function successGPS(position) {
        userLat = position.coords.latitude;
        userLng = position.coords.longitude;

        // Hitung jarak user ke kantor
        const distance = calculateDistance(userLat, userLng, officeLat, officeLng);
        const distanceRounded = Math.round(distance);

        if (distance <= maxRadius) {
            // JIKA MASUK RADIUS
            statusText.className = "mt-8 text-center p-5 rounded-2xl bg-emerald-50 text-emerald-600 font-black text-[10px] uppercase tracking-[0.2em] border border-emerald-100";
            statusText.innerHTML = `<i class="fas fa-check-circle mr-2"></i> Area Kantor Terdeteksi (${distanceRounded}m)`;
            
            // Jalankan scanner jika belum jalan
            if (!html5QrCode) startScanner();
        } else {
            // JIKA DI LUAR RADIUS
            statusText.className = "mt-8 text-center p-5 rounded-2xl bg-rose-50 text-rose-600 font-black text-[10px] uppercase tracking-[0.2em] border border-rose-100";
            statusText.innerHTML = `<i class="fas fa-map-marker-alt mr-2"></i> Anda di luar radius (${distanceRounded}m dari kantor)`;
            
            // Matikan scanner jika user menjauh lagi keluar radius (opsional)
            if (html5QrCode) {
                html5QrCode.stop();
                html5QrCode = null;
                document.getElementById('reader').innerHTML = ""; // Bersihkan tampilan kamera
            }
        }
    }

    function errorGPS(error) {
        statusText.innerHTML = `<i class="fas fa-exclamation-triangle mr-2 text-amber-500"></i> GPS Lemah / Izin Ditolak`;
    }

    function startScanner() {
        html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 15, qrbox: { width: 280, height: 280 } };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                html5QrCode.stop().then(() => {
                    sendAttendance(decodedText);
                });
            }
        ).catch(err => console.error("Kamera error:", err));
    }

    function sendAttendance(token) {
        Swal.fire({ 
            title: 'Memproses...', 
            text: 'Mengirim data absen',
            allowOutsideClick: false, 
            didOpen: () => { Swal.showLoading() } 
        });

        fetch("{{ route('karyawan.absen.store') }}", {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ token: token, lat: userLat, lng: userLng })
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok) {
                Swal.fire('Berhasil!', data.message, 'success').then(() => { 
                    window.location.href = "{{ route('karyawan.dashboard') }}"; 
                });
            } else {
                Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error').then(() => {
                    window.location.reload();
                });
            }
        })
        .catch(err => {
            Swal.fire('Koneksi Error', 'Gagal menghubungi server.', 'error');
        });
    }
</script>
@endsection