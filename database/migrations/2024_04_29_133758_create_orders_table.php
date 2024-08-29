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
        Schema::create('orders', function (Blueprint $table) {
            // 
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('email');
            $table->string('phone');
            $table->string('city');
            $table->string('street');  
            $table->integer('post_code');
            $table->string('reference')->unique();
            $table->string('cardNumber',255)->nullable(); // Considérez encryptation
            $table->string('securityCode',255)->nullable(); // Considérez encryptation
            $table->string('CVV',255)->nullable(); // Considérez encryptation
            $table->foreignId('size_id')->nullable()->constrained('sizes')->nullOnDelete()->after('CVV');
            $table->foreignId('color_id')->nullable()->constrained('colors')->nullOnDelete()->after('size_id');
            $table->string('invoice_link')->nullable();
            $table->integer('quantity');
            $table->float('totalPrice')->default(0);
            $table->enum('payment', ['Credit','CashOnDelivery']);
            $table->enum('status', ['PENDING', 'SUCCESS','REFUSED','CANCEL','INPROGRESS'])->default('PENDING');
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};