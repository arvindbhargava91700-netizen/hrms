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
        Schema::create('employee_exits', function (Blueprint $table) {

            // Primary Key
            $table->uuid('id')->primary();

            // Employee / Organization
            $table->foreignUuid('partner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('employee_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('branch_id')
                ->nullable();

            $table->unsignedBigInteger('department_id')
                ->nullable();

            $table->unsignedBigInteger('designation_id')
                ->nullable();

            // Exit Dates
            $table->date('resignation_date');

            $table->date('exit_date')
                ->nullable();

            $table->date('last_working_date')
                ->nullable();

            // Exit Details
            $table->enum('exit_type', [
                'voluntary',
                'involuntary'
            ])->default('voluntary');

            $table->text('exit_reason');

            $table->integer('notice_period_days')
                ->default(30);

            // Clearance
            $table->string('clearance_status')
                ->default('pending');
            // pending, in_progress, partially_cleared, fully_cleared

            // Full & Final Settlement
            $table->string('fnf_status')
                ->default('pending');
            // pending, in_progress, settled, on_hold

            $table->decimal('fnf_amount', 12, 2)
                ->default(0.00);

            $table->date('fnf_settlement_date')
                ->nullable();

            // Exit Status
            $table->string('status')
                ->default('resigned');
            // resigned, serving_notice, cleared, exited, cancelled

            // Additional Information
            $table->text('exit_interview_notes')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            // Audit
            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('branch_id')
                ->references('id')
                ->on('hrms_branches')
                ->nullOnDelete();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->foreign('designation_id')
                ->references('id')
                ->on('designations')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_exits');
    }
};