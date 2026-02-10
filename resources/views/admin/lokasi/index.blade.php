@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 400px; border-radius: 1.5rem; z-index: 10; }
</style>

<div class="w-full max-w-6xl">
    <div class="mb-10">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Konfigurasi Lokasi Kantor</h1>
        <p class="text-gray-500 text-sm italic">Klik pada peta atau geser marker untuk menentukan titik koordinat.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-2xl shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Input -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8">
                <form action="{{ route('admin.lokasi.update') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Gedung/Kantor</label>
                        <input type="text" name="nama_kantor" value="{{ $lokasi->nama_kantor ?? '' }}" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Radius Aman (Meter)</label>
                        <input type="number" id="input-radius" name="radius" value="{{ $lokasi->radius ?? '50' }}" class="w-full border-gray-200 rounded-xl p-4 border outline-none focus:ring-2 focus:ring-indigo-500 font-bold" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Latitude</label>
                            <input type="text" id="input-lat" name="latitude" value="{{ $lokasi->latitude ?? '-6.200000' }}" class="w-full border-gray-200 rounded-xl p-3 border text-xs font-mono" readonly>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Longitude</label>
                            <input type="text" id="input-lng" name="longitude" value="{{ $lokasi->longitude ?? '106.816666' }}" class="w-full border-gray-200 rounded-xl p-3 border text-xs font-mono" readonly>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg uppercase text-sm tracking-widest mt-4">
                        Simpan Lokasi
                    </button>
                </form>
            </div>
        </div>

        <!-- Tampilan Peta -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-4">
                <div id="map"></div>
                <div class="mt-4 flex items-center justify-between px-4">
                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-tight italic">
                        <i class="fas fa-mouse-pointer mr-2"></i> Klik di mana saja untuk memindahkan titik kantor
                    </div>
                    <button onclick="getLocation()" class="text-indigo-600 text-[10px] font-black uppercase tracking-widest hover:underline">
                        <i class="fas fa-location-arrow mr-1"></i> Deteksi Lokasi Saya
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script Peta -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Ambil koordinat awal dari database atau default
    let defaultLat = {{ $lokasi->latitude ?? -6.200000 }};
    let defaultLng = {{ $lokasi->longitude ?? 106.816666 }};
    let defaultRadius = {{ $lokasi->radius ?? 50 }};

    // 1. Inisialisasi Peta
    const map = L.map('map').setView([defaultLat, defaultLng], 17);

    // 2. Tambahkan Layer Peta (Google Maps Style)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // 3. Tambahkan Marker yang bisa digeser
    let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    // 4. Tambahkan Lingkaran Radius
    let circle = L.circle([defaultLat, defaultLng], {
        color: '#4f46e5',
        fillColor: '#4f46e5',
        fillOpacity: 0.2,
        radius: defaultRadius
    }).addTo(map);

    // Fungsi update input form
    function updateInputs(lat, lng) {
        document.getElementById('input-lat').value = lat.toFixed(8);
        document.getElementById('input-lng').value = lng.toFixed(8);
    }

    // Event saat marker digeser
    marker.on('dragend', function (e) {
        let position = marker.getLatLng();
        circle.setLatLng(position);
        updateInputs(position.lat, position.lng);
    });

    // Event saat peta diklik
    map.on('click', function (e) {
        let lat = e.latlng.lat;
        let lng = e.latlng.lng;
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
        updateInputs(lat, lng);
    });

    // Event saat input radius diubah
    document.getElementById('input-radius').addEventListener('input', function (e) {
        let radius = e.target.value;
        circle.setRadius(radius);
    });

    // Fungsi deteksi lokasi saat ini (GPS Admin)
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                let lat = position.coords.latitude;
                let lng = position.coords.longitude;
                map.setView([lat, lng], 17);
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                updateInputs(lat, lng);
            });
        }
    }
</script>
@endsection