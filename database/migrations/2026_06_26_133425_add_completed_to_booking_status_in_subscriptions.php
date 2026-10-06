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
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN booking_status ENUM('pending_otp', 'confirmed', 'active', 'cancelled', 'completed') DEFAULT 'pending_otp'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN booking_status ENUM('pending_otp', 'confirmed', 'active', 'cancelled') DEFAULT 'pending_otp'");
    }
};
