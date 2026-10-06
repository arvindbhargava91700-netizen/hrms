<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Training Master Table
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('trainer')->nullable();
            $table->enum('training_type', ['online', 'classroom', 'on_job', 'workshop', 'certification'])->default('classroom');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('duration')->default(1)->comment('Duration in hours or days');
            $table->decimal('passing_score', 5, 2)->default(60.00)->comment('Passing percentage');
            $table->enum('status', ['scheduled', 'active', 'completed', 'cancelled'])->default('scheduled');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        // 2. Employee Training Assignments Table
        Schema::create('employee_trainings', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->constrained('hrms_branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->uuid('employee_id');
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled'])->default('assigned');
            $table->integer('total_sessions')->default(1);
            $table->integer('sessions_attended')->default(0);
            $table->decimal('attendance_percentage', 5, 2)->default(0.00);
            $table->decimal('maximum_score', 8, 2)->default(100.00);
            $table->decimal('obtained_score', 8, 2)->default(0.00);
            $table->decimal('assessment_score', 5, 2)->default(0.00)->comment('Score percentage');
            $table->enum('result', ['pending', 'passed', 'failed'])->default('pending');
            $table->text('remarks')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['employee_id', 'training_id'], 'emp_training_unique');
        });

        // 3. Training Attendance Log Table
        Schema::create('training_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_training_id')->constrained('employee_trainings')->cascadeOnDelete();
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            $table->uuid('employee_id');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['employee_training_id', 'date'], 'emp_training_att_date_unique');
        });

        // 4. Training Assessment Log Table
        Schema::create('training_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_training_id')->constrained('employee_trainings')->cascadeOnDelete();
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            $table->uuid('employee_id');
            $table->date('assessment_date');
            $table->decimal('maximum_score', 8, 2)->default(100.00);
            $table->decimal('obtained_score', 8, 2)->default(0.00);
            $table->decimal('score_percentage', 5, 2)->default(0.00);
            $table->enum('result', ['passed', 'failed'])->default('passed');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_assessments');
        Schema::dropIfExists('training_attendances');
        Schema::dropIfExists('employee_trainings');
        Schema::dropIfExists('trainings');
    }
};
