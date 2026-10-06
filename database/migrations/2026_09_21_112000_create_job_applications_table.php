<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('referral_id')->nullable();
            $table->string('referral_code')->nullable();
            $table->unsignedBigInteger('apply_user_id');
            $table->string('status')->default('applied');
            $table->timestamps();

            $table->unique(['post_id', 'apply_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};