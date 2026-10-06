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
        Schema::create('recruitments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('partner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('job_title');
            $table->integer('vacancies_count')->default(1);
            $table->string('candidate_name');
            $table->string('candidate_email')->nullable();
            $table->string('candidate_phone')->nullable();
            $table->string('interview_stage')->default('applied'); // applied, screening, technical_round, hr_round, final_round
            $table->string('status')->default('under_review'); // under_review, selected, rejected, on_hold
            $table->string('offer_status')->default('pending'); // pending, offered, offer_accepted, offer_declined
            $table->string('joining_status')->default('pending'); // pending, joined, not_joined
            $table->decimal('cost_per_hire', 12, 2)->default(0.00);
            $table->date('interview_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitments');
    }
};
