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
        Schema::table('customer_visits', function (Blueprint $table) {
            $table->uuid('partner_id')->nullable()->after('id');
            $table->datetime('visit_date')->nullable()->after('employee_id');
            $table->string('location')->nullable()->after('gps_location');
            $table->string('purpose')->nullable()->after('location');
            $table->string('status')->default('scheduled')->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_visits', function (Blueprint $table) {
            $table->dropColumn(['partner_id', 'visit_date', 'location', 'purpose', 'status']);
        });
    }
};
