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
            $table->json('commission_ranges')->nullable()->after('commission_value');
            $table->json('offline_commission_ranges')->nullable()->after('offline_commission_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_packages', function (Blueprint $table) {
            $table->dropColumn(['commission_ranges', 'offline_commission_ranges']);
        });
    }
};
