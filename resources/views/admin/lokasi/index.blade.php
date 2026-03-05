@extends('layouts.admin')

@section('content')
<!-- 1. LEAFLET CSS (Harus di Atas) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    /* PAKSA TINGGI PETA AGAR TIDAK 0 */
    #map { 
        height: 350px !important; 
        width: 100% !important;
        border-radius: 1.5rem; 
        z-index: 1; 
        border: 2px solid #e2e8f0;
    }
    @media (min-width: 768px) {
        #map { height: 550px !important; border-radius: 2.5rem; }
    }
    /* Memperbaiki kontrol leaflet agar tidak tertutup sidebar */
    .leaflet-container { font-family: inherit; }
</style>

<div class="w-full pb-10">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 text-center md:text-left">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 uppercase tracking-tighter">Lokasi Kantor</h1>
            <p class="text-gray-500 text-xs italic font-medium">Klik pada peta untuk mengubah koordinat pusat kantor.</p>
        </div>
        <button onclick="getLocation()" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg flex items-center justify-center">
            <i class="fas fa-location-arrow mr-2"></i> Deteksi GPS Saya
        </button>
    </div>

    @if(session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-8 rounded-xl shadow-sm text-xs font-bold uppercase tracking-tight">
            {{ session('success') }}
        </div>
    @endif

    <!-- GRID UTAMA -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- MAP AREA (DI ATAS PADA MOBILE) -->
        <div class="order-1 lg:order-2 lg:col-span-2">
            <div class="bg-white rounded-3xl md:rounded-[2.5rem] shadow-sm border border-gray-100 p-2 md:p-4">
                <div id="map"></div> <!-- DIV PETA -->
                <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 px-2 mb-2">
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">
                        <i class="fas fa-mouse-pointer mr-1 text-indigo-400"></i> Klik/Geser Marker
                    </p>
                    <div class="text-[9px] font-black text-indigo-500 bg-indigo-50 px-3 py-1 rounded-lg uppercase">
                        Radius: <span id="display-radius">{{ $lokasi->radius ?? '50' }}</span>m
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM AREA -->
        <div class="order-2 lg:order-1 lg:col-span-1">
            <div class="bg-white rounded-3xl md:rounded-[2.5rem] shadow-sm border border-gray-100 p-6 md:p-8">
                <form action="{{ route('admin.lokasi.update') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Kantor</label>
                        <input type="text" name="nama_kantor" value="{{ $lokasi->nama_kantor ?? '' }}" class="w-full border-gray-200 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 text-sm" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Radius (Meter)</label>
                        <input type="number" id="input-radius" name="radius" value="{{ $lokasi->radius ?? '50' }}" class="w-full border-gray-200 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 border outline-none font-bold text-gray-700 text-sm" required>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" id="input-lat" name="latitude" value="{{ $lokasi->latitude ?? '-6.200000' }}" class="w-full bg-gray-50 border-gray-100 rounded-xl p-3 border text-[10px] font-mono font-bold text-indigo-600 text-center" readonly title="Latitude">
                        <input type="text" id="input-lng" name="longitude" value="{{ $lokasi->longitude ?? '106.816666' }}" class="w-full bg-gray-50 border-gray-100 rounded-xl p-3 border text-[10px] font-mono font-bold text-indigo-600 text-center" readonly title="Longitude">
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-3xl font-black hover:bg-black transition shadow-xl uppercase text-xs tracking-widest">
                        Simpan Konfigurasi
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- 2. LEAFLET JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let defaultLat = {{ $lokasi->latitude ?? -6.200000 }};
        let defaultLng = {{ $lokasi->longitude ?? 106.816666 }};
        let defaultRadius = {{ $lokasi->radius ?? 50 }};

        // 1. Inisialisasi Peta
        const map = L.map('map', {
            scrollWheelZoom: false,
            dragging: !L.Browser.mobile, // Memudahkan scroll di mobile
            tap: !L.Browser.mobile
        }).setView([defaultLat, defaultLng], 17);

        // 2. Tambah Layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // 3. Tambah Marker & Circle
        let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
        let circle = L.circle([defaultLat, defaultLng], {
            color: '#4f46e5',
            fillColor: '#4f46e5',
            fillOpacity: 0.2,
            radius: defaultRadius
        }).addTo(map);

        // Fungsi Update Input
        function updateInputs(lat, lng) {
            document.getElementById('input-lat').value = lat.toFixed(8);
            document.getElementById('input-lng').value = lng.toFixed(8);
        }

        // Event: Marker Digeser
        marker.on('dragend', function() {
            let pos = marker.getLatLng();
            circle.setLatLng(pos);
            updateInputs(pos.lat, pos.lng);
        });

        // Event: Klik Peta
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });

        // Event: Input Radius
        document.getElementById('input-radius').addEventListener('input', function(e) {
            let radius = e.target.value;
            circle.setRadius(radius);
            document.getElementById('display-radius').innerText = radius;
        });

        // Fix: Force Render Peta agar tidak abu-abu/kosong
        setTimeout(function() {
            map.invalidateSize();
        }, 500);
    });

    // Deteksi Lokasi Admin
    function getLocation() {
        if (navigator.geolocation) {
            Swal.fire({ title: 'Mencari Lokasi...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
            navigator.geolocation.getCurrentPosition(function(pos) {
                Swal.close();
                // Ini butuh variabel map, marker, dll yang bersifat global atau dipanggil ulang
                location.reload(); // Untuk admin paling simpel reload saja setelah koordinat didapat browser
            });
        }
    }
</script>
@endsection