@extends('layouts.admin')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[70vh]">
    <div class="bg-white p-10 rounded-3xl shadow-xl text-center max-w-sm w-full border">
        <h1 class="text-2xl font-black text-gray-800 mb-6 uppercase">Scanner QR</h1>
        
        <div class="flex justify-center mb-6 p-4 bg-white border-2 border-dashed border-gray-200 rounded-2xl">
            <!-- WADAH QR -->
            <div id="qrcode"></div>
        </div>

        <div id="status" class="text-xs font-bold text-gray-400 uppercase tracking-widest">
            Menghubungkan ke server...
        </div>
    </div>
</div>

<!-- KITA PAKAI LIBRARY LAIN YANG LEBIH STABIL -->
<script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs@master/qrcode.min.js"></script>

<script>
    const qrContainer = document.getElementById("qrcode");
    const statusText = document.getElementById("status");

    function fetchQR() {
        console.log("Fetching...");
        statusText.innerText = "MENGAMBIL DATA...";

        fetch("{{ route('admin.qr.generate') }}")
            .then(res => {
                if(!res.ok) throw new Error("Server Error");
                return res.json();
            })
            .then(data => {
                if(data.token) {
                    qrContainer.innerHTML = ""; // Hapus yang lama
                    
                    // Membuat QR
                    new QRCode(qrContainer, {
                        text: data.token,
                        width: 200,
                        height: 200
                    });

                    statusText.innerText = "QR CODE AKTIF";
                    console.log("QR Generated!");
                }
            })
            .catch(err => {
                console.error(err);
                statusText.innerText = "GAGAL: CEK KONEKSI / DATABASE";
                statusText.classList.add("text-red-500");
            });
    }

    // Jalankan otomatis
    window.addEventListener('load', fetchQR);
    
    // Refresh tiap 30 detik
    setInterval(fetchQR, 30000);
</script>
@endsection