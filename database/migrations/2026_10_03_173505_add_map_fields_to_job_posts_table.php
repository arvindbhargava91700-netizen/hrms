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
            $table->decimal('office_lat', 10, 7)->nullable();
            $table->decimal('office_lng', 10, 7)->nullable();
            $table->string('office_city')->nullable();
            $table->boolean('accept_pan_india_candidates')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn(['office_lat', 'office_lng', 'office_city', 'accept_pan_india_candidates']);
        });
    }
};
