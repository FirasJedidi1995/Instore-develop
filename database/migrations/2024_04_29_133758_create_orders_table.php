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
            $table->integer('cardNumber')->nullable();
            $table->integer('securityCode')->nullable();
            $table->string('CVV')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('invoice_link')->nullable();
            $table->integer('quantity');
            $table->float('TVA');
            $table->float('shippingCost');
            $table->float('totalProduct')->default(0);
            $table->float('totalPrice')->default(0);
            $table->enum('payment', ['Credit','CashOnDelivery']);
            $table->enum('status', ['PENDING', 'SUCCESS','REFUSED','CANCEL','INPROGRESS'])->default('PENDING');
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
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
