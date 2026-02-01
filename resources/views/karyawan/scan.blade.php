@extends('layouts.karyawan')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[70vh]">
    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-lg border">
        <h1 class="text-xl font-bold text-center mb-2">Arahkan Kamera ke QR Code</h1>
        <p class="text-center text-gray-500 text-sm mb-6">Pastikan lokasi GPS Anda sudah aktif</p>
        
        <!-- Area Kamera -->
        <div id="reader" class="overflow-hidden rounded-2xl bg-black shadow-inner"></div>

        <div id="status-location" class="mt-6 text-center text-sm font-bold p-3 rounded-xl bg-gray-50 text-gray-400">
            <i class="fas fa-map-marker-alt mr-2"></i> Mencari Lokasi GPS...
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let userLat, userLng;
    const statusText = document.getElementById('status-location');

    // 1. Ambil GPS
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successGPS, errorGPS, { enableHighAccuracy: true });
    }

    function successGPS(position) {
        userLat = position.coords.latitude;
        userLng = position.coords.longitude;
        statusText.innerHTML = `<span class="text-green-600"><i class="fas fa-check-circle mr-2"></i> Lokasi Berhasil Dikunci</span>`;
        startScanner();
    }

    function errorGPS() {
        statusText.innerHTML = `<span class="text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i> Aktifkan GPS Anda!</span>`;
    }

    function startScanner() {
        const html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 280, height: 280 } },
            (decodedText) => {
                html5QrCode.stop();
                sendAttendance(decodedText);
            }
        );
    }

    function sendAttendance(token) {
        Swal.fire({ title: 'Menyimpan Absensi...', didOpen: () => Swal.showLoading() });

        fetch("{{ route('karyawan.absen.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ token: token, lat: userLat, lng: userLng })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Berhasil!', data.message, 'success').then(() => { window.location.href = "/karyawan/dashboard"; });
            } else {
                Swal.fire('Gagal!', data.message, 'error').then(() => { location.reload(); });
            }
        });
    }
</script>
@endsection