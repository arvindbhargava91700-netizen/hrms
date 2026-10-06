<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->uuid('room_id')->nullable()->after('package_id');
            $table->enum('occupancy_type', ['standard', 'single', 'full_room', 'per_bed'])->default('standard')->after('room_id');
            $table->integer('beds_booked')->default(1)->after('occupancy_type');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn(['room_id', 'occupancy_type', 'beds_booked']);
        });
    }
};
