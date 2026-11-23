<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_reference', 32)->unique();
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('listing_id')->constrained('listings')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('original_unit_price', 10, 2);
            $table->decimal('total_discounted_price', 10, 2);
            $table->decimal('original_total_price', 10, 2);
            $table->dateTime('reserved_at');
            $table->dateTime('expires_at');
            $table->dateTime('collected_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->enum('status', ['pending', 'collected', 'cancelled', 'expired'])->default('pending');
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index(['buyer_id', 'status']);
            $table->index(['listing_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
