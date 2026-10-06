<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_order_stage_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_order_id')->constrained('lead_orders')->cascadeOnDelete();
            $table->foreignUuid('pipeline_stage_id')->constrained('pipeline_stages')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment');
            $table->timestamps();

            $table->index(['lead_order_id', 'pipeline_stage_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_order_stage_comments');
    }
};
