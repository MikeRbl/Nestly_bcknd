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
            $table->foreignId('id_propietario')->constrained('users')->onDelete('cascade');
            $table->foreignId('tipo_propiedad_id')->constrained('tipos_propiedad')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descripcion');
            $table->string('direccion');
            $table->string('pais');
            $table->string('estado_ubicacion');
            $table->string('ciudad');
            $table->string('colonia');
            $table->decimal('precio', 10, 2);
            $table->integer('habitaciones');
            $table->decimal('banos', 3, 1);
            $table->decimal('metros_cuadrados', 8, 2)->nullable();
            $table->boolean('amueblado')->default(false);
            $table->boolean('anualizado')->default(false);
            $table->decimal('deposito', 10, 2)->nullable();
            $table->boolean('mascotas')->nullable();
            $table->string('estado_propiedad')->default('Disponible');
            $table->json('fotos')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propiedades');
    }
};
