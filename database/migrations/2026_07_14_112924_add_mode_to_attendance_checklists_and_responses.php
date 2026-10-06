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
        Schema::table('attendance_checklists', function (Blueprint $table) {
            $table->string('mode')->default('both')->after('question'); // punch_in, punch_out, both
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->json('check_out_checklist_responses')->nullable()->after('checklist_responses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_checklists', function (Blueprint $table) {
            $table->dropColumn('mode');
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropColumn('check_out_checklist_responses');
        });
    }
};
