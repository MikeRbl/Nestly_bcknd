<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rentas', function (Blueprint $table) {
            $table->id();
            
            // Relación con usuarios
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Relación con propiedades (usando id_propiedad)
            $table->unsignedBigInteger('propiedad_id');
            $table->foreign('propiedad_id')
                  ->references('id_propiedad')
                  ->on('propiedades')
                  ->onDelete('cascade');
            
            // Fechas
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            
            // Datos financieros
            $table->decimal('monto', 10, 2);
            $table->decimal('deposito', 10, 2)->nullable();
            $table->string('estado')->default('activa');
            $table->string('metodo_pago', 50)->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'propiedad_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rentas');
    }
};