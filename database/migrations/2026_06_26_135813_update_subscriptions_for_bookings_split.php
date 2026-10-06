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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignUuid('booking_id')->nullable()->after('id')->constrained('bookings')->nullOnDelete();
            
            $table->dropForeign(['shift_id']);
            
            // Note: Since we are in development, dropping these columns directly.
            // If SQLite is used, dropping multiple columns might have issues, but for MySQL it's fine.
            $table->dropColumn([
                'payment_method',
                'booking_status',
                'otp',
                'otp_verified_at',
                'coupon_id',
                'coupon_type',
                'discount_amount',
                'final_amount',
                'shift_id',
                'trainer_ids'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropColumn('booking_id');
            
            $table->enum('payment_method', ['cash', 'online', 'auto_pay'])->default('online');
            $table->enum('booking_status', ['pending_otp', 'confirmed', 'active', 'cancelled', 'completed'])->default('pending_otp');
            $table->string('otp', 6)->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->nullableMorphs('coupon');
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->nullable();
            $table->foreignId('shift_id')->nullable();
            $table->json('trainer_ids')->nullable();
        });
    }
};
