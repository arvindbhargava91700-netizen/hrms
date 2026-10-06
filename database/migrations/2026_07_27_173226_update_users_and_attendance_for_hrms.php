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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('department_id');
            $table->unsignedBigInteger('shift_id')->nullable()->after('branch_id');
            $table->date('joining_date')->nullable()->after('reporting_to');
            $table->date('resignation_date')->nullable()->after('joining_date');
            $table->date('termination_date')->nullable()->after('resignation_date');
            $table->string('employment_status')->default('active')->after('termination_date')->comment('active, resigned, terminated');

            $table->foreign('branch_id')->references('id')->on('hrms_branches')->onDelete('set null');
            $table->foreign('shift_id')->references('id')->on('work_shifts')->onDelete('set null');
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('employee_id');
            $table->unsignedBigInteger('shift_id')->nullable()->after('branch_id');

            $table->foreign('branch_id')->references('id')->on('hrms_branches')->onDelete('set null');
            $table->foreign('shift_id')->references('id')->on('work_shifts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['branch_id', 'shift_id', 'joining_date', 'resignation_date', 'termination_date', 'employment_status']);
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['branch_id', 'shift_id']);
        });
    }
};
