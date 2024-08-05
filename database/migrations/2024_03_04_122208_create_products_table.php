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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('quantity');
            $table->decimal('priceSale', 8, 2);
            $table->decimal('priceFav', 8, 2)->nullable();
            $table->decimal('priceMax', 8, 2)->nullable();
            $table->string('reference');
            $table->foreignId('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreignId('subcategory_id')->references('id')->on('subcategories')->onDelete('cascade');
            $table->enum('echantillon', ['FREE', 'PAID', 'REFUNDED'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};