<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shared_referrals', function (Blueprint $table) {
            $table->string('app_com_status')->default('pending')->after('referral_code');
            $table->string('post_com_status')->default('pending')->after('app_com_status');
        });
    }

    public function down(): void
    {
        Schema::table('shared_referrals', function (Blueprint $table) {
            $table->dropColumn(['app_com_status', 'post_com_status']);
        });
    }
};