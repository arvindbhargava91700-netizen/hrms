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
        Schema::create('attendance_checklists', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id');
            $table->string('question');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_checklists');
    }
};
