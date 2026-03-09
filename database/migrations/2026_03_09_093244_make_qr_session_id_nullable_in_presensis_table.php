<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            // Kita ubah kolom qr_session_id agar boleh null (kosong)
            $table->foreignId('qr_session_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->foreignId('qr_session_id')->nullable(false)->change();
        });
    }
};
