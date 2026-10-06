<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            // Shift reference (for shift-based categories like Gym)
            $table->unsignedBigInteger('shift_id')->nullable()->after('listing_id');
            $table->foreign('shift_id')->references('id')->on('listing_shifts')->nullOnDelete();

            // GPS coordinates at punch-in
            $table->decimal('lat', 10, 8)->nullable()->after('shift_id');
            $table->decimal('lng', 11, 8)->nullable()->after('lat');

            // GPS coordinates at punch-out
            $table->decimal('punch_out_lat', 10, 8)->nullable()->after('punch_out_at');
            $table->decimal('punch_out_lng', 11, 8)->nullable()->after('punch_out_lat');

            // Late tracking
            $table->boolean('is_late')->default(false)->after('punch_out_lng');
            $table->integer('late_minutes')->nullable()->after('is_late');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn([
                'shift_id',
                'lat', 'lng',
                'punch_out_lat', 'punch_out_lng',
                'is_late', 'late_minutes',
            ]);
        });
    }
};
