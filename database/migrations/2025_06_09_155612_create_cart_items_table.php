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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            // 'cart_id' será una clave foránea a la tabla 'carts'
            // Esto vincula el ítem a un carrito específico.
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            // 'product_id' será una clave foránea a la tabla 'products'
            // Esto vincula el ítem a un producto existente.
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1); // Cantidad del producto en el carrito
            // Puedes almacenar el precio al momento de añadirlo al carrito
            // Esto es útil si los precios cambian en el futuro.
            $table->decimal('price_at_addition', 10, 2);
            // Si quieres almacenar la imagen para mostrarla fácilmente
            $table->string('image_path')->nullable();
            $table->timestamps(); // created_at, updated_at

            // Esto asegura que un producto solo pueda estar una vez en un carrito específico
            $table->unique(['cart_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
