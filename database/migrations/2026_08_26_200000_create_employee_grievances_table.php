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
        Schema::create('employee_grievances', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('partner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('record_type')->default('complaint'); // complaint, warning, show_cause, disciplinary_action
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('incident_date')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->string('status')->default('open'); // open, under_investigation, resolved, closed
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'employee_id']);
            $table->index('record_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_grievances');
    }
};
