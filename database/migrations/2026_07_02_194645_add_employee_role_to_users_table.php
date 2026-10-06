<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify ENUM to include 'employee'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'partner', 'customer', 'employee') DEFAULT 'customer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert ENUM if possible
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'partner', 'customer') DEFAULT 'customer'");
    }
};
