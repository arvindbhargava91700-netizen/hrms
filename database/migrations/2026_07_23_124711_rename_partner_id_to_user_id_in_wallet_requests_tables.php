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
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->renameColumn('partner_id', 'user_id');
        });

        Schema::table('wallet_recharge_requests', function (Blueprint $table) {
            $table->renameColumn('partner_id', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->renameColumn('user_id', 'partner_id');
        });

        Schema::table('wallet_recharge_requests', function (Blueprint $table) {
            $table->renameColumn('user_id', 'partner_id');
        });
    }
};
