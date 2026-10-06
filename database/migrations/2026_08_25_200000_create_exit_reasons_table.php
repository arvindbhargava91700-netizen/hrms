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
        Schema::create('exit_reasons', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id')->nullable();
            $table->string('name');
            $table->enum('type', ['voluntary', 'involuntary'])->default('voluntary');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exit_reasons');
    }
};
