<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToPropiedadesTable extends Migration
{
    public function up()
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->string('titulo')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('direccion')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->boolean('apartamento')->default(false);
            $table->boolean('casaPlaya')->default(false);
            $table->boolean('industrial')->default(false);
            $table->boolean('anualizado')->default(false);
            $table->decimal('deposito', 8, 2)->nullable();
            $table->enum('mascotas', ['si', 'no'])->default('no');
            $table->integer('tamano')->nullable();

            // Solo agregamos si no existe la columna 'fotos'
            if (!Schema::hasColumn('propiedades', 'fotos')) {
                $table->text('fotos')->nullable(); // Puedes guardar como JSON o string separada por comas
            }
        });
    }

    public function down()
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropColumn([
                'titulo',
                'descripcion',
                'direccion',
                'email',
                'telefono',
                'apartamento',
                'casaPlaya',
                'industrial',
                'anualizado',
                'deposito',
                'mascotas',
                'tamano',
                'fotos', // también lo removemos en el rollback
            ]);
        });
    }
}
