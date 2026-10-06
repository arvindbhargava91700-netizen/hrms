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
            $table->uuid('current_stage_id')->nullable()->after('approval_status');
            
            $table->foreign('current_stage_id')->references('id')->on('pipeline_stages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_orders', function (Blueprint $table) {
            $table->dropForeign(['current_stage_id']);
            $table->dropColumn('current_stage_id');
        });
    }
};
