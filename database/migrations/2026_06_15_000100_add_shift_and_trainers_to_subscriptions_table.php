<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add columns if they don't already exist (from previous partial migration)
        if (!Schema::hasColumn('subscriptions', 'shift_id')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->unsignedBigInteger('shift_id')->nullable()->after('room_id');
                $table->json('trainer_ids')->nullable()->after('shift_id');
                $table->foreign('shift_id')->references('id')->on('listing_shifts')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['shift_id', 'trainer_ids']);
        });
    }
};
