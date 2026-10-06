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
        Schema::create('module_billing_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id');
            $table->foreignId('partner_module_subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('reference_id')->nullable();
            $table->string('status'); // paid, failed
            $table->timestamps();
            
            $table->foreign('partner_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_billing_logs');
    }
};
