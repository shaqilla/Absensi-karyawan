<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Karyawan
            $table->enum('jenis_pengajuan', ['cuti', 'sakit', 'izin', 'lembur'])->change();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('alasan');
            $table->string('lampiran')->nullable(); // Nama file foto/pdf
            $table->enum('status_approval', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Admin/Manager
            $table->text('catatan_admin')->nullable();
            $table->timestamps(); // Ini otomatis mencakup created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
