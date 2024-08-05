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
        Schema::create('echantillon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instagrammer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->enum('payment', ['Credit','CashOnDelivery']);
            $table->enum('status', ['PENDING', 'SUCCESS','FAILED'])->default('PENDING');
            $table->timestamps();
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('echantillon');
    }
};
