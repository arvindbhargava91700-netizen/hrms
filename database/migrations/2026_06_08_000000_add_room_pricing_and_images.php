<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'room_id')) {
                $table->foreignUuid('room_id')->nullable()->after('listing_id')->constrained('rooms')->nullOnDelete();
            }

            if (!Schema::hasColumn('packages', 'occupancy_type')) {
                $table->enum('occupancy_type', ['standard', 'single', 'full_room', 'per_bed'])->default('standard')->after('room_id');
            }
        });

        if (!Schema::hasTable('room_images')) {
            Schema::create('room_images', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('room_id')->constrained('rooms')->onDelete('cascade');
                $table->string('image_path');
                $table->string('caption')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('room_images')) {
            Schema::dropIfExists('room_images');
        }

        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'occupancy_type')) {
                $table->dropColumn('occupancy_type');
            }

            if (Schema::hasColumn('packages', 'room_id')) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            }
        });
    }
};
