<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('has_shifts')->default(false)->after('is_active');
            $table->boolean('has_trainers')->default(false)->after('has_shifts');
            $table->boolean('has_rooms')->default(false)->after('has_trainers');
            $table->boolean('has_packages')->default(false)->after('has_rooms');
        });
        // listing_shifts and listing_trainers are already created with correct names
        // in the earlier migration (2026_06_07_120000_add_category_listing_tables.php)
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['has_shifts', 'has_trainers', 'has_rooms', 'has_packages']);
        });
    }
};
