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
        Schema::create('employee_probations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('partner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->date('confirmation_due_date');
            $table->date('extended_due_date')->nullable();
            $table->boolean('is_extended')->default(false);
            $table->text('extension_reason')->nullable();
            $table->text('asset_allocation')->nullable();
            $table->string('status')->default('on_probation'); // on_probation, extended, confirmed, rejected_failed
            $table->date('confirmation_date')->nullable();
            $table->text('evaluation_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_probations');
    }
};
