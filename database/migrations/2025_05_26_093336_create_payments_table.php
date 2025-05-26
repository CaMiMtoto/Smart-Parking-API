<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\ParkingSession::class)->constrained();
            $table->decimal('amount', 10, 0);
            $table->string('payment_method');
            $table->string('phone_number')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('tx_ref')->nullable();
            $table->string('flw_ref')->nullable();
            $table->string('status')->default('pending');
            $table->string('payment_gateway')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
