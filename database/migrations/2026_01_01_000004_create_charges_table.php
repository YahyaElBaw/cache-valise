<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('charge_date')->index();
            $table->foreignId('batch_id')->constrained('batches')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->enum('category', ['fabric', 'sewing', 'printing', 'other'])->index();
            $table->string('label');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('unit')->default('forfait');
            $table->decimal('unit_price', 12, 3);
            $table->decimal('amount', 12, 3);
            $table->enum('status', ['active', 'cancelled'])->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};
