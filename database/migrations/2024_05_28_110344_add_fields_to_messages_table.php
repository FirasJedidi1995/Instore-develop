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
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->enum('sender_type', ['provider', 'admin'])->after('sender_id');
            $table->timestamp('read_at')->nullable();
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['receiver_id']);
            $table->dropColumn(['receiver_id', 'sender_type', 'read_at']); // Supprimer les colonnes ajoutées
        });
    }
};
