<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carts and cart_items tables – Ordering bounded context.
 * Supports both authenticated (user_id) and guest (session_id) carts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')            // NULL for guest sessions
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('session_id')            // Guest cart identifier
                  ->nullable()
                  ->index();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')
                  ->constrained('carts')
                  ->onDelete('cascade');
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');
            $table->unsignedInteger('quantity');    // Must be at least 1 (enforced in app layer)
            $table->decimal('price', 10, 2);        // Snapshot price at add-to-cart time
            $table->timestamps();

            // Prevent duplicate product entries per cart
            $table->unique(['cart_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
