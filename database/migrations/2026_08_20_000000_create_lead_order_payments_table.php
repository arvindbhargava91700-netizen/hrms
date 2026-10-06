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
        Schema::create('lead_order_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lead_order_id');
            $table->uuid('paid_by')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash');
            $table->date('payment_date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('lead_order_id')->references('id')->on('lead_orders')->onDelete('cascade');
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_order_payments');
    }
};
