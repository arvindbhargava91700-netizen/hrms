<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_order_stage_approvals')) {
            return;
        }

        Schema::create('lead_order_stage_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('lead_order_id')
                ->constrained('lead_orders')
                ->cascadeOnDelete();

            $table->foreignUuid('pipeline_stage_id')
                ->constrained('pipeline_stages')
                ->cascadeOnDelete();

            $table->foreignUuid('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status')->default('approved');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Short index name to stay within MySQL's 64-character limit
            $table->unique(
                ['lead_order_id', 'pipeline_stage_id'],
                'lead_order_stage_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_order_stage_approvals');
    }
};