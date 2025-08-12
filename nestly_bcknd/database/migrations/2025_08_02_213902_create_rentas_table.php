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
        Schema::create('rentas', function (Blueprint $table) {
            $table->id();
            
            // Claves foráneas que conectan la renta con la propiedad y los usuarios
            $table->foreignId('propiedad_id')->constrained('propiedades', 'id_propiedad')->onDelete('cascade');
            $table->foreignId('inquilino_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('propietario_id')->constrained('users')->onDelete('cascade');

            // Detalles del contrato de alquiler
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('monto_mensual', 10, 2);
            $table->enum('estado', ['activa', 'finalizada', 'cancelada', 'pendiente'])->default('pendiente');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentas');
    }
};
