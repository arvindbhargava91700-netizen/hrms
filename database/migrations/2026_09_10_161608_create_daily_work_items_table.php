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
        Schema::create('daily_work_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_work_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('employee_tasks')->nullOnDelete();
            $table->string('title');
            $table->text('details')->nullable();
            $table->string('category')->default('Other');
            $table->string('status')->default('Completed');
            $table->string('priority')->default('Medium');
            $table->integer('time_spent_hours')->default(0);
            $table->integer('time_spent_minutes')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_work_items');
    }
};
