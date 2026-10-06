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
        Schema::create('job_post_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->onDelete('cascade');
            $table->uuid('referred_by');
            $table->foreign('referred_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('candidate_name');
            $table->string('candidate_email');
            $table->string('candidate_phone');
            $table->string('resume_path')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'interviewed', 'hired', 'rejected'])->default('pending');
            $table->timestamp('referral_date')->useCurrent();
            $table->timestamps();

            // Constraint: A user cannot refer the exact same candidate (by email) for the exact same job twice.
            $table->unique(['job_post_id', 'referred_by', 'candidate_email'], 'unique_referral_per_user');
            $table->unique(['job_post_id', 'referred_by', 'candidate_phone'], 'unique_referral_per_user_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_post_referrals');
    }
};
