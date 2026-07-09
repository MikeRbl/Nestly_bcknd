<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propiedades', function (Blueprint $table) {
            $table->id('id_propiedad');
            $table->foreignId('id_propietario')->constrained('users');
            $table->foreignId('tipo_propiedad_id')->constrained('tipos_propiedad');
            $table->string('titulo');
            $table->text('descripcion');
            $table->string('direccion');
            $table->string('pais');
            $table->string('estado_ubicacion');
            $table->string('ciudad');
            $table->string('colonia')->nullable();
            $table->decimal('precio', 12, 2);
            $table->integer('habitaciones');
            $table->integer('banos');
            $table->integer('metros_cuadrados');
            $table->boolean('amueblado')->default(false);
            $table->boolean('anualizado')->default(false);
            $table->decimal('deposito', 12, 2)->nullable();
            $table->string('mascotas');
            $table->string('estado_propiedad')->default('Disponible');
            $table->text('fotos')->nullable();
            $table->string('email');
            $table->string('telefono');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propiedades');
    }
};
