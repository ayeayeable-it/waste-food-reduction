<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('food_name', 150);
            $table->text('description');
            $table->string('image_path', 255)->nullable();
            $table->decimal('original_price', 10, 2);
            $table->decimal('discounted_price', 10, 2);
            $table->unsignedInteger('available_quantity');
            $table->string('expiry_label', 150);
            $table->text('storage_instructions');
            $table->text('allergen_info');
            $table->dateTime('collection_deadline');
            $table->enum('status', ['active', 'closed', 'sold_out', 'expired'])->default('active');
            $table->timestamps();

            $table->index(['status', 'collection_deadline']);
            $table->index(['store_id', 'status']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
