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
        Schema::create('mst_residents', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 100);
            $table->string('id_card_photo');
            $table->enum('resident_status', ["contract","permanent"]);
            $table->string('phone_number', 13);
            $table->tinyInteger('is_married');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_residents');
    }
};
