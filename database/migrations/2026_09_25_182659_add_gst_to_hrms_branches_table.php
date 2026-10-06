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
        Schema::table('hrms_branches', function (Blueprint $table) {
            $table->string('gst_number')->nullable()->after('company_logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hrms_branches', function (Blueprint $table) {
            $table->dropColumn('gst_number');
        });
    }
};
