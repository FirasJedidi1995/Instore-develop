<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCardFieldsInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cardNumber', 255)->nullable()->change();
            $table->string('securityCode', 255)->nullable()->change();
            $table->string('CVV', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cardNumber')->nullable(false)->change();
            $table->string('securityCode')->nullable(false)->change();
            $table->string('CVV')->nullable(false)->change();
        });
    }
}