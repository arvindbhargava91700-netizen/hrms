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
        Schema::table('partner_packages', function (Blueprint $table) {
            $table->dropColumn('single_category_listing');
            $table->integer('category_limit')->nullable()->after('is_free_trial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_packages', function (Blueprint $table) {
            $table->boolean('single_category_listing')->default(false);
            $table->dropColumn('category_limit');
        });
    }
};
