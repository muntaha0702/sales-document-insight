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
        Schema::create('sales', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('transaction_id')->unique(); // Unique transaction identifier (e.g., T001)
            $table->date('date'); // Date of the sale (e.g., 2025-06-01)
            $table->string('product_name'); // Name of the product (e.g., T-Shirts)
            $table->string('category')->nullable(); // Product category (e.g., Apparel), optional
            $table->integer('quantity'); // Number of units sold
            $table->decimal('unit_price', 8, 2); // Price per unit (e.g., 20.00)
            $table->decimal('revenue', 10, 2); // Total revenue (Quantity × UnitPrice)
            $table->string('customer_id')->nullable(); // Customer identifier (e.g., C123), optional
            $table->string('region')->nullable(); // Geographic region (e.g., North), optional
            $table->integer('returns')->default(0); // Number of units returned
            $table->decimal('discount', 8, 2)->default(0.00); // Discount applied
            $table->string('payment_method')->nullable(); // Payment method (e.g., Credit Card), optional
            $table->string('store_id')->nullable(); // Store or sales channel (e.g., S01), optional
            $table->timestamps(); // Created_at and updated_at columns

            // Indexes for faster querying
            $table->index('date');
            $table->index('product_name');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
