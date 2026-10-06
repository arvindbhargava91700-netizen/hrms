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
            $table->boolean('is_free_trial')->default(false)->after('name');
            $table->boolean('single_category_listing')->default(false)->after('is_free_trial');
            $table->integer('listing_limit')->nullable()->after('single_category_listing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_packages', function (Blueprint $table) {
            $table->dropColumn(['is_free_trial', 'single_category_listing', 'listing_limit']);
        });
    }
};
