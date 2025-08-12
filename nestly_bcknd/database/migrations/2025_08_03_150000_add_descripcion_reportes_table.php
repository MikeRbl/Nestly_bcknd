<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescripcionReportesTable extends Migration
{
    public function up()
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('motivo');
        });
    }

    public function down()
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }
}
