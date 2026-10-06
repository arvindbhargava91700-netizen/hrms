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
        Schema::table('employee_salary_structures', function (Blueprint $table) {
            $table->enum('salary_type', ['base_plus_target', 'commission_only'])->default('base_plus_target')->after('employee_id');
            $table->decimal('monthly_target', 10, 2)->default(0)->after('salary_type');
            $table->decimal('commission_percent', 5, 2)->default(0)->after('monthly_target');
            $table->decimal('recovery_percent', 5, 2)->default(0)->after('commission_percent');
        });
    }

    public function down(): void
    {
        Schema::table('employee_salary_structures', function (Blueprint $table) {
            $table->dropColumn(['salary_type', 'monthly_target', 'commission_percent', 'recovery_percent']);
        });
    }
};
