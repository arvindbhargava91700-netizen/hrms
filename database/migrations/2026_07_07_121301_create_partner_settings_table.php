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
        Schema::create('partner_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
            
            $table->foreign('partner_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['partner_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_settings');
    }
};
