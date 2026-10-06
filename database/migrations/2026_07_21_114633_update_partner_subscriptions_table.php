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
        // Using raw SQL to modify ENUM to avoid DBAL issues
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE partner_subscriptions MODIFY COLUMN status ENUM('active', 'expired', 'cancelled', 'pending', 'rejected') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE partner_subscriptions MODIFY COLUMN status ENUM('active', 'expired', 'cancelled') DEFAULT 'active'");
    }
};
