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
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partner_id'); // Link to company
            $table->string('customer_name');
            $table->string('customer_mobile')->nullable();
            $table->uuid('assigned_to')->nullable(); // Employee assigned to
            $table->enum('status', ['new', 'first_call', 'interested', 'meeting_scheduled', 'customer_visit', 'quotation', 'negotiation', 'won', 'lost'])->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('partner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
