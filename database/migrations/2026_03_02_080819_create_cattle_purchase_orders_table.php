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
        Schema::create('cattle_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique(); // SWM/PC#26001

            /* Referensi ke tabel suppliers (plural) */
            $table->foreignId('supplier_id')->constrained('suppliers');

            $table->date('po_date');
            $table->integer('term_of_payment')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('DRAFT');
            $table->text('note')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cattle_purchase_orders');
    }
};
