<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make partner_id nullable in HRMS tables so records
     * can be created without a partner scope.
     */
    public function up(): void
    {
        // Employee Documents — drop FK, make nullable, re-add FK
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropForeign('employee_documents_partner_id_foreign');
        });
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->string('partner_id')->nullable()->change();
        });
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->foreign('partner_id', 'employee_documents_partner_id_foreign')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });

        // Employee Exits — drop FK if exists, make nullable, re-add FK
        // Check if FK exists before dropping
        try {
            Schema::table('employee_exits', function (Blueprint $table) {
                $table->dropForeign('employee_exits_partner_id_foreign');
            });
        } catch (\Exception $e) {
            // FK may not exist, continue
        }
        Schema::table('employee_exits', function (Blueprint $table) {
            $table->string('partner_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropForeign('employee_documents_partner_id_foreign');
        });
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->string('partner_id')->nullable(false)->change();
        });
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->foreign('partner_id', 'employee_documents_partner_id_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('employee_exits', function (Blueprint $table) {
            $table->string('partner_id')->nullable(false)->change();
        });
    }
};
