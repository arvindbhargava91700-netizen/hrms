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
        $tables = [
            'partner_settings',
            'holidays',
            'commission_levels',
            'expense_categories',
            'pipeline_stages',
            'attendance_checklists',
            'leave_categories'
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'partner_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    try {
                        $t->dropForeign($table . '_partner_id_foreign');
                    } catch (\Exception $e) {
                        try {
                            $t->dropForeign('hrms_' . $table . '_partner_id_foreign');
                        } catch (\Exception $e) {
                            try {
                                $t->dropForeign(['partner_id']);
                            } catch (\Exception $e) {
                                // ignore
                            }
                        }
                    }
                });

                Schema::table($table, function (Blueprint $t) {
                    $t->string('partner_id')->nullable()->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
