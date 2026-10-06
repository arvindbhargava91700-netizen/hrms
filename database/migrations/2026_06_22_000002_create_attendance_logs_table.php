<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignUuid('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('listing_id')->constrained('listings')->onDelete('cascade');
            $table->date('date');
            $table->timestamp('punch_in_at')->nullable();
            $table->timestamp('punch_out_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // One punch-in record per subscription per day
            $table->unique(['subscription_id', 'date']);
            $table->index(['customer_id', 'date']);
            $table->index(['listing_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
