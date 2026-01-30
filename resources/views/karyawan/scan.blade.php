@extends('layouts.presensi')

@section('content')
<div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-md">
    <h1 class="text-xl font-bold text-center mb-4">Scan Kehadiran</h1>
    
    <!-- Area Kamera -->
    <div id="reader" class="overflow-hidden rounded-xl border-2 border-dashed border-gray-300"></div>

    <div id="status-location" class="mt-4 text-center text-sm text-gray-600">
        Menunggu lokasi GPS...
    </div>
</div>

<!-- Library Scanner -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let userLat, userLng;
    const statusText = document.getElementById('status-location');

    // 1. Ambil Lokasi GPS
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successGPS, errorGPS, {
            enableHighAccuracy: true
        });
    } else {
        alert("GPS tidak didukung oleh browser ini.");
    }

    function successGPS(position) {
        userLat = position.coords.latitude;
        userLng = position.coords.longitude;
        statusText.innerHTML = `<span class="text-green-600 font-bold">● Lokasi Terdeteksi</span>`;
        
        // Mulai kamera hanya setelah GPS didapat
        startScanner();
    }

    function errorGPS() {
        statusText.innerHTML = `<span class="text-red-600 font-bold">● Gagal mendapatkan lokasi. Pastikan GPS aktif.</span>`;
    }

    function startScanner() {
        const html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        html5QrCode.start(
            { facingMode: "environment" }, // Pakai kamera belakang
            config,
            (decodedText) => {
                // Berhenti scan agar tidak mengirim data berkali-kali
                html5QrCode.stop();
                
                // Kirim data ke server via AJAX
                sendAttendance(decodedText);
            }
        );
    }

    function sendAttendance(token) {
        Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch("{{ route('karyawan.absen.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                token: token,
                lat: userLat,
                lng: userLng
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                    window.location.href = "/dashboard";
                });
            } else {
                Swal.fire('Gagal!', data.message, 'error').then(() => {
                    location.reload();
                });
            }
        })
        .catch(err => {
            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
        });
    }
</script>
@endsection