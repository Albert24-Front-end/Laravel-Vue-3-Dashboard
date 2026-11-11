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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'revenue' or 'expense'
            $table->string('category'); // e.g. ФОТ, Аренда офиса..., Выручка...
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->text('comment')->nullable();
            $table->timestamps();

            // Indexes for fast dashboard filtering
            $table->index(['date', 'type']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
