<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->dateTime('moved_at')->index();
            $table->enum('type', [
                'opening',
                'top_up',
                'customer_payment',
                'charge_payment',
                'withdrawal',
                'bank_deposit',
                'correction',
                'reversal',
            ])->index();
            $table->enum('direction', ['in', 'out'])->index();
            $table->decimal('amount', 12, 3);
            $table->string('label');
            $table->foreignId('payment_id')->nullable()->unique()->constrained('payments')->restrictOnDelete();
            $table->foreignId('reverses_id')->nullable()->unique()->constrained('cash_movements')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
