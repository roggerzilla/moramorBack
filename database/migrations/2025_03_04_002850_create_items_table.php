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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();  // Usamos nullable para permitir que el campo pueda estar vacío
            $table->decimal('price', 10, 2);  // Define el tipo de dato para precios
            $table->integer('quantity');  // Define el tipo de dato para cantidad
            $table->timestamps();  // Crea los campos 'created_at' y 'updated_at'
            $table->string('image_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');  // Corregido: Elimina la tabla en lugar de crearla
    }
};