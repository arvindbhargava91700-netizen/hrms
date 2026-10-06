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
        Schema::table('lead_orders', function (Blueprint $table) {
            $table->uuid('partner_id')->nullable()->after('id');
            $table->decimal('discount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('final_amount', 12, 2)->default(0)->after('discount');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('final_amount');
            $table->string('approval_status')->default('pending')->after('remaining_balance');
            $table->string('payment_status')->default('pending')->after('approval_status');

            $table->dropColumn(['discount_amount', 'payment_received', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_orders', function (Blueprint $table) {
            $table->dropColumn(['partner_id', 'discount', 'final_amount', 'paid_amount', 'approval_status', 'payment_status']);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('payment_received', 12, 2)->default(0);
            $table->string('status')->default('pending_manager');
        });
    }
};
