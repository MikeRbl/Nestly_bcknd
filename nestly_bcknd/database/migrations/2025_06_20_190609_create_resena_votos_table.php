<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resena_votos', function (Blueprint $table) {
            // Llaves foráneas que conectan al usuario y a la reseña
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('resena_id')->constrained('resenas')->onDelete('cascade');

            // Clave primaria compuesta: previene que un usuario vote dos veces por la misma reseña.
            $table->primary(['user_id', 'resena_id']);

            $table->timestamps(); // Para saber cuándo se dio el like
        });
    }

    public function down()
    {
        Schema::dropIfExists('resena_votos');
    }
};