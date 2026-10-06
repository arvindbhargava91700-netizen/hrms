<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_payroll_id')->constrained('employee_payrolls')->cascadeOnDelete();
            $table->string('adjustment_type'); // basic_salary | allowance | bonus | deduction
            $table->string('field_key')->nullable();
            $table->decimal('original_amount', 12, 2)->default(0);
            $table->decimal('adjusted_amount', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_adjustments');
    }
};