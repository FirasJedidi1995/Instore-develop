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
            $table->text('description')->nullable(); 
            $table->integer('quantity')->default(0); 
            $table->decimal('priceSale', 8, 2);
            $table->decimal('priceFav', 8, 2)->nullable();
            $table->decimal('priceMax', 8, 2)->nullable();
            $table->string('reference')->unique(); 
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('subcategory_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('instagrammer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('echantillon', ['FREE', 'PAID', 'REFUNDED'])->nullable();
            $table->enum('status', ['INSTOCK', 'OUTSTOCK']);
            $table->timestamps();
        });
    }

  
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};