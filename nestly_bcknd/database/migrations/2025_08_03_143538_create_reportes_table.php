<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reportador_id')->constrained('users')->onDelete('cascade');
            $table->morphs('reportable'); // Para reportar usuarios, reseñas, etc.
            
            // Usamos un enum para los motivos predefinidos.
            $table->enum('motivo', [
                'Spam', 
                'Contenido Inapropiado', 
                'Información Falsa', 
                'Acoso',
                'Suplantación de Identidad',
                'Otro' // Siempre es bueno tener una opción general
            ]);

            $table->text('descripcion')->nullable(); // Para que el usuario dé más detalles
            $table->enum('estado', ['pendiente', 'revisado', 'resuelto'])->default('pendiente'); // Añadido para gestionar el reporte
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reportes');
    }
};