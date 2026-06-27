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
        Schema::create('mst_houses', function (Blueprint $table) {
            $table->id();
            $table->string('house_number', 20);
            $table->text('address');
            $table->enum('occupancy_status', ["occupied","unoccupied"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_houses');
    }
};
