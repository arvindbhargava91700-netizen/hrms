<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_remarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('employee_tasks')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('remark');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_remarks');
    }
};
