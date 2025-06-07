<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProductsTableImagePathColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Modifica la columna image_path para que sea un VARCHAR de 500 caracteres
            // o un TEXT si esperas URLs aún más largas. 500 es un buen punto de partida.
            // Asegúrate de tener 'doctrine/dbal' instalado para usar change().
            $table->string('image_path', 500)->nullable()->change();
            // Si cambiaste a TEXT (elimina la línea de arriba y usa esta)
            // $table->text('image_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Revertir a la longitud original (ej. 255) si es necesario para el rollback.
            // Esto es importante si cambiaste de TEXT a string.
            $table->string('image_path', 255)->nullable()->change();
        });
    }
}