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
        Schema::create('partner_package_system_module', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_package_id');
            $table->foreignId('system_module_id')->constrained()->cascadeOnDelete();
            
            $table->foreign('partner_package_id')->references('id')->on('partner_packages')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_package_system_module');
    }
};
