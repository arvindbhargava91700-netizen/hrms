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
        Schema::create('lead_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lead_id');
            $table->uuid('employee_id');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('payment_received', 12, 2)->default(0);
            $table->decimal('remaining_balance', 12, 2)->default(0);
            
            // Approval flow: pending_manager -> pending_finance -> pending_docs -> pending_accounts -> pending_operations -> pending_recovery -> completed
            $table->string('status')->default('pending_manager');
            
            $table->text('finance_notes')->nullable();
            $table->text('doc_notes')->nullable();
            $table->text('accounts_notes')->nullable();
            $table->text('recovery_notes')->nullable();
            
            $table->timestamps();
            
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_orders');
    }
};
