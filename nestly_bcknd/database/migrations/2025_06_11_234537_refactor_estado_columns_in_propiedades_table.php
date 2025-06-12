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
        Schema::table('propiedades', function (Blueprint $table) {
            // 1. Renombramos la columna existente 'estado' a 'estado_ubicacion'.

            $table->renameColumn('estado', 'estado_ubicacion');
            // 2. Añadimos la nueva columna para el estatus de la propiedad.
            $table->string('estado_propiedad')->default('Disponible')->after('amueblado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('propiedades', function (Blueprint $table) {
            // Esto es para poder revertir la migración si algo sale mal
            $table->renameColumn('estado_ubicacion', 'estado');
            $table->dropColumn('estado_propiedad');
        });
    }
};