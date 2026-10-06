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
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('auto_mark_attendance')->default(false);
            $table->string('auto_mark_status')->nullable()->default('absent')->comment('Status to mark if not punched in');
            $table->integer('late_tolerance_minutes')->default(15);
            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('hrms_branches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_shifts');
    }
};
