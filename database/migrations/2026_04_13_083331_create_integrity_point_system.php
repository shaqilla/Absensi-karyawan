<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    // 1. Aturan Poin
    Schema::create('point_rules', function (Blueprint $table) {
        $table->id();
        $table->string('rule_name');
        $table->string('target_role');
        $table->enum('condition_operator', ['<', '>', 'BETWEEN']);
        $table->time('condition_value'); // Cth: 07:00:00
        $table->integer('point_modifier'); // Cth: 5 atau -3
        $table->timestamps();
    });

    // 2. Buku Besar Poin (Ledger)
    Schema::create('point_ledgers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->enum('transaction_type', ['EARN', 'SPEND', 'PENALTY']);
        $table->integer('amount');
        $table->integer('current_balance');
        $table->text('description');
        $table->timestamps();
    });

    // 3. Katalog Toko (Marketplace)
    Schema::create('flexibility_items', function (Blueprint $table) {
        $table->id();
        $table->string('item_name');
        $table->integer('point_cost');
        $table->integer('stock_limit')->nullable();
        $table->timestamps();
    });

    // 4. Token yang dimiliki User (Inventory)
    Schema::create('user_tokens', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('item_id')->constrained('flexibility_items');
        $table->enum('status', ['AVAILABLE', 'USED', 'EXPIRED'])->default('AVAILABLE');
        $table->integer('used_at_attendance_id')->nullable();
        $table->timestamps();
    });
}
};
