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
        Schema::create('lead_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lead_order_id');
            $table->uuid('product_id'); // Link to Product
            $table->decimal('price', 12, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();
            
            $table->foreign('lead_order_id')->references('id')->on('lead_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_order_items');
    }
};
