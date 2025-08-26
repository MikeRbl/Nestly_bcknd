<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar id_propiedad si existe y su FK
        if (Schema::hasColumn('testimonios', 'id_propiedad')) {
            try {
                DB::statement('ALTER TABLE testimonios DROP FOREIGN KEY testimonios_id_propiedad_foreign;');
            } catch (\Exception $e) {}
            
            Schema::table('testimonios', function (Blueprint $table) {
                $table->dropColumn('id_propiedad');
            });
        }

        // Verificar y manejar FK de id_usuario
        try {
            DB::statement('ALTER TABLE testimonios DROP FOREIGN KEY testimonios_id_usuario_foreign;');
        } catch (\Exception $e) {
            // No pasa nada si no existía
        }

        Schema::table('testimonios', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario')->nullable()->change();
            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('testimonios', function (Blueprint $table) {
            $table->unsignedBigInteger('id_propiedad')->nullable();
            $table->foreign('id_propiedad')
                ->references('id_propiedad')
                ->on('propiedades')
                ->onDelete('set null');

            try {
                DB::statement('ALTER TABLE testimonios DROP FOREIGN KEY testimonios_id_usuario_foreign;');
            } catch (\Exception $e) {}
        });
    }
};
