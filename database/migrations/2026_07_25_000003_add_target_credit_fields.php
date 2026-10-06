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
        Schema::table('pipeline_stages', function (Blueprint $table) {
            $table->boolean('counts_towards_target')->default(false)->after('order_index');
        });

        Schema::table('lead_orders', function (Blueprint $table) {
            $table->boolean('target_credited')->default(false)->after('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pipeline_stages', function (Blueprint $table) {
            $table->dropColumn('counts_towards_target');
        });

        Schema::table('lead_orders', function (Blueprint $table) {
            $table->dropColumn('target_credited');
        });
    }
};
