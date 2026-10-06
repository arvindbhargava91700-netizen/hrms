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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id')->index(); // the admin/partner who owns this
            $table->uuid('created_by')->nullable(); // the user who created this post
            $table->string('job_title');
            $table->string('job_code')->unique()->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('employment_type')->nullable(); // Full Time, Part Time, etc
            $table->string('experience')->nullable();
            $table->string('salary')->nullable();
            $table->integer('vacancies_count')->default(1);
            $table->decimal('referral_budget', 10, 2)->default(0);
            $table->decimal('referral_amount', 10, 2)->default(0);
            $table->date('application_deadline')->nullable();
            $table->text('job_description')->nullable();
            $table->text('skills')->nullable();
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->decimal('wallet_deducted', 10, 2)->default(0);
            $table->decimal('online_payable', 10, 2)->default(0);
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
