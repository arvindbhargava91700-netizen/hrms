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
        Schema::create('candidate_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            
            // Basic & Job Profile
            $table->string('highest_education')->nullable();
            $table->string('doctorate')->nullable();
            $table->json('educations')->nullable(); // Array of education objects
            $table->json('skills')->nullable(); // Array of skills
            
            $table->string('resume_path')->nullable();
            $table->timestamp('resume_updated_at')->nullable();
            
            $table->json('preferred_job_roles')->nullable();
            $table->json('preferred_locations')->nullable();
            $table->string('preferred_job_type')->nullable(); // Full Time
            $table->string('preferred_work_mode')->nullable(); // Work from Office
            $table->string('preferred_shift')->nullable(); // Day Shift
            $table->decimal('expected_salary', 10, 2)->nullable();
            
            $table->json('documents_and_assets')->nullable();
            
            // Work Experience
            $table->json('work_experiences')->nullable(); // Array of experience objects
            $table->integer('total_experience_years')->default(0);
            $table->integer('total_experience_months')->default(0);
            $table->decimal('current_monthly_salary', 10, 2)->nullable();
            
            $table->json('internships')->nullable(); // Array of internship objects
            
            $table->string('gender')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_profiles');
    }
};
