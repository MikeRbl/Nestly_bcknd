<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->string('motivo', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
        });
    }
};