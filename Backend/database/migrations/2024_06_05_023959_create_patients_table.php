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
        // Schema::create('doctors_order', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('hpercode');
        //     $table->string('prev_diet');
        //     $table->string('new_diet');
        //     $table->string('updated_by');
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors_order');
    }
};
