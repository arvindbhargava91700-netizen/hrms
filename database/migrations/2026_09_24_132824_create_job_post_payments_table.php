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
        Schema::create('job_post_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id')->index();
            $table->unsignedBigInteger('job_post_id');
            $table->unsignedBigInteger('job_plan_id');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->string('transaction_ref')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_post_payments');
    }
};
