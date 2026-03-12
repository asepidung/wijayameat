<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boning_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boning_id')->constrained('bonings')->cascadeOnDelete();

            // Relasi ke tabel products
            $table->foreignId('product_id')->constrained('products');

            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->string('condition', 50)->default('CHILL'); // CHILL, FROZEN, REJECT

            $table->decimal('weight', 10, 2);
            $table->integer('qty_pcs')->default(0);
            $table->decimal('ph_level', 3, 1)->nullable();

            $table->date('pack_date');
            $table->date('exp_date')->nullable();
            $table->string('barcode', 50)->unique();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boning_items');
    }
};
