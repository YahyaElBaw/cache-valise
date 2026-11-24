<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->date('payment_date')->index();
            $table->enum('direction', ['in', 'out'])->index();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->restrictOnDelete();
            $table->foreignId('charge_id')->nullable()->constrained('charges')->restrictOnDelete();
            $table->decimal('amount', 12, 3);
            $table->enum('method', ['cash', 'transfer', 'cheque'])->index();
            $table->string('reference')->nullable();
            $table->enum('status', ['valid', 'cancelled'])->default('valid')->index();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
