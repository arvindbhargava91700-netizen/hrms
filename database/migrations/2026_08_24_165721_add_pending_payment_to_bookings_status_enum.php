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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending_payment', 'pending', 'pending_otp', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending_otp'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending_otp', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending_otp'");
    }
};
