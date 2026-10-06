<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------
        // 1. categories  (self-referencing, no FK dependency)
        // -------------------------------------------------------
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable(); // self-ref added after
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
        });

        // -------------------------------------------------------
        // 2. kyc_documents  (FK → users.id uuid)
        // -------------------------------------------------------
        Schema::create('kyc_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('partner_id')->constrained('users')->onDelete('cascade');
            $table->string('aadhaar_number', 20)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->string('gst_number', 20)->nullable();
            $table->json('bank_details')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // 3. listings  (FK → users, categories)
        // -------------------------------------------------------
        Schema::create('listings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('partner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'suspended'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        // -------------------------------------------------------
        // 4. custom_fields  (FK → categories)
        // -------------------------------------------------------
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('label');
            $table->enum('field_type', ['text', 'textarea', 'number', 'select', 'multiselect', 'checkbox', 'radio', 'date', 'file', 'url'])->default('text');
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // -------------------------------------------------------
        // 5. packages  (FK → listings)
        // -------------------------------------------------------
        Schema::create('packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('listing_id')->constrained('listings')->onDelete('cascade');
            $table->uuid('room_id')->nullable();
            $table->string('name');
            $table->enum('occupancy_type', ['standard', 'single', 'full_room', 'per_bed'])->default('standard');
            $table->integer('duration_days')->default(30);
            $table->decimal('price', 10, 2);
            $table->enum('type', ['monthly', 'quarterly', 'half_yearly', 'yearly', 'custom'])->default('monthly');
            $table->json('features')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // -------------------------------------------------------
        // 6. subscriptions  (FK → users, packages)
        // -------------------------------------------------------
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('package_id')->constrained('packages')->onDelete('cascade');
            $table->date('starts_at');
            $table->date('expires_at');
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending', 'paused'])->default('pending');
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // -------------------------------------------------------
        // 7. invoices  (FK → subscriptions)
        // -------------------------------------------------------
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->timestamps();
        });

        // -------------------------------------------------------
        // 8. payments  (FK → subscriptions, invoices)
        // -------------------------------------------------------
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('gateway');                        // razorpay / stripe / cash
            $table->string('gateway_ref')->nullable();        // gateway payment ID
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // 9. rooms  (FK → floors via floors table created inline)
        // -------------------------------------------------------
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('floor_number')->default(0);
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('floor_id')->constrained('floors')->onDelete('cascade');
            $table->string('room_number');
            $table->integer('capacity')->default(1);
            $table->integer('available_beds')->default(1);
            $table->decimal('full_rent', 10, 2)->nullable();
            $table->decimal('per_bed_rent', 10, 2)->nullable();
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('floors');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('kyc_documents');
        Schema::dropIfExists('categories');
    }
};
