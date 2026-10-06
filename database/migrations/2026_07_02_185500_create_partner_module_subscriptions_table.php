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
        Schema::create('partner_module_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id');
            $table->foreignId('system_module_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_package_id')->constrained()->cascadeOnDelete();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status'); // active, expired, cancelled
            $table->timestamps();
            
            $table->foreign('partner_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_module_subscriptions');
    }
};
