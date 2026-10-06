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
        Schema::table('employee_payrolls', function (Blueprint $table) {
            $table->json('allowances_breakdown')->nullable()->after('basic_salary');
            $table->json('deductions_breakdown')->nullable()->after('deductions');
            $table->decimal('gross_pay', 12, 2)->default(0)->after('bonuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payrolls', function (Blueprint $table) {
            $table->dropColumn('allowances_breakdown');
            $table->dropColumn('deductions_breakdown');
            $table->dropColumn('gross_pay');
        });
    }
};
