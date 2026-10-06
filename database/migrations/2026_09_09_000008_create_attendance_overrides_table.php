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
        Schema::create('attendance_overrides', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id')->nullable();
            $table->uuid('employee_id');
            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->uuid('overridden_by')->nullable();
            $table->date('date');
            $table->string('previous_status')->nullable();
            $table->string('previous_check_in')->nullable();
            $table->string('previous_check_out')->nullable();
            $table->string('new_status');
            $table->string('new_check_in')->nullable();
            $table->string('new_check_out')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->integer('working_minutes')->nullable();
            $table->integer('late_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('overridden_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_overrides');
    }
};
