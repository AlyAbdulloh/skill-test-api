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
        Schema::disableForeignKeyConstraints();

        Schema::create('tr_payment_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('house_resident_id');
            $table->foreign('house_resident_id')->references('id')->on('tr_house_residents');
            $table->unsignedBigInteger('fee_type_id');
            $table->foreign('fee_type_id')->references('id')->on('mst_fee_types');
            $table->date('billing_month');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ["paid","unpaid"]);
            $table->date('paid_at')->nullable();
            $table->string('payment_group_id')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_payment_bills');
    }
};
