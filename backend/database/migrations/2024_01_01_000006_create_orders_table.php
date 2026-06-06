<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Orders and order_items tables – Ordering bounded context.
 * Orders are immutable once created; status changes are append-only in production systems.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict');             // Prevent deleting users with orders
            $table->enum('status', [                  // Order lifecycle state machine
                'pending',
                'paid',
                'shipped',
                'cancelled',
            ])->default('pending');
            $table->decimal('total_amount', 10, 2);   // Sum of all order_items at checkout
            $table->text('shipping_address');          // Snapshot of delivery address
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->onDelete('cascade');
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('restrict');             // Keep FK for reporting
            $table->string('product_name');           // Snapshot: remains accurate if product renamed
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);     // Snapshot: price at purchase time
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
