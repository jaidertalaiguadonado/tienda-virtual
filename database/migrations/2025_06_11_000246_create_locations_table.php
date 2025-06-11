<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Clave foránea al usuario
            $table->string('address'); // Dirección completa
            $table->decimal('latitude', 10, 7)->nullable(); // Latitud, si la tienes
            $table->decimal('longitude', 10, 7)->nullable(); // Longitud, si la tienes
            $table->timestamps();
        });
    }

    public function down(): void
    {
            Schema::dropIfExists('locations');
    }
};