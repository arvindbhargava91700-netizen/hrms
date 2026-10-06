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
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignUuid('booking_id')->nullable()->after('id')->constrained('bookings')->nullOnDelete();
            // Make subscription_id nullable because initial booking invoice doesn't have a subscription yet
            $table->foreignUuid('subscription_id')->nullable()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignUuid('booking_id')->nullable()->after('id')->constrained('bookings')->nullOnDelete();
            $table->foreignUuid('subscription_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropColumn('booking_id');
            // Reverting to non-nullable might fail if there's data, but standard practice in down()
            $table->foreignUuid('subscription_id')->nullable(false)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropColumn('booking_id');
            $table->foreignUuid('subscription_id')->nullable(false)->change();
        });
    }
};
