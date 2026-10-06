<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partner_id');
            $table->uuid('partner_package_id');
            $table->date('starts_at');
            $table->date('expires_at');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('partner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('partner_package_id')->references('id')->on('partner_packages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_subscriptions');
    }
};
