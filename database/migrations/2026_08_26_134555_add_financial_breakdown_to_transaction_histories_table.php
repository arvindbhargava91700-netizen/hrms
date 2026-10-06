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
        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->decimal('wallet_deducted', 12, 2)->default(0)->after('total_amount');
            $table->decimal('online_payable', 12, 2)->default(0)->after('wallet_deducted');
            $table->string('payment_gateway_id')->nullable()->after('online_payable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->dropColumn(['wallet_deducted', 'online_payable', 'payment_gateway_id']);
        });
    }
};
