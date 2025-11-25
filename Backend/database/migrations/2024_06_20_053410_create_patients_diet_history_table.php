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
        // Schema::create('patients_diet_history', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('doctors_order_id')
        //             ->references('id')
        //             ->on('doctors_order')
        //             ->constrained();
        //     $table->string('meal_time');
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients_diet_history');
    }
};
