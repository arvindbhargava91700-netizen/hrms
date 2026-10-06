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
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('package_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('room_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('occupancy_type', ['standard', 'per_bed', 'full_room'])->default('standard');
            $table->integer('beds_booked')->default(1);
            
            $table->foreignId('shift_id')->nullable()->constrained('listing_shifts')->nullOnDelete();
            $table->json('trainer_ids')->nullable();
            
            $table->enum('payment_method', ['cash', 'online', 'auto_pay'])->default('online');
            $table->enum('status', ['pending_otp', 'confirmed', 'completed', 'cancelled'])->default('pending_otp');
            
            $table->string('otp', 6)->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            
            $table->nullableMorphs('coupon');
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
