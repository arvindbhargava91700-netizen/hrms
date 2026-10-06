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
        Schema::create('employee_pips', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('partner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('improvement_targets')->nullable();
            $table->text('review_result')->nullable();
            $table->string('status')->default('active'); // active, under_review, completed_passed, completed_failed, cancelled
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_pips');
    }
};
