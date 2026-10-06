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
        Schema::create('incentive_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id'); // Link to company/partner
            $table->integer('min_percentage'); // e.g. 50
            $table->integer('max_percentage')->nullable(); // e.g. 70
            $table->decimal('incentive_percent', 5, 2)->default(0); // e.g. 2.00
            $table->decimal('bonus_amount', 10, 2)->default(0);
            $table->timestamps();
            
            $table->foreign('partner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_rules');
    }
};
