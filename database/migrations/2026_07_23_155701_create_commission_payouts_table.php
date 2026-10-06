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
        Schema::create('commission_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id')->constrained('users')->onDelete('cascade');
            $table->string('month'); // e.g. "07"
            $table->string('year'); // e.g. "2026"
            $table->decimal('total_new_business', 10, 2)->default(0);
            $table->decimal('total_recovery_business', 10, 2)->default(0);
            $table->decimal('target_amount', 10, 2)->default(0);
            $table->decimal('commission_earned', 10, 2)->default(0);
            $table->decimal('recovery_earned', 10, 2)->default(0);
            $table->decimal('upline_commission_earned', 10, 2)->default(0); // If they got commission from their downline
            $table->decimal('total_payout', 10, 2)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('calculation_details')->nullable(); // Store how the commission was calculated
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_payouts');
    }
};
