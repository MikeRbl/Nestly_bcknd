<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reportes', function (Blueprint $table) {
            // Elimina columnas antiguas si existen
            if (Schema::hasColumn('reportes', 'reportado_id')) {
                $table->dropColumn('reportado_id');
            }
            if (Schema::hasColumn('reportes', 'tipo')) {
                $table->dropColumn('tipo');
            }
            
            $table->morphs('reportable');
        });
    }

    public function down()
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->unsignedBigInteger('reportado_id')->nullable();
            $table->string('tipo')->nullable();

            $table->dropMorphs('reportable');
        });
    }
};
