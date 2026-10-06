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
            $table->unsignedBigInteger('template_id')->nullable()->index();
            $table->unsignedBigInteger('job_plan_id')->nullable();
            $table->dateTime('expires_at')->nullable()->index();
            $table->dateTime('published_at')->nullable();
            $table->string('city')->nullable();
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('job_type')->nullable();
            $table->json('screening_questions')->nullable();
            
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex(['template_id']);
            $table->dropIndex(['expires_at']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'template_id', 'job_plan_id', 'expires_at', 'published_at',
                'city', 'salary_min', 'salary_max', 'job_type', 'screening_questions'
            ]);
        });
    }
};
