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
        Schema::create('commission_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('partner_id')->constrained('users')->onDelete('cascade');
            $table->string('level_name'); // e.g. "Junior (L1)", "Manager (L4)"
            $table->integer('level_order'); // 1 = L1, 2 = L2, etc. Higher number = higher up the chain
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_levels');
    }
};
