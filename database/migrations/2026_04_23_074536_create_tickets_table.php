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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users'); // Karyawan
            $table->foreignId('operator_id')->nullable()->constrained('users'); // Operator
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['Low', 'Mid', 'High'])->default('Low');
            $table->enum('status', ['open', 'in-progress', 'closed'])->default('open');
            $table->timestamp('first_response_at')->nullable(); // Buat hitung Response Time
            $table->timestamp('resolved_at')->nullable(); // Buat hitung Resolution Time
            $table->timestamps();

            // FULL TEXT SEARCH INDEX (PENTING BUAT FITUR 2)
            $table->fullText(['subject', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
