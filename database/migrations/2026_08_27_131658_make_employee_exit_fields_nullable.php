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
        Schema::table('employee_exits', function (Blueprint $table) {
            $table->foreignUuid('employee_id')
                ->nullable()
                ->change();

            $table->date('resignation_date')
                ->nullable()
                ->change();

            $table->text('exit_reason')
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_exits', function (Blueprint $table) {
             $table->foreignUuid('employee_id')
                ->nullable(false)
                ->change();

            $table->date('resignation_date')
                ->nullable(false)
                ->change();

            $table->text('exit_reason')
                ->nullable(false)
                ->change();
        });
    }
};
