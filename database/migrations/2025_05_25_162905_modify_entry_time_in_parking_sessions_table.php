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
        Schema::table('parking_sessions', function (Blueprint $table) {
            DB::statement("ALTER TABLE parking_sessions MODIFY entry_time TIMESTAMP NOT NULL;");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_sessions', function (Blueprint $table) {
            //
        });
    }
};
