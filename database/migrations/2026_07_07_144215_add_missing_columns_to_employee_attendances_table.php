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
        Schema::table('employee_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_attendances', 'check_in_lat')) {
                $table->string('check_in_lat')->nullable();
            }
            if (!Schema::hasColumn('employee_attendances', 'check_in_lng')) {
                $table->string('check_in_lng')->nullable();
            }
            if (!Schema::hasColumn('employee_attendances', 'check_in_photo')) {
                $table->string('check_in_photo')->nullable();
            }
            if (!Schema::hasColumn('employee_attendances', 'check_in_selfie')) {
                $table->string('check_in_selfie')->nullable();
            }
            if (!Schema::hasColumn('employee_attendances', 'check_out_lat')) {
                $table->string('check_out_lat')->nullable();
            }
            if (!Schema::hasColumn('employee_attendances', 'check_out_lng')) {
                $table->string('check_out_lng')->nullable();
            }
            if (!Schema::hasColumn('employee_attendances', 'check_out_photo')) {
                $table->string('check_out_photo')->nullable();
            }
            if (!Schema::hasColumn('employee_attendances', 'check_out_selfie')) {
                $table->string('check_out_selfie')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            $columns = [
                'check_in_lat', 'check_in_lng', 'check_in_photo', 'check_in_selfie',
                'check_out_lat', 'check_out_lng', 'check_out_photo', 'check_out_selfie'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('employee_attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
