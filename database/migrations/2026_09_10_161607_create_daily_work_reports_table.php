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
        Schema::create('daily_work_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('partner_id')->constrained('users')->cascadeOnDelete();
            $table->date('report_date');
            $table->text('summary')->nullable();
            $table->enum('status', ['draft', 'submitted', 'manager_approved', 'partner_approved', 'rejected'])->default('draft');
            $table->foreignUuid('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // An employee can only have one report per day
            $table->unique(['employee_id', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_work_reports');
    }
};
