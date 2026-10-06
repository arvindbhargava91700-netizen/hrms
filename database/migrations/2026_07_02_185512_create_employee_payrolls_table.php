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
        Schema::create('employee_payrolls', function (Blueprint $table) {
            $table->id();
            $table->uuid('employee_id');
            $table->string('month'); // e.g. 07
            $table->string('year'); // e.g. 2026
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->decimal('net_pay', 10, 2);
            $table->string('status')->default('pending'); // pending, paid
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payrolls');
    }
};
