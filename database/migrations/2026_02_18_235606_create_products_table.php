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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Relasi ke Master Kategori (Prefix 1, 2, 3...)
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            // Relasi ke Master Satuan (Kg)
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();

            // Relasi ke Diri Sendiri (Self-Join)
            // Nullable karena kalau barang Utama (Induk), parent_id-nya kosong.
            $table->foreignId('parent_id')->nullable()->constrained('products')->nullOnDelete();

            $table->string('name');           // TENDERLOIN atau TENDERLOIN TS
            $table->string('code')->unique(); // Hasil rumus 100100 atau 100101
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
