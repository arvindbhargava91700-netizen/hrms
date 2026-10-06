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
        Schema::table('commission_payouts', function (Blueprint $table) {
            $table->decimal('tds_percent', 5, 2)->default(0)->after('upline_commission_earned');
            $table->decimal('tds_amount', 12, 2)->default(0)->after('tds_percent');
            $table->decimal('net_payout', 12, 2)->default(0)->after('tds_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_payouts', function (Blueprint $table) {
            $table->dropColumn(['tds_percent', 'tds_amount', 'net_payout']);
        });
    }
};
