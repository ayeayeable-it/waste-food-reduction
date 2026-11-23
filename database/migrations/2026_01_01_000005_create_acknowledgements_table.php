<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->unique()->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->boolean('acknowledged_product_info')->default(false);
            $table->boolean('acknowledged_expiry')->default(false);
            $table->boolean('acknowledged_allergens')->default(false);
            $table->boolean('acknowledged_storage')->default(false);
            $table->boolean('acknowledged_collection')->default(false);
            $table->boolean('disclaimer_accepted')->default(false);
            $table->dateTime('acknowledged_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acknowledgements');
    }
};
