<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Products table – ProductCatalog bounded context.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // Product display name
            $table->string('slug')->unique();               // URL-friendly identifier (SEO)
            $table->text('description')->nullable();        // Full product description
            $table->decimal('price', 10, 2);               // Price with 2 decimal precision
            $table->unsignedInteger('stock')->default(0);  // Available inventory count
            $table->string('image')->nullable();            // Image URL or relative path
            $table->boolean('is_active')->default(true);   // Controls public visibility
            $table->foreignId('created_by')                // Admin who created the product
                  ->constrained('users')
                  ->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();                         // Soft delete preserves order history
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
