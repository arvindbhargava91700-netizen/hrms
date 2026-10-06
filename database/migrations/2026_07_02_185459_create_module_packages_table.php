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
        Schema::create('module_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_module_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. HRMS Starter
            $table->decimal('price', 10, 2);
            $table->string('billing_cycle'); // monthly, yearly, lifetime
            $table->json('features_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_packages');
    }
};
