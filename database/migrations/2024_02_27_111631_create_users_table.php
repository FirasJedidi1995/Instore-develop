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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone');
            $table->string('image')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE','PENDING'])->default('PENDING');
           // $table->date('birthday')->nullable();
            //$table->enum('sexe', ['female', 'male'])->nullable();
            $table->enum('poste', ['administrator', 'operator'])->nullable();
            $table->string('acountLink')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->integer('post_code')->nullable();
            $table->string('CIN')->nullable();
            $table->string('TAXNumber')->nullable();
            $table->string('companyName')->nullable();
            $table->boolean('companyUnderConstruction')->nullable()->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        
    }
};
