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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'gst_type')) {
                $table->string('gst_type')->default('notinclude')->after('amount');
            }
            if (!Schema::hasColumn('products', 'gst_percent')) {
                $table->decimal('gst_percent', 8, 2)->default(0.00)->after('gst_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'gst_percent')) {
                $table->dropColumn('gst_percent');
            }
            if (Schema::hasColumn('products', 'gst_type')) {
                $table->dropColumn('gst_type');
            }
        });
    }
};
