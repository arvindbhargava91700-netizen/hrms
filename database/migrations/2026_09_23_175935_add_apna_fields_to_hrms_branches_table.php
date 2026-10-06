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
            $table->string('company_logo')->nullable()->after('status');
            $table->year('founded_year')->nullable();
            $table->string('website')->nullable();
            $table->string('company_size')->nullable();
            $table->string('company_type')->nullable();
            $table->string('industry')->nullable();
            $table->text('about_company')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hrms_branches', function (Blueprint $table) {
            //
        });
    }
};
