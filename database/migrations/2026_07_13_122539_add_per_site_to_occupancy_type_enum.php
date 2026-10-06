<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE packages MODIFY occupancy_type ENUM('standard', 'single', 'full_room', 'per_bed', 'per_site') DEFAULT 'standard'");
        DB::statement("ALTER TABLE subscriptions MODIFY occupancy_type ENUM('standard', 'single', 'full_room', 'per_bed', 'per_site') DEFAULT 'standard'");
        DB::statement("ALTER TABLE bookings MODIFY occupancy_type ENUM('standard', 'single', 'full_room', 'per_bed', 'per_site') DEFAULT 'standard'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('occupancy_type_enum', function (Blueprint $table) {
            //
        });
    }
};
