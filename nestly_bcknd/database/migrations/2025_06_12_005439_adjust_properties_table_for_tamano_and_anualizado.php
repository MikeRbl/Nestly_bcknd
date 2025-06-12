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
            // 1. Elimina la columna 'tamano'
            $table->dropColumn('tamano');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Esto permite revertir ambos cambios si es necesario
        Schema::table('propiedades', function (Blueprint $table) {
            // 1. Vuelve a crear la columna 'tamano'
            $table->string('tamano')->nullable();

        });
    }
};
