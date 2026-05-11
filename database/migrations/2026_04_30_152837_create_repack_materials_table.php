<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repack_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repack_id')->constrained('repacks')->cascadeOnDelete();
            $table->string('barcode', 30)->index();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('grade_id')->constrained('grades')->restrictOnDelete();
            $table->decimal('weight', 8, 2);
            $table->integer('qty_pcs')->default(0)->nullable();
            $table->date('production_date')->nullable();
            $table->integer('origin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repack_materials');
    }
};
