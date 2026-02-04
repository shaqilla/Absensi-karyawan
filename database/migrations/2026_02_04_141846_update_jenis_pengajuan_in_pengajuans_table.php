<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Tambahkan ini agar bisa pakai DB statement

return new class extends Migration
{
    /**
     * Jalankan Perubahan
     */
    public function up(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            // Kita gunakan raw SQL agar perubahan ENUM pasti berhasil di semua versi MySQL
            DB::statement("ALTER TABLE pengajuans MODIFY COLUMN jenis_pengajuan ENUM('cuti', 'sakit', 'izin', 'lembur') NOT NULL");
        });
    }

    /**
     * Batalkan Perubahan (Kembalikan ke awal)
     */
    public function down(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            DB::statement("ALTER TABLE pengajuans MODIFY COLUMN jenis_pengajuan ENUM('cuti', 'sakit', 'izin') NOT NULL");
        });
    }
};