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
        Schema::table('job_posts', function (Blueprint $table) {
            $table->string('work_location_type')->nullable();
            $table->string('office_address')->nullable();
            $table->string('working_area')->nullable();
            $table->decimal('average_incentive', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn([
                'work_location_type',
                'office_address',
                'working_area',
                'average_incentive',
            ]);
        });
    }
};
