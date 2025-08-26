<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('testimonios', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'avatar', 'fecha']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testimonios', function (Blueprint $table) {
            // Esto permite revertir los cambios si algo sale mal.
            $table->string('nombre');
            $table->string('avatar')->nullable();
            $table->date('fecha');
        });
    }
};
