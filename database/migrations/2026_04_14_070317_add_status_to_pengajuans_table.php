<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            // Kita tambahkan kolom status setelah kolom jenis_pengajuan
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending')->after('jenis_pengajuan');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
