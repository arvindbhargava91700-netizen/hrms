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
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('partner_id');
            $table->string('head_id')->nullable()->after('parent_id');
            
            $table->foreign('parent_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('head_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['head_id']);
            $table->dropColumn(['parent_id', 'head_id']);
        });
    }
};
