<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── coupons ───────────────────────────────────────────────
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['flat', 'percent'])->default('flat');
            $table->decimal('value', 10, 2);                    // discount value
            $table->decimal('min_amount', 10, 2)->default(0);   // min plan price to apply
            $table->decimal('max_discount', 10, 2)->nullable(); // cap for percent type
            $table->unsignedBigInteger('listing_id')->nullable()->comment('null = global');
            $table->integer('max_uses')->default(0);            // 0 = unlimited
            $table->integer('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── listing_reviews ───────────────────────────────────────
        Schema::create('listing_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('listing_id')->constrained('listings')->onDelete('cascade');
            $table->foreignUuid('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->unsignedTinyInteger('rating');               // 1–5
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['listing_id', 'customer_id']);
        });

        // ── extend subscriptions table ────────────────────────────
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'online', 'auto_pay'])->default('online')->after('auto_renew');
            $table->enum('booking_status', ['pending_otp', 'confirmed', 'active', 'cancelled'])->default('pending_otp')->after('payment_method');
            $table->string('otp', 6)->nullable()->after('booking_status');
            $table->timestamp('otp_verified_at')->nullable()->after('otp');
            $table->nullableMorphs('coupon');                   // coupon_id + coupon_type
            $table->decimal('discount_amount', 10, 2)->default(0)->after('otp_verified_at');
            $table->decimal('final_amount', 10, 2)->nullable()->after('discount_amount');
        });

        // ── extend listings table with contact phone ──────────────
        Schema::table('listings', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'booking_status', 'otp', 'otp_verified_at', 'coupon_id', 'coupon_type', 'discount_amount', 'final_amount']);
        });
        Schema::dropIfExists('listing_reviews');
        Schema::dropIfExists('coupons');
    }
};
