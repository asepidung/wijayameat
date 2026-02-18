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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            // Relasi
            $table->foreignId('segment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_group_id')->constrained()->cascadeOnDelete();

            // Data Identitas
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address');

            // Operasional & Dokumen
            $table->boolean('is_tukar_faktur')->default(false);
            $table->integer('term_of_payment')->default(0);
            $table->json('document_requirements')->nullable(); // Kita simpan array dokumen di sini
            $table->text('notes')->nullable(); // Catatan khusus pengiriman

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
