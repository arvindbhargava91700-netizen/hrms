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
        Schema::table('job_templates', function (Blueprint $table) {
            $table->string('minimum_education')->nullable();
            $table->string('english_level')->nullable();
            $table->string('min_experience_years')->nullable();
            $table->string('max_experience_years')->nullable();
            $table->string('gender_preference')->nullable();
            $table->json('interview_information')->nullable();
            $table->json('additional_perks')->nullable();
            $table->boolean('joining_fee_required')->default(false);
            $table->string('salary_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_templates', function (Blueprint $table) {
            $table->dropColumn([
                'minimum_education',
                'english_level',
                'min_experience_years',
                'max_experience_years',
                'gender_preference',
                'interview_information',
                'additional_perks',
                'joining_fee_required',
                'salary_type'
            ]);
        });
    }
};
