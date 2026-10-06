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
        Schema::table('hrms_branches', function (Blueprint $table) {
            $table->time('fixed_check_in_time')->nullable();
            $table->time('fixed_check_out_time')->nullable();
            $table->integer('late_grace_period')->nullable();
            $table->integer('min_present_mins')->nullable();
            $table->integer('min_half_day_mins')->nullable();
            $table->integer('auto_absent_mark_mins')->nullable();
            $table->json('week_off_days')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hrms_branches', function (Blueprint $table) {
            $table->dropColumn([
                'fixed_check_in_time',
                'fixed_check_out_time',
                'late_grace_period',
                'min_present_mins',
                'min_half_day_mins',
                'auto_absent_mark_mins',
                'week_off_days'
            ]);
        });
    }
};
