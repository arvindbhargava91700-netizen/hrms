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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_number')->unique()->nullable()->after('id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('subscription_number')->unique()->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('booking_number');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('subscription_number');
        });
    }
};
