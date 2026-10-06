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
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'email')) {
                $table->string('email')->nullable()->after('customer_mobile');
            }
            if (!Schema::hasColumn('leads', 'company_name')) {
                $table->string('company_name')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'company_name')) {
                $table->dropColumn('company_name');
            }
            if (Schema::hasColumn('leads', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
