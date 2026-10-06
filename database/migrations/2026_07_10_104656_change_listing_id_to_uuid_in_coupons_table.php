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
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('listing_id');
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->uuid('listing_id')->nullable()->comment('null = global')->after('max_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('listing_id');
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->nullable()->comment('null = global')->after('max_discount');
        });
    }
};
