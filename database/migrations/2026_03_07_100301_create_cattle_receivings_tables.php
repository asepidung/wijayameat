<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table Header: CattleReceiving
        Schema::create('cattle_receivings', function (Blueprint $table) {
            $table->id();
            $table->string('receiving_number')->unique(); // Format GRC#...
            $table->foreignId('cattle_purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->date('receive_date');
            $table->string('doc_no')->nullable(); // Nomor dokumen pendukung
            $table->boolean('sv_ok')->default(false); // Surat Veteriner
            $table->boolean('skkh_ok')->default(false); // SK Kesehatan Hewan
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Table Detail: CattleReceivingItem (Data Per Ekor Sapi)
        Schema::create('cattle_receiving_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cattle_receiving_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cattle_category_id')->constrained(); // Link ke HEIFER, STEER, dll
            $table->string('eartag')->index(); // ID Unik Sapi
            $table->decimal('initial_weight', 10, 2); // Berat saat turun truk
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cattle_receiving_items');
        Schema::dropIfExists('cattle_receivings');
    }
};
