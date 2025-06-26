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
        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();

            // Clave foránea para el usuario
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // 1. Creamos la columna
            $table->unsignedBigInteger('propiedad_id'); 

            // 2. Definimos la restricción de clave foránea
            $table->foreign('propiedad_id')
                  ->references('id_propiedad')
                  ->on('propiedades')        
                  ->onDelete('cascade');  
            
            $table->timestamps();
            $table->unique(['user_id', 'propiedad_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favoritos');
    }
};
