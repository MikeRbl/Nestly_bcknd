<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitudes_alquiler', function (Blueprint $table) {
            $table->id('id_solicitud'); 
            $table->unsignedBigInteger('id_usuario'); 
            $table->unsignedBigInteger('id_propiedad');
            $table->boolean('estado'); 
            $table->date('fecha_solicitud'); 
            $table->timestamps(); 

            // Claves foráneas
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_propiedad')->references('id_propiedad')->on('propiedades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('solicitudes_alquiler');
    }
};
