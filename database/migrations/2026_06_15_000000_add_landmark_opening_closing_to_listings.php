<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('landmark')->nullable()->after('address');
            $table->time('opening_time')->nullable()->after('landmark');
            $table->time('closing_time')->nullable()->after('opening_time');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['landmark', 'opening_time', 'closing_time']);
        });
    }
};
