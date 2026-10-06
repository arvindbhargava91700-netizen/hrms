<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_documents', function (Blueprint $table) {
            $table->json('submission_data')->nullable()->after('bank_details');
            $table->timestamp('submitted_at')->nullable()->after('submission_data');
        });
    }

    public function down(): void
    {
        Schema::table('kyc_documents', function (Blueprint $table) {
            $table->dropColumn(['submission_data', 'submitted_at']);
        });
    }
};
