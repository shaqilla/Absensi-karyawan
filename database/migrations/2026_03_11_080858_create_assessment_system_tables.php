<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel 1: Kategori Penilaian (dinamis, dikelola Admin)
        Schema::create('assessment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // Cth: "Disiplin", "Kerja Sama"
            $table->text('description')->nullable();        // Deskripsi indikator
            $table->string('type')->default('Employee');    // "Employee" atau "Student"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabel 2: Pertanyaan untuk setiap kategori (5 pertanyaan per kategori)
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('assessment_categories')->onDelete('cascade');
            $table->string('question');                      // Pertanyaan spesifik
            $table->text('description')->nullable();         // Penjelasan tambahan
            $table->unsignedTinyInteger('order')->default(0); // Urutan pertanyaan (1-5)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index untuk memudahkan pencarian
            $table->index(['category_id', 'order']);
        });

        // Tabel 3: Header Transaksi Penilaian
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('evaluatee_id')->constrained('users')->onDelete('cascade');
            $table->date('assessment_date');
            $table->string('period');                       // Cth: "Minggu 1 Jan 2024"
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->default('monthly');
            $table->text('general_notes')->nullable();
            $table->timestamps();

            // Index untuk query laporan
            $table->index(['evaluator_id', 'evaluatee_id', 'assessment_date']);
        });

        // Tabel 4: Detail Nilai Per Pertanyaan (BUKAN per kategori)
        Schema::create('assessment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('assessment_questions')->onDelete('cascade');
            $table->unsignedTinyInteger('score');           // Nilai 1-5 (bintang)
            $table->timestamps();

            // Unique constraint: 1 assessment hanya boleh punya 1 nilai per pertanyaan
            $table->unique(['assessment_id', 'question_id']);

            // Index untuk analisis
            $table->index(['question_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_details');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessment_categories');
    }
};
