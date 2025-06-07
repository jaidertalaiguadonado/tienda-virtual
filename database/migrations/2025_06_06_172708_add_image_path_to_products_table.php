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
        Schema::table('products', function (Blueprint $table) {
            // Añade la columna 'image_path' después de la columna 'stock'
            // Puede ser 'nullable' si no todos los productos tendrán imagen,
            // o no poner nullable() si quieres que sea obligatoria.
            $table->string('image_path')->nullable()->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Cuando reviertas la migración, elimina la columna
            $table->dropColumn('image_path');
        });
    }
};