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
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('color')->default('primary');
            $table->integer('order')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_statuses');
    }
};
