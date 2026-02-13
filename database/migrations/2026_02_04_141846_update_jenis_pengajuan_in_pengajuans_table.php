<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jalankan Perubahan
     */
    public function up(): void
    {
        // 1. Cek apakah kolom sudah ada di tabel 'pengajuans'
        if (Schema::hasColumn('pengajuans', 'jenis_pengajuan')) {
            // Jika SUDAH ADA, gunakan MODIFY untuk menambah opsi 'lembur'
            DB::statement("ALTER TABLE pengajuans MODIFY COLUMN jenis_pengajuan ENUM('cuti', 'sakit', 'izin', 'lembur') NOT NULL");
        } else {
            // Jika BELUM ADA, gunakan ADD untuk membuat kolom baru
            Schema::table('pengajuans', function (Blueprint $table) {
                $table->enum('jenis_pengajuan', ['cuti', 'sakit', 'izin', 'lembur'])->after('id'); 
                // .after('id') opsional, agar posisi kolom rapi setelah kolom ID
            });
        }
    }

    /**
     * Batalkan Perubahan (Kembalikan ke awal)
     */
    public function down(): void
    {
        if (Schema::hasColumn('pengajuans', 'jenis_pengajuan')) {
            // Kembalikan ke opsi semula tanpa 'lembur'
            DB::statement("ALTER TABLE pengajuans MODIFY COLUMN jenis_pengajuan ENUM('cuti', 'sakit', 'izin') NOT NULL");
        }
    }
};