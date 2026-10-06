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
        Schema::create('customer_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lead_id');
            $table->uuid('employee_id');
            $table->string('gps_location')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->boolean('manager_approved')->default(false);
            $table->timestamps();
            
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_visits');
    }
};
