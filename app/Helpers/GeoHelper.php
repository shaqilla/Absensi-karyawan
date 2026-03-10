<?php

namespace App\Helpers;

class GeoHelper {

    // static = fungsi ini bisa dipanggil tanpa perlu buat objek dulu
    // Contoh pemanggilan: GeoHelper::calculateDistance(...)
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2) {

        $earthRadius = 50;

        // Konversi koordinat dari derajat ke radian
        // Karena fungsi matematika PHP (sin, cos, dll) butuh nilai radian, bukan derajat
        // deg2rad() = degree to radian
        $latFrom = deg2rad($lat1); // Latitude titik 1 (posisi karyawan)
        $lonFrom = deg2rad($lon1); // Longitude titik 1 (posisi karyawan)
        $latTo   = deg2rad($lat2); // Latitude titik 2 (posisi kantor)
        $lonTo   = deg2rad($lon2); // Longitude titik 2 (posisi kantor)

        // Hitung selisih latitude dan longitude antara 2 titik
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        // RUMUS HAVERSINE
        // Rumus matematika untuk hitung jarak 2 titik di permukaan bola (bumi)
        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +          // Selisih latitude
            cos($latFrom) * cos($latTo) *          // Faktor koreksi lengkungan bumi
            pow(sin($lonDelta / 2), 2)             // Selisih longitude
        ));

        // Kalikan sudut (angle) dengan radius bumi → hasil = jarak dalam kilometer
        return $angle * $earthRadius;
    }
}