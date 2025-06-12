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
            // Añade estas dos columnas
            $table->decimal('iva_amount', 10, 2)->after('total')->default(0.00);
            $table->decimal('mp_fee_amount', 10, 2)->after('iva_amount')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Asegúrate de eliminarlas en el orden inverso al que las añadiste
            $table->dropColumn('mp_fee_amount');
            $table->dropColumn('iva_amount');
        });
    }
};