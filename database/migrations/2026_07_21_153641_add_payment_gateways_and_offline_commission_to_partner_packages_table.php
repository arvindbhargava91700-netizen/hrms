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
        Schema::table('partner_packages', function (Blueprint $table) {
            $table->json('payment_gateways')->nullable();
            $table->enum('offline_commission_type', ['fixed', 'percent'])->default('fixed');
            $table->decimal('offline_commission_value', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_packages', function (Blueprint $table) {
            $table->dropColumn(['payment_gateways', 'offline_commission_type', 'offline_commission_value']);
        });
    }
};
