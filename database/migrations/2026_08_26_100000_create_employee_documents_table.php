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
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('partner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_category')->default('kyc'); // kyc, offer_letter, appointment_letter, agreement, other
            $table->string('document_type')->default('other'); // aadhaar, pan, bank_passbook, kyc, offer_letter, appointment_letter, agreement, other
            $table->string('title');
            $table->string('document_number')->nullable();
            $table->string('file_path')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('pending_verification'); // pending_verification, verified, rejected, expired
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'employee_id']);
            $table->index('status');
            $table->index('document_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
