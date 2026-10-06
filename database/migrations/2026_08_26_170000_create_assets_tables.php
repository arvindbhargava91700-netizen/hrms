<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Assets Master Table
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->constrained('hrms_branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('asset_code', 100);
            $table->enum('category', ['laptop', 'mobile', 'sim', 'id_card', 'other'])->default('other');
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('imei_number')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('sim_number')->nullable();
            $table->string('card_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->enum('condition', ['new', 'good', 'fair', 'damaged', 'lost'])->default('good');
            $table->enum('status', ['available', 'issued', 'damaged'])->default('available');
            $table->text('description')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['partner_id', 'asset_code'], 'partner_asset_code_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
