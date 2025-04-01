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
        Schema::create('propiedades', function (Blueprint $table) {
            $table->id('id_propiedad'); 
            $table->unsignedBigInteger('id_propietario');
            $table->string('pais', 100); 
            $table->string('estado', 100); 
            $table->string('ciudad', 100); 
            $table->string('colonia', 100); 
            $table->decimal('precio', 10, 2);
            $table->integer('habitaciones');
            $table->integer('banos'); 
            $table->integer('metros_cuadrados'); 
            $table->boolean('amueblado'); 
            $table->string('disponibilidad', 20); 
            $table->text('fotos')->nullable(); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('propiedades');
    }
};
