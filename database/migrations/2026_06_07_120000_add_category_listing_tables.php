<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. listing_images
        Schema::create('listing_images', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('listing_id')->constrained('listings')->onDelete('cascade');
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 1b. room_images
        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. listing_shifts
        Schema::create('listing_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('listing_id')->constrained('listings')->onDelete('cascade');
            $table->enum('shift_name', ['morning', 'evening', 'night']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('max_members')->default(30);
            $table->decimal('fee', 10, 2)->default(0);
            $table->timestamps();
        });

        // 3. listing_trainers
        Schema::create('listing_trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('listing_id')->constrained('listings')->onDelete('cascade');
            $table->string('name');
            $table->string('specialization')->nullable();
            $table->integer('experience_years')->default(0);
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        // 4. listing_meta (custom field values per listing)
        Schema::create('listing_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('listing_id')->constrained('listings')->onDelete('cascade');
            $table->foreignId('custom_field_id')->constrained('custom_fields')->onDelete('cascade');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['listing_id', 'custom_field_id']);
        });

        // 5. Add listing_id to floors (floors now belong to a listing/property)
        Schema::table('floors', function (Blueprint $table) {
            $table->uuid('listing_id')->nullable()->after('id');
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
        });

        // 6. Add room_type to rooms for direct access
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('room_type')->nullable()->after('room_number'); // single/double/triple/full
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('room_type');
        });
        Schema::table('floors', function (Blueprint $table) {
            $table->dropForeign(['listing_id']);
            $table->dropColumn('listing_id');
        });
        Schema::dropIfExists('listing_meta');
        Schema::dropIfExists('listing_trainers');
        Schema::dropIfExists('listing_shifts');
        Schema::dropIfExists('room_images');
        Schema::dropIfExists('listing_images');
    }
};
