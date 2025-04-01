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
        Schema::create('contratos', function (Blueprint $table) {
            $table->id('id_contrato');
            $table->unsignedBigInteger('id_solicitud');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('monto', 10, 2);
            $table->boolean('estado');
            $table->timestamps();
            $table->foreign('id_solicitud')->references('id_solicitud')->on('solicitudes_alquiler')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contratos');
    }
};
