@extends('layouts.karyawan')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Scan Presensi</h1>
            <p class="text-gray-500 text-sm">Pastikan wajah dan QR Code terlihat jelas.</p>
        </div>
        <!-- TOMBOL KEMBALI -->
        <a href="{{ route('karyawan.dashboard') }}" class="bg-rose-50 text-rose-600 px-6 py-3 rounded-2xl font-bold hover:bg-rose-600 hover:text-white transition flex items-center text-xs uppercase tracking-widest">
            <i class="fas fa-times mr-2"></i> Batalkan Scan
        </a>
    </div>

    <div class="flex flex-col items-center justify-center min-h-[60vh]">
        <div class="bg-white p-6 rounded-[2.5rem] shadow-xl w-full max-w-lg border border-gray-100">
            <div id="reader" class="overflow-hidden rounded-3xl bg-slate-900 shadow-inner"></div>
            
            <div id="status-location" class="mt-8 text-center p-4 rounded-2xl bg-indigo-50 text-indigo-600 font-black text-[10px] uppercase tracking-[0.2em]">
                <i class="fas fa-spinner fa-spin mr-2"></i> Mengunci Lokasi GPS...
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let userLat, userLng;
    const statusText = document.getElementById('status-location');

    // 1. Ambil GPS dengan batasan waktu (Timeout)
    if (navigator.geolocation) {
        // Kita beri waktu 10 detik untuk mencari GPS, jika gagal pakai akurasi rendah
        navigator.geolocation.getCurrentPosition(successGPS, errorGPS, { 
            enableHighAccuracy: true,
            timeout: 10000, // 10 detik
            maximumAge: 0
        });
    } else {
        alert("GPS tidak didukung di HP ini");
    }

    function successGPS(position) {
        userLat = position.coords.latitude;
        userLng = position.coords.longitude;
        statusText.innerHTML = `<i class="fas fa-check-circle mr-2 text-emerald-500"></i> <span class="text-emerald-600">Lokasi Terkunci</span>`;
        startScanner();
    }

    function errorGPS(error) {
        // Jika GPS lemot, kita coba paksa ambil lokasi meskipun kurang akurat
        console.warn("GPS High Accuracy Timeout, mencoba akurasi standar...");
        statusText.innerHTML = `<i class="fas fa-exclamation-triangle mr-2 text-amber-500"></i> <span class="text-amber-600">GPS Lemah, mencoba mengunci...</span>`;
        
        navigator.geolocation.getCurrentPosition(
            (pos) => { successGPS(pos); },
            (err) => { alert("Gagal mengunci lokasi. Pastikan GPS HP Aktif dan Anda tidak di dalam ruangan beton tebal."); },
            { enableHighAccuracy: false }
        );
    }

    function startScanner() {
        const html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 15, qrbox: { width: 280, height: 280 } },
            (decodedText) => {
                html5QrCode.stop();
                sendAttendance(decodedText);
            }
        ).catch(err => {
            alert("Kamera error: " + err);
        });
    }

    function sendAttendance(token) {
        // Munculkan Loading
        Swal.fire({ 
            title: 'Mengirim Data...', 
            text: 'Harap tunggu sebentar',
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
                // Tutup loading dan tampilkan error dari server
                Swal.fire('Gagal!', data.message || 'Terjadi kesalahan pada data.', 'error');
            }
        })
        .catch(err => {
            // Jika koneksi Ngrok terputus
            console.error(err);
            Swal.fire('Koneksi Error', 'Gagal menghubungi server.', 'error');
        });
    }
</script>
@endsection