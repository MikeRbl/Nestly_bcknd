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
        Schema::table('resenas', function (Blueprint $table) {
    

            // Vincula la reseña a la propiedad reseñada.
            $table->foreignId('propiedad_id')->after('id')->constrained(table: 'propiedades', column: 'id_propiedad')->onDelete('cascade');
            
            // Vincula la reseña al usuario que la escribió.
            $table->foreignId('user_id')->after('propiedad_id')->constrained('users')->onDelete('cascade');

            // Contenido de la reseña.
            $table->unsignedTinyInteger('puntuacion')->comment('Puntuación de 1 a 5');
            $table->text('comentario')->nullable();

            // Impedir que un usuario reseñe la misma propiedad dos veces.
            $table->unique(['propiedad_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resenas', function (Blueprint $table) {
            // --- QUITAR TODO LO QUE SE AGREGÓ EN UP() ---
            // Es importante hacerlo en el orden inverso: primero índices, luego llaves foráneas y al final columnas.

            $table->dropUnique(['propiedad_id', 'user_id']);
            
            $table->dropForeign(['propiedad_id']);
            $table->dropForeign(['user_id']);

            
            $table->dropColumn(['propiedad_id', 'user_id', 'puntuacion', 'comentario']);
        });
    }
};