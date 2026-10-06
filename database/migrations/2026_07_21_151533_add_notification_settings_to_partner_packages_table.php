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
            $table->boolean('email_notification')->default(false);
            $table->boolean('app_notification')->default(false);
            $table->boolean('sms_notification')->default(false);
            $table->boolean('whatsapp_notification')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_packages', function (Blueprint $table) {
            $table->dropColumn([
                'email_notification',
                'app_notification',
                'sms_notification',
                'whatsapp_notification'
            ]);
        });
    }
};
