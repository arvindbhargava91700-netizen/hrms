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
            $table->decimal('base_amount', 12, 2)->default(0)->after('total_amount');
            $table->string('gst_type')->default('notinclude')->after('base_amount');
            $table->decimal('gst_percent', 8, 2)->default(0)->after('gst_type');
            $table->decimal('gst_amount', 12, 2)->default(0)->after('gst_percent');
        });

        Schema::table('lead_order_items', function (Blueprint $table) {
            $table->decimal('base_price', 12, 2)->nullable()->after('price');
            $table->string('gst_type')->default('notinclude')->after('base_price');
            $table->decimal('gst_percent', 8, 2)->default(0)->after('gst_type');
            $table->decimal('gst_amount', 12, 2)->default(0)->after('gst_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_orders', function (Blueprint $table) {
            $table->dropColumn(['base_amount', 'gst_type', 'gst_percent', 'gst_amount']);
        });

        Schema::table('lead_order_items', function (Blueprint $table) {
            $table->dropColumn(['base_price', 'gst_type', 'gst_percent', 'gst_amount']);
        });
    }
};
