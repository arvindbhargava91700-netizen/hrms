<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('parent_id');
        });

        // Seed initial ordering for existing departments (by creation time)
        $departments = DB::table('departments')->orderBy('created_at')->get();
        $i = 1;
        foreach ($departments as $dept) {
            DB::table('departments')->where('id', $dept->id)->update(['sort_order' => $i++]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
