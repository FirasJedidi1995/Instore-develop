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
        Schema::table('orders', function (Blueprint $table) {
            // Supprimer les anciennes colonnes size et color
            $table->dropColumn('size');
            $table->dropColumn('color');

            // Ajouter les nouvelles colonnes size_id et color_id
            $table->foreignId('size_id')->nullable()->constrained('sizes')->nullOnDelete()->after('CVV');
            $table->foreignId('color_id')->nullable()->constrained('colors')->nullOnDelete()->after('size_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Supprimer les colonnes size_id et color_id
            $table->dropForeign(['size_id']);
            $table->dropForeign(['color_id']);
            $table->dropColumn('size_id');
            $table->dropColumn('color_id');

            // Restaurer les anciennes colonnes size et color
            $table->string('size')->nullable()->after('CVV');
            $table->string('color')->nullable()->after('size');
        });
    }
};