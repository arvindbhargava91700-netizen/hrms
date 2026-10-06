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
        $tables = ['users', 'listings', 'visit_bookings', 'bookings', 'subscriptions', 'payments', 'invoices'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table_bp) {
                $table_bp->uuid('created_by')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'listings', 'visit_bookings', 'bookings', 'subscriptions', 'payments', 'invoices'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table_bp) {
                $table_bp->dropColumn('created_by');
            });
        }
    }
};
