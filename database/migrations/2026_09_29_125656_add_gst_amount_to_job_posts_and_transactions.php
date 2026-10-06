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
        Schema::table('job_posts', function (Blueprint $table) {
            $table->decimal('gst_amount', 10, 2)->default(0)->after('online_payable');
        });

        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->decimal('gst_amount', 12, 2)->default(0)->after('wallet_deducted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn('gst_amount');
        });

        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->dropColumn('gst_amount');
        });
    }
};
