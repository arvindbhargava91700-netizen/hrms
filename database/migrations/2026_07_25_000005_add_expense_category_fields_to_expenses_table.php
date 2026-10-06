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
        Schema::table('expenses', function (Blueprint $table) {
            $table->uuid('expense_category_id')->nullable()->after('employee_id');
            $table->decimal('quantity', 10, 2)->nullable()->after('amount');
            $table->decimal('unit_rate', 10, 2)->nullable()->after('quantity');

            $table->foreign('expense_category_id')->references('id')->on('expense_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']);
            $table->dropColumn(['expense_category_id', 'quantity', 'unit_rate']);
        });
    }
};
