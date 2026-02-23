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
        Schema::create('logistic_items', function (Blueprint $table) {
            $table->id();
            // Relasi ke kategori yang kita bikin tadi
            $table->foreignId('logistic_category_id')->constrained()->cascadeOnDelete();

            $table->string('code')->unique();
            $table->string('name')->unique();
            $table->string('unit')->nullable(); // PCS, ROLL, etc.

            // Fitur khusus request lu
            $table->boolean('show_in_stock')->default(true);

            // SOP Anti-Delete
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistic_items');
    }
};
