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
            $table->dropColumn([
                'disponibilidad',
                'apartamento',
                'casaPlaya',
                'industrial'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Esto permite revertir los cambios si algo sale mal
        Schema::table('propiedades', function (Blueprint $table) {
            $table->boolean('disponibilidad')->default(true);
            $table->boolean('apartamento')->default(false);
            $table->boolean('casaPlaya')->default(false);
            $table->boolean('industrial')->default(false);
        });
    }
};