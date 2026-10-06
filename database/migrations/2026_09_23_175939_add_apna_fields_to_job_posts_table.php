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
        Schema::table('job_posts', function (Blueprint $table) {
            $table->string('job_city')->nullable()->after('salary');
            $table->boolean('is_work_from_home')->default(false);
            $table->decimal('min_salary', 10, 2)->nullable();
            $table->decimal('max_salary', 10, 2)->nullable();
            $table->string('salary_type')->nullable();
            $table->json('additional_perks')->nullable();
            $table->boolean('joining_fee_required')->default(false);
            $table->string('minimum_education')->nullable();
            $table->integer('min_experience_years')->nullable();
            $table->integer('max_experience_years')->nullable();
            $table->string('english_level')->nullable();
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->string('gender_preference')->nullable();
            $table->string('degree_requirement')->nullable();
            $table->string('industry_preference')->nullable();
            $table->text('interview_information')->nullable();
            $table->string('plan_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            //
        });
    }
};
