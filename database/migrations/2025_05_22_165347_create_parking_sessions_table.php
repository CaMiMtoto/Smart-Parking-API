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
        Schema::create('parking_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number');
            $table->string('phone')->nullable();
            $table->timestamp('entry_time')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('exit_time')->nullable(); // for checkout
            $table->integer('duration_minutes')->nullable(); // calculated at checkout
            $table->decimal('amount', 10, 0)->nullable(); // total paid
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamps();
            $table->index(['plate_number', 'status']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_sessions');
    }
};
