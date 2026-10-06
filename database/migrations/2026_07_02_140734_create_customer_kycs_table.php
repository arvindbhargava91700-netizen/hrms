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
        Schema::create('customer_kycs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('live_photo')->nullable();
            $table->string('aadhaar_front')->nullable();
            $table->string('aadhaar_back')->nullable();
            $table->string('pan_front')->nullable();
            $table->string('pan_back')->nullable();
            $table->string('bank_statement')->nullable();
            $table->string('passport_front')->nullable();
            $table->string('passport_back')->nullable();
            $table->string('driving_license_front')->nullable();
            $table->string('driving_license_back')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_kycs');
    }
};
